<?php

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use PHPUnit\Framework\TestCase;

class KindeClientSDKTest extends TestCase
{
    private $client;

    private $domain;

    private $redirectUri;

    private $logoutRedirectUri;

    private $clientId;

    private $clientSecret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->domain = $_ENV['KINDE_HOST'];

        $this->redirectUri = $_ENV['KINDE_REDIRECT_URI'];

        $this->clientId = $_ENV['KINDE_CLIENT_ID'];

        $this->clientSecret = $_ENV['KINDE_CLIENT_SECRET'];

        $this->logoutRedirectUri = $_ENV['KINDE_POST_LOGOUT_REDIRECT_URL'];
    }

    /**
     * `test_initial` tests the initialisation of the KindeClientSDK class
     */
    public function test_initial(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $this->assertInstanceOf(KindeClientSDK::class, $this->client);
    }

    /**
     * `test_initial_empty_domain` tests that an exception is thrown when the domain is empty
     */
    public function test_initial_empty_domain(): void
    {
        $this->expectExceptionMessage("Please provide domain");
        $this->client = new KindeClientSDK('', $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    /**
     * This function tests that the KindeClientSDK class throws an exception when the redirect_uri is
     * not provided
     */
    public function test_initial_empty_redirect_uri(): void
    {
        $this->expectExceptionMessage("Please provide redirect_uri");
        $this->client = new KindeClientSDK($this->domain, '', $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    public function test_initial_empty_client_id(): void
    {
        $this->expectExceptionMessage("Please provide client_id");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, '', $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    public function test_initial_empty_client_secret(): void
    {
        $this->expectExceptionMessage("Please provide client_secret");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, '', GrantType::PKCE, $this->logoutRedirectUri);
    }

    /**
     * `test_initial_invalid_domain` tests that the `KindeClientSDK` class throws an exception when an
     * invalid domain is provided
     */
    public function test_initial_invalid_domain(): void
    {
        $this->expectExceptionMessage("Please provide valid domain");
        $this->client = new KindeClientSDK('test.c', $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    /**
     * It tests the initial invalid redirect_uri.
     */
    public function test_initial_invalid_redirect_uri(): void
    {
        $this->expectExceptionMessage("Please provide valid redirect_uri");
        $this->client = new KindeClientSDK($this->domain, 'test.c', $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
    }

    public function test_get_grant_type_empty(): void
    {
        $this->expectExceptionMessage("Please provide correct grant_type");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $this->client->getGrantType('');
    }

    public function test_get_grant_type_wrong(): void
    {
        $this->expectExceptionMessage("Please provide correct grant_type");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $this->client->getGrantType('test123');
    }

    public function test_initial_valid_audience(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $this->assertInstanceOf(KindeClientSDK::class, $this->client);
    }

    public function test_initial_invalid_audience(): void
    {
        $this->expectExceptionMessage("Please supply a valid audience. Expected: string");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri, '', ['audience' => 1233]);
    }

    public function test_get_is_authenticated(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $this->assertIsBool($this->client->isAuthenticated);
        $this->assertEmpty($this->client->isAuthenticated);
    }

    public function test_login_invalid_org_code(): void
    {
        $this->expectExceptionMessage("Please supply a valid org_code. Expected: string");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code' => 123,
            'org_name' => 'My Application',
        ];
        $this->client->login($additional);
    }

    public function test_login_invalid_org_name(): void
    {
        $this->expectExceptionMessage("Please supply a valid org_name. Expected: string");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code' => '123',
            'org_name' => 123
        ];
        $this->client->login($additional);
    }

    public function test_login_invalid_additional_org_name(): void
    {
        $this->expectExceptionMessage("Please provide correct additional, org_name_test");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code' => '123',
            'org_name_test' => '123'
        ];
        $this->client->login($additional);
    }

    public function test_login_invalid_additional_org_code(): void
    {
        $this->expectExceptionMessage("Please provide correct additional, org_code_test");
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $additional = [
            'org_code_test' => '123',
            'org_name' => '123'
        ];
        $this->client->login($additional);
    }
}
