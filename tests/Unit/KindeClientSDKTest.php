<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Enums\TokenType;
use Kinde\KindeSDK\Sdk\Enums\PortalPage;
use Kinde\KindeSDK\OAuthException;
use InvalidArgumentException;
use Exception;
use PHPUnit\Framework\TestCase;

class KindeClientSDKTest extends TestCase
{
    private string $testDomain = 'https://test-domain.kinde.com';
    private string $testRedirectUri = 'http://localhost:8000/auth/callback';
    private string $testClientId = 'test_client_id';
    private string $testClientSecret = 'test_client_secret';
    private string $testLogoutRedirectUri = 'http://localhost:8000';

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
    }

    public function testCreateFromEnvWithValidEnvironmentVariables()
    {
        // Set up environment variables
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_REDIRECT_URI'] = $this->testRedirectUri;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;
        $_ENV['KINDE_GRANT_TYPE'] = 'authorization_code';
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = $this->testLogoutRedirectUri;

        $client = KindeClientSDK::createFromEnv();

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals($this->testDomain, $client->domain);
        $this->assertEquals($this->testRedirectUri, $client->redirectUri);
        $this->assertEquals($this->testClientId, $client->clientId);
        $this->assertEquals($this->testClientSecret, $client->clientSecret);
        $this->assertEquals('authorization_code', $client->grantType);
        $this->assertEquals($this->testLogoutRedirectUri, $client->logoutRedirectUri);
    }

    public function testCreateFromEnvWithKindeHostEnvironmentVariable()
    {
        // Set up environment variables using KINDE_HOST
        $_ENV['KINDE_HOST'] = $this->testDomain;
        $_ENV['KINDE_REDIRECT_URI'] = $this->testRedirectUri;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;
        $_ENV['KINDE_GRANT_TYPE'] = 'authorization_code';
        $_ENV['KINDE_LOGOUT_REDIRECT_URI'] = $this->testLogoutRedirectUri;

        $client = KindeClientSDK::createFromEnv();

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals($this->testDomain, $client->domain);
    }

    public function testCreateFromEnvWithMissingDomain()
    {
        $_ENV['KINDE_REDIRECT_URI'] = $this->testRedirectUri;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide domain via parameter or KINDE_DOMAIN/KINDE_HOST environment variable');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvWithInvalidDomain()
    {
        $_ENV['KINDE_DOMAIN'] = 'invalid-domain';
        $_ENV['KINDE_REDIRECT_URI'] = $this->testRedirectUri;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide valid domain');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvWithMissingRedirectUri()
    {
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;
        $_ENV['KINDE_GRANT_TYPE'] = 'authorization_code';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide redirect_uri via parameter or KINDE_REDIRECT_URI environment variable');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvWithMissingClientId()
    {
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_REDIRECT_URI'] = $this->testRedirectUri;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide client_id via parameter or KINDE_CLIENT_ID environment variable');

        KindeClientSDK::createFromEnv();
    }

    public function testCreateFromEnvWithMissingClientSecret()
    {
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_REDIRECT_URI'] = $this->testRedirectUri;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please provide client_secret via parameter or KINDE_CLIENT_SECRET environment variable');

        KindeClientSDK::createFromEnv();
    }

    public function testConstructorWithExplicitParameters()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            'authorization_code',
            $this->testLogoutRedirectUri
        );

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals($this->testDomain, $client->domain);
        $this->assertEquals($this->testRedirectUri, $client->redirectUri);
        $this->assertEquals($this->testClientId, $client->clientId);
        $this->assertEquals($this->testClientSecret, $client->clientSecret);
    }

    public function testConstructorWithMixedParameters()
    {
        // Set some environment variables
        $_ENV['KINDE_DOMAIN'] = $this->testDomain;
        $_ENV['KINDE_CLIENT_ID'] = $this->testClientId;
        $_ENV['KINDE_CLIENT_SECRET'] = $this->testClientSecret;

        // Override some parameters
        $client = new KindeClientSDK(
            domain: null, // Use from environment
            redirectUri: $this->testRedirectUri, // Override
            clientId: null, // Use from environment
            clientSecret: null, // Use from environment
            grantType: 'authorization_code', // Override
            logoutRedirectUri: $this->testLogoutRedirectUri // Override
        );

        $this->assertInstanceOf(KindeClientSDK::class, $client);
        $this->assertEquals($this->testDomain, $client->domain);
        $this->assertEquals($this->testRedirectUri, $client->redirectUri);
        $this->assertEquals($this->testClientId, $client->clientId);
        $this->assertEquals($this->testClientSecret, $client->clientSecret);
    }

    public function testConstructorWithCustomScopes()
    {
        $_ENV['KINDE_SCOPES'] = 'openid profile email offline custom_scope';

        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            'authorization_code',
            $this->testLogoutRedirectUri
        );

        $this->assertEquals('openid profile email offline custom_scope', $client->scopes);
    }

    public function testConstructorWithCustomProtocol()
    {
        $_ENV['KINDE_PROTOCOL'] = 'https';

        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            'authorization_code',
            $this->testLogoutRedirectUri
        );

        $this->assertEquals('https', $client->protocol);
    }

    public function testClientCredentialsGrantType()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            null, // No redirect URI needed for client credentials
            $this->testClientId,
            $this->testClientSecret,
            'client_credentials',
            null // No logout redirect URI needed for client credentials
        );

        $this->assertEquals('client_credentials', $client->grantType);
        $this->assertNull($client->redirectUri);
        $this->assertNull($client->logoutRedirectUri);
    }

    public function testEndpointGeneration()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            'authorization_code',
            $this->testLogoutRedirectUri
        );

        $this->assertEquals($this->testDomain . '/oauth2/auth', $client->authorizationEndpoint);
        $this->assertEquals($this->testDomain . '/oauth2/token', $client->tokenEndpoint);
        $this->assertEquals($this->testDomain . '/logout', $client->logoutEndpoint);
    }

    public function testAdditionalParameters()
    {
        $additionalParams = [
            'audience' => $this->testDomain . '/api',
            'org_code' => 'test-org',
            'org_name' => 'Test Organization'
        ];

        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            'authorization_code',
            $this->testLogoutRedirectUri,
            'openid profile email offline',
            $additionalParams
        );

        $this->assertEquals($additionalParams, $client->additionalParameters);
    }

    public function testStorageInitialization()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            'authorization_code',
            $this->testLogoutRedirectUri
        );

        $this->assertNotNull($client->storage);
    }

    public function testJwksUrlSetting()
    {
        $client = new KindeClientSDK(
            $this->testDomain,
            $this->testRedirectUri,
            $this->testClientId,
            $this->testClientSecret,
            'authorization_code',
            $this->testLogoutRedirectUri
        );

        // The storage should have the JWKS URL set
        $this->assertNotNull($client->storage);
    }
} 