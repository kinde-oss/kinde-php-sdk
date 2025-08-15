<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Enums\TokenType;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Api\Frontend\PermissionsApi;
use Kinde\KindeSDK\Api\Frontend\FeatureFlagsApi;
use Kinde\KindeSDK\Api\Frontend\OAuthApi;
use Kinde\KindeSDK\Model\Frontend\GetUserPermissionsResponse;
use Kinde\KindeSDK\Model\Frontend\GetUserPermissionsResponseData;
use Kinde\KindeSDK\Model\Frontend\GetUserPermissionsResponseDataPermissionsInner;
use Kinde\KindeSDK\Model\Frontend\GetFeatureFlagsResponse;
use Kinde\KindeSDK\Model\Frontend\GetFeatureFlagsResponseData;
use Kinde\KindeSDK\Model\Frontend\GetFeatureFlagsResponseDataFeatureFlagsInner;
use Kinde\KindeSDK\Model\Frontend\UserProfileV2;
use InvalidArgumentException;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class HardcheckTest extends TestCase
{
    private string $testDomain = 'https://test-domain.kinde.com';
    private string $testRedirectUri = 'http://localhost:8000/auth/callback';
    private string $testClientId = 'test_client_id';
    private string $testClientSecret = 'test_client_secret';
    private string $testLogoutRedirectUri = 'http://localhost:8000';
    private string $testAccessToken = 'test_access_token';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear any existing environment variables
        unset($_ENV['KINDE_DOMAIN']);
        unset($_ENV['KINDE_HOST']);
        unset($_ENV['KINDE_REDIRECT_URI']);
        unset($_ENV['KINDE_CLIENT_ID']);
        unset($_ENV['KINDE_CLIENT_SECRET']);
        unset($_ENV['KINDE_GRANT_TYPE']);
        unset($_ENV['KINDE_LOGOUT_REDIRECT_URI']);
        unset($_ENV['KINDE_SCOPES']);
        unset($_ENV['KINDE_PROTOCOL']);
        unset($_ENV['KINDE_FORCE_API']);
    }

    public function testConstructorWithForceApiFlag()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            GrantType::authorizationCode,
            $this->testLogoutRedirectUri,
            'openid profile email offline',
            [],
            '',
            true
        );

        $this->assertTrue($client->forceApi);
    }

    public function testConstructorWithForceApiEnvironmentVariable()
    {
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_REDIRECT_URI'] = $this->testRedirectUri;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;
        $_ENV['KINDE_GRANT_TYPE'] = 'authorization_code';
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = $this->testLogoutRedirectUri;
        $_ENV['KINDE_FORCE_API'] = 'true';

        $client = KindeClientSDK::createFromEnv();

        $this->assertTrue($client->forceApi);
    }

    public function testForceApiDisabledByDefault()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            GrantType::authorizationCode,
            $this->testLogoutRedirectUri
        );

        $this->assertFalse($client->forceApi);
    }



    public function testGetFlagTypeMethodExists()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            GrantType::authorizationCode,
            $this->testLogoutRedirectUri,
            'openid profile email offline',
            [],
            '',
            true
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('getFlagType');
        
        $this->assertTrue($method->isPrivate());
    }

    public function testGetApiConfigMethodExists()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            GrantType::authorizationCode,
            $this->testLogoutRedirectUri,
            'openid profile email offline',
            [],
            '',
            true
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('getApiConfig');
        
        $this->assertTrue($method->isPrivate());
    }

    public function testGetUserProfileFromApiMethodExists()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            GrantType::authorizationCode,
            $this->testLogoutRedirectUri,
            'openid profile email offline',
            [],
            '',
            true
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('getUserProfileFromApi');
        
        $this->assertTrue($method->isPrivate());
    }

    public function testGetPermissionsFromApiMethodExists()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            GrantType::authorizationCode,
            $this->testLogoutRedirectUri,
            'openid profile email offline',
            [],
            '',
            true
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('getPermissionsFromApi');
        
        $this->assertTrue($method->isPrivate());
    }

    public function testGetFeatureFlagsFromApiMethodExists()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            GrantType::authorizationCode,
            $this->testLogoutRedirectUri,
            'openid profile email offline',
            [],
            '',
            true
        );

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('getFeatureFlagsFromApi');
        
        $this->assertTrue($method->isPrivate());
    }


}
