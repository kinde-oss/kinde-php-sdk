<?php

namespace Kinde\KindeSDK\Tests\Unit;

use InvalidArgumentException;
use Kinde\KindeSDK\Sdk\Enums\AcrValues;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Exceptions\CallbackErrorException;
use Kinde\KindeSDK\Sdk\Exceptions\LoginRequiredException;
use Kinde\KindeSDK\Sdk\Exceptions\UnmetAuthenticationRequirementsException;
use Kinde\KindeSDK\Sdk\OAuth2\AuthorizationCode;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Sdk\Utils\StepUp;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Kinde\KindeSDK\Tests\Support\TestableKindeClientSDK;

/**
 * Unit tests for step-up authentication (RFC 9470).
 *
 * @covers \Kinde\KindeSDK\Sdk\Utils\StepUp
 * @covers \Kinde\KindeSDK\KindeClientSDK::getStepUpUrl
 * @covers \Kinde\KindeSDK\KindeClientSDK::getAuthenticationContext
 * @covers \Kinde\KindeSDK\KindeClientSDK::hasAuthenticationContext
 * @covers \Kinde\KindeSDK\KindeClientSDK::isStepUpSupported
 * @covers \Kinde\KindeSDK\KindeClientSDK::getToken
 */
class StepUpTest extends KindeTestCase
{
    private array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->serverBackup = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        parent::tearDown();
    }

    private function queryOf(string $url): array
    {
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        return $query;
    }

    private function simulateCallback(array $query): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost:8000';
        $_SERVER['REQUEST_URI'] = '/callback?' . http_build_query($query);
    }

    // =========================================================================
    // StepUp helpers
    // =========================================================================

    public function testRequiredAcr(): void
    {
        $this->assertSame('phr', StepUp::requiredAcr('mfa phr'));
        $this->assertSame('mfa', StepUp::requiredAcr(['MFA', 'unknown']));
        $this->assertSame('mfa', StepUp::requiredAcr('urn:okta:loa:2fa:any'));
        $this->assertNull(StepUp::requiredAcr(null));
    }

    public function testRequiredAcrFailsClosedOnUnrecognisedValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        StepUp::requiredAcr('mfaa');
    }

    public function testSatisfies(): void
    {
        $now = 1716800000;
        $claims = ['acr' => 'mfa', 'auth_time' => $now - 60];

        $this->assertTrue(StepUp::satisfies($claims, AcrValues::MFA, 300, $now));
        $this->assertFalse(StepUp::satisfies($claims, AcrValues::MFA, 30, $now));
        $this->assertFalse(StepUp::satisfies($claims, AcrValues::PHR, null, $now));
        $this->assertTrue(StepUp::satisfies(['acr' => 'phr'], AcrValues::MFA), 'phr meets mfa');
        $this->assertFalse(StepUp::satisfies([], AcrValues::MFA), 'absent acr does not meet mfa');
        $this->assertTrue(StepUp::satisfies([], null), 'absent acr is fine when nothing is required');
    }

    public function testAdditionalParametersNormalizeStepUpValues(): void
    {
        $this->assertSame(
            ['acr_values' => 'mfa phr', 'max_age' => '300'],
            Utils::checkAdditionalParameters(['acr_values' => ['mfa', 'phr'], 'max_age' => 300])
        );
        $this->assertSame(['acr_values' => 'mfa'], Utils::checkAdditionalParameters(['acr_values' => 'mfa', 'max_age' => null]));

        $this->expectException(InvalidArgumentException::class);
        Utils::checkAdditionalParameters(['max_age' => -1]);
    }

    // =========================================================================
    // Starting a step-up
    // =========================================================================

    public function testGetStepUpUrl(): void
    {
        $query = $this->queryOf($this->createClient()->getStepUpUrl(AcrValues::MFA, 300));

        $this->assertSame('mfa', $query['acr_values']);
        $this->assertSame('300', $query['max_age']);
        $this->assertSame(Storage::getState(), $query['state']);
    }

    public function testGetStepUpUrlWithPkce(): void
    {
        $query = $this->queryOf($this->createClient(['grantType' => GrantType::PKCE])->getStepUpUrl(AcrValues::PHR));

        $this->assertSame('phr', $query['acr_values']);
        $this->assertArrayNotHasKey('max_age', $query);
        $this->assertSame('S256', $query['code_challenge_method']);
    }

    public function testStepUpKeepsExistingTokens(): void
    {
        $this->setMockToken();
        $before = Storage::getAccessToken();

        $this->createClient()->getStepUpUrl(AcrValues::MFA);

        $this->assertSame($before, Storage::getAccessToken());
    }

    public function testStepUpRejectsInvalidRequests(): void
    {
        $cases = [
            'neither acr_values nor max_age' => [null, null, []],
            'fractional max_age' => [AcrValues::MFA, 1.5, []],
            'negative max_age' => [AcrValues::MFA, -1, []],
            'prompt=none' => [AcrValues::MFA, null, ['prompt' => 'none']],
        ];

        foreach ($cases as $name => [$acrValues, $maxAge, $additionalParameters]) {
            try {
                $this->createClient()->getStepUpUrl($acrValues, $maxAge, $additionalParameters);
                $this->fail("Expected $name to be rejected");
            } catch (InvalidArgumentException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testLoginUrlUnchangedWithoutStepUpOptions(): void
    {
        $query = $this->queryOf((new AuthorizationCode())->buildAuthorizationUrl($this->createClient()));

        $this->assertArrayNotHasKey('acr_values', $query);
        $this->assertArrayNotHasKey('max_age', $query);
    }

    public function testStepUpRejectsClientCredentials(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createClient(['grantType' => GrantType::clientCredentials])->getStepUpUrl(AcrValues::MFA);
    }

    // =========================================================================
    // Reading the authentication context
    // =========================================================================

    public function testGetAuthenticationContextReadsTokenEvenWithForceApi(): void
    {
        $authTime = time() - 30;
        $this->setMockToken([], ['acr' => 'mfa', 'amr' => ['pwd', 'totp'], 'auth_time' => $authTime]);

        $this->assertSame(
            ['acr' => 'mfa', 'amr' => ['pwd', 'totp'], 'auth_time' => $authTime],
            $this->createClient(['forceApi' => true])->getAuthenticationContext()
        );
    }

    public function testGetAuthenticationContextPrefersIdTokenAuthTime(): void
    {
        $this->setMockToken(['auth_time' => 1716800000], ['auth_time' => 1716800500]);

        $this->assertSame(1716800500, $this->createClient()->getAuthenticationContext()['auth_time']);
    }

    public function testHasAuthenticationContext(): void
    {
        $this->setMockToken([], ['acr' => 'mfa', 'auth_time' => time() - 120]);
        $client = $this->createClient();

        $this->assertTrue($client->hasAuthenticationContext(AcrValues::MFA, 300));
        $this->assertFalse($client->hasAuthenticationContext(AcrValues::MFA, 60));
        $this->assertFalse($client->hasAuthenticationContext(AcrValues::PHR));
    }

    public function testHasAuthenticationContextFalseForSingleFactorOrNoToken(): void
    {
        $this->assertFalse($this->createClient()->hasAuthenticationContext(AcrValues::MFA));

        $this->setMockToken([], ['amr' => ['pwd'], 'auth_time' => time()]);
        $this->assertFalse($this->createClient()->hasAuthenticationContext(AcrValues::MFA));
    }

    public function testHasAuthenticationContextThrowsOnUnrecognisedRequirement(): void
    {
        $this->setMockToken([], ['amr' => ['pwd'], 'auth_time' => time()]);

        $this->expectException(InvalidArgumentException::class);
        $this->createClient()->hasAuthenticationContext('mfaa');
    }

    // =========================================================================
    // Callback handling
    // =========================================================================

    public function testCallbackErrorsThrowTypedExceptions(): void
    {
        foreach ([UnmetAuthenticationRequirementsException::class, LoginRequiredException::class] as $exceptionClass) {
            $client = $this->createClient();
            $state = $this->queryOf($client->getStepUpUrl(AcrValues::PHR))['state'];
            $this->simulateCallback([
                'error' => $exceptionClass::ERROR_CODE,
                'error_description' => 'The requested authentication context could not be met',
                'state' => $state,
            ]);

            try {
                $client->getToken();
                $this->fail("Expected $exceptionClass");
            } catch (CallbackErrorException $e) {
                $this->assertInstanceOf($exceptionClass, $e);
                $this->assertSame('The requested authentication context could not be met', $e->getErrorDescription());
                $this->assertSame($state, $e->getState());
            }
        }
    }

    public function testCallbackAfterStepUpIsProcessedWhenAlreadyAuthenticated(): void
    {
        $this->setMockToken([], ['amr' => ['pwd'], 'auth_time' => time()]);
        $client = $this->createClient();
        $state = $this->queryOf($client->getStepUpUrl(AcrValues::MFA))['state'];
        $this->simulateCallback(['error' => 'unmet_authentication_requirements', 'state' => $state]);

        // Reaching the error proves the callback was processed rather than returning the stored token
        $this->expectException(UnmetAuthenticationRequirementsException::class);
        $client->getToken();
    }

    public function testRevisitedCallbackReturnsStoredTokenWhenAuthenticated(): void
    {
        $this->setMockToken();
        $this->simulateCallback(['code' => 'old_code', 'state' => 'stale_state']);

        $this->assertNotEmpty($this->createClient()->getToken()->access_token);
    }

    // =========================================================================
    // Discovery
    // =========================================================================

    private function createTestableClient(array $openIdConfiguration): TestableKindeClientSDK
    {
        return (new TestableKindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        ))->setMockOpenIdConfiguration($openIdConfiguration);
    }

    public function testIsStepUpSupported(): void
    {
        $client = $this->createTestableClient(['acr_values_supported' => ['mfa', 'phr']]);

        $this->assertTrue($client->isStepUpSupported());
        $this->assertTrue($client->isStepUpSupported());
        $this->assertSame(1, $client->getMethodCallCount('fetchOpenIdConfiguration'));

        $this->assertFalse($this->createTestableClient(['issuer' => self::TEST_DOMAIN])->isStepUpSupported());
    }
}
