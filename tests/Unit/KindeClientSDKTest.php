<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use InvalidArgumentException;

/**
 * Unit tests for KindeClientSDK initialization and configuration.
 * Tests constructor behavior, environment variable handling, and endpoint generation.
 *
 * @covers \Kinde\KindeSDK\KindeClientSDK::__construct
 * @covers \Kinde\KindeSDK\KindeClientSDK::createFromEnv
 */
class KindeClientSDKTest extends KindeTestCase
{
    // =========================================================================
    // Constructor Tests
    // =========================================================================

    public function testConstructorWithExplicitParameters(): void
    {
        $client = new KindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals(self::TEST_DOMAIN, $client->domain);
        $this->assertEquals(self::TEST_REDIRECT_URI, $client->redirectUri);
        $this->assertEquals(self::TEST_CLIENT_ID, $client->clientId);
        $this->assertEquals(self::TEST_CLIENT_SECRET, $client->clientSecret);
        $this->assertEquals(GrantType::authorizationCode, $client->grantType);
        $this->assertEquals(self::TEST_LOGOUT_REDIRECT_URI, $client->logoutRedirectUri);
    }

    public function testConstructorSetsDefaultScopes(): void
    {
        $client = $this->createClient();

        $this->assertEquals('openid profile email offline', $client->scopes);
    }

    public function testConstructorWithCustomScopes(): void
    {
        $client = new KindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI,
            'openid profile email offline custom_scope'
        );

        $this->assertEquals('openid profile email offline custom_scope', $client->scopes);
    }

    public function testConstructorSetsDefaultForceApiToFalse(): void
    {
        $client = $this->createClient();

        $this->assertFalse($client->forceApi);
    }

    public function testConstructorWithForceApiTrue(): void
    {
        $client = new KindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI,
            'openid profile email offline',
            [],
            '',
            true
        );

        $this->assertTrue($client->forceApi);
    }

    public function testConstructorWithAdditionalParameters(): void
    {
        $additionalParams = [
            'audience' => self::TEST_DOMAIN . '/api',
            'org_code' => 'test-org',
            'org_name' => 'Test Organization'
        ];

        $client = new KindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI,
            'openid profile email offline',
            $additionalParams
        );

        $this->assertEquals($additionalParams, $client->additionalParameters);
    }

    // =========================================================================
    // Environment Variable Tests
    // =========================================================================

    public function testCreateFromEnvWithValidEnvironmentVariables(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_GRANT_TYPE'] = GrantType::authorizationCode;
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = self::TEST_LOGOUT_REDIRECT_URI;

        $client = KindeClientSDK::createFromEnv();

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals(self::TEST_DOMAIN, $client->domain);
        $this->assertEquals(self::TEST_REDIRECT_URI, $client->redirectUri);
        $this->assertEquals(self::TEST_CLIENT_ID, $client->clientId);
        $this->assertEquals(self::TEST_CLIENT_SECRET, $client->clientSecret);
        $this->assertEquals(GrantType::authorizationCode, $client->grantType);
        $this->assertEquals(self::TEST_LOGOUT_REDIRECT_URI, $client->logoutRedirectUri);
    }

    public function testCreateFromEnvWithKindeHostEnvironmentVariable(): void
    {
        // KINDE_HOST is an alias for KINDE_DOMAIN
        $_ENV['KINDE_HOST'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_GRANT_TYPE'] = GrantType::authorizationCode;
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = self::TEST_LOGOUT_REDIRECT_URI;

        $client = KindeClientSDK::createFromEnv();

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals(self::TEST_DOMAIN, $client->domain);
    }

    public function testCreateFromEnvWithForceApiEnvironmentVariable(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_GRANT_TYPE'] = GrantType::authorizationCode;
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = self::TEST_LOGOUT_REDIRECT_URI;
        $_ENV['KINDE_FORCE_API'] = 'true';

        $client = KindeClientSDK::createFromEnv();

        $this->assertTrue($client->forceApi);
    }

    public function testCreateFromEnvWithCustomScopesEnvironmentVariable(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_GRANT_TYPE'] = GrantType::authorizationCode;
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = self::TEST_LOGOUT_REDIRECT_URI;
        $_ENV['KINDE_SCOPES'] = 'openid profile email offline custom_scope';

        $client = KindeClientSDK::createFromEnv();

        $this->assertEquals('openid profile email offline custom_scope', $client->scopes);
    }

    public function testCreateFromEnvWithProtocolEnvironmentVariable(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_GRANT_TYPE'] = GrantType::authorizationCode;
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = self::TEST_LOGOUT_REDIRECT_URI;
        $_ENV['KINDE_PROTOCOL'] = 'https';

        $client = KindeClientSDK::createFromEnv();

        $this->assertEquals('https', $client->protocol);
    }

    public function testConstructorWithMixedParametersAndEnvironmentVariables(): void
    {
        // Set some environment variables
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;

        // Override some parameters
        $client = new KindeClientSDK(
            domain: null, // Use from environment
            redirectUri: self::TEST_REDIRECT_URI, // Override
            clientId: null, // Use from environment
            clientSecret: null, // Use from environment
            grantType: GrantType::authorizationCode, // Override
            logoutRedirectUri: self::TEST_LOGOUT_REDIRECT_URI // Override
        );

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals(self::TEST_DOMAIN, $client->domain);
        $this->assertEquals(self::TEST_REDIRECT_URI, $client->redirectUri);
        $this->assertEquals(self::TEST_CLIENT_ID, $client->clientId);
        $this->assertEquals(self::TEST_CLIENT_SECRET, $client->clientSecret);
    }

    // =========================================================================
    // Validation Tests
    // =========================================================================

    public function testCreateFromEnvThrowsWhenMissingDomain(): void
    {
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide domain via parameter or KINDE_DOMAIN/KINDE_HOST environment variable');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvThrowsWhenInvalidDomain(): void
    {
        $_ENV['KINDE_DOMAIN'] = 'invalid-domain';
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide valid domain');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvThrowsWhenMissingRedirectUri(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_GRANT_TYPE'] = GrantType::authorizationCode;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide redirect_uri via parameter or KINDE_REDIRECT_URI environment variable');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvThrowsWhenMissingClientId(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide client_id via parameter or KINDE_CLIENT_ID environment variable');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvThrowsWhenMissingClientSecret(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide client_secret via parameter or KINDE_CLIENT_SECRET environment variable');

        KindeClientSDK::createFromEnv();
    }

    // =========================================================================
    // Grant Type Tests
    // =========================================================================

    public function testClientCredentialsGrantTypeDoesNotRequireRedirectUri(): void
    {
        $client = new KindeClientSDK(
            self::TEST_DOMAIN,
            null, // No redirect URI needed for client credentials
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::clientCredentials,
            null // No logout redirect URI needed for client credentials
        );

        $this->assertEquals(GrantType::clientCredentials, $client->grantType);
        $this->assertNull($client->redirectUri);
        $this->assertNull($client->logoutRedirectUri);
    }

    public function testPKCEGrantType(): void
    {
        $client = new KindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::PKCE,
            self::TEST_LOGOUT_REDIRECT_URI
        );

        $this->assertEquals(GrantType::PKCE, $client->grantType);
    }

    public function testAuthorizationCodeGrantType(): void
    {
        $client = new KindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );

        $this->assertEquals(GrantType::authorizationCode, $client->grantType);
    }

    // =========================================================================
    // Endpoint Generation Tests
    // =========================================================================

    public function testEndpointGeneration(): void
    {
        $client = $this->createClient();

        $this->assertEquals(self::TEST_DOMAIN . '/oauth2/auth', $client->authorizationEndpoint);
        $this->assertEquals(self::TEST_DOMAIN . '/oauth2/token', $client->tokenEndpoint);
        $this->assertEquals(self::TEST_DOMAIN . '/logout', $client->logoutEndpoint);
    }

    // =========================================================================
    // Storage Initialization Tests
    // =========================================================================

    public function testStorageInitialization(): void
    {
        $client = $this->createClient();

        $this->assertNotNull($client->storage);
    }

    // =========================================================================
    // Default Values Tests
    // =========================================================================

    public function testDefaultGrantTypeIsAuthorizationCode(): void
    {
        $_ENV['KINDE_DOMAIN'] = self::TEST_DOMAIN;
        $_ENV['KINDE_REDIRECT_URI'] = self::TEST_REDIRECT_URI;
        $_ENV['KINDE_CLIENT_ID'] = self::TEST_CLIENT_ID;
        $_ENV['KINDE_CLIENT_SECRET'] = self::TEST_CLIENT_SECRET;
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = self::TEST_LOGOUT_REDIRECT_URI;
        // Not setting KINDE_GRANT_TYPE

        $client = KindeClientSDK::createFromEnv();

        $this->assertEquals(GrantType::authorizationCode, $client->grantType);
    }
}
