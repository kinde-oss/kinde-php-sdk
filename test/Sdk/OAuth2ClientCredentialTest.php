<?php

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\KindeClientSDK;
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

    /**
     * It tests the login function with the client credentials grant type.
     */
    public function test_login_type_client_credential(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::clientCredentials, $this->logoutRedirectUri);
        $response = $this->client->login();
        $this->assertResponse($response);
    }

    /**
     * It tests the login function with the client credential grant type with audience.
     */
    public function test_login_type_client_credential_flow_with_audience(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::clientCredentials, $this->logoutRedirectUri, ['audience' => $this->domain . '/api']);
        $response = $this->client->login();
        $this->assertResponse($response);
    }

    /**
     * It tests the login function with the client credential grant type with additional.
     */
    public function test_login_type_client_credential_flow_with_org_code(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::clientCredentials, $this->logoutRedirectUri, ['audience' => $this->domain . '/api']);
        $additional = [
            'org_code' => 'org_123',
            'org_name' => 'My Application',
        ];
        $response = $this->client->login($additional);
        $this->assertResponse($response);
    }

    private function assertResponse($response): void
    {
        $this->assertObjectHasAttribute('access_token', $response);
        $this->assertObjectHasAttribute('expires_in', $response);
        $this->assertObjectHasAttribute('scope', $response);
        $this->assertObjectHasAttribute('token_type', $response);
    }
}
