<?php

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Test\Sdk\KindeClientSDK;
use PHPUnit\Framework\TestCase;

class OAuth2ClientCredentialTest extends TestCase
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

    public function test_login_type_client_credential(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::clientCredentials, $this->logoutRedirectUri);
        $response = $this->client->login();
        $this->assertResponse($response);
    }

    public function test_login_type_client_credential_flow_with_audience(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::clientCredentials, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $response = $this->client->login();
        $this->assertResponse($response);
    }

    public function test_login_type_client_credential_flow_with_org_code(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::clientCredentials, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $additional = [
            'org_code' => 'org_123',
            'org_name' => 'My Application',
        ];
        $response = $this->client->login($additional);
        $this->assertResponse($response);
    }

    private function assertResponse($response): void
    {
        $this->assertIsObject($response);
        $this->assertTrue(property_exists($response, 'access_token'));
        $this->assertTrue(property_exists($response, 'expires_in'));
        $this->assertTrue(property_exists($response, 'scope'));
        $this->assertTrue(property_exists($response, 'token_type'));
    }
}
