<?php

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Test\Sdk\KindeClientSDK;
use PHPUnit\Framework\TestCase;


class OAuth2AuthorizationCodeFlowTest extends TestCase
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

    public function test_login_type_authorization_code_flow(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::authorizationCode, $this->logoutRedirectUri);
        $result = $this->client->login();
        $this->assertEquals('redirecting...', $result);
    }

    public function test_login_type_authorization_code_flow_with_audience(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::authorizationCode, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $result = $this->client->login();
        $this->assertEquals('redirecting...', $result);
    }

    public function test_login_type_authorization_code_flow_with_additional(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::authorizationCode, $this->logoutRedirectUri, '', ['audience' => $this->domain . '/api']);
        $additional = [
            'org_code' => 'org_123',
            'org_name' => 'My Application',
        ];
        $result = $this->client->login($additional);
        $this->assertEquals('redirecting...', $result);
    }
}
