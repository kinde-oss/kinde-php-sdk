<?php

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\KindeClientSDK;
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

    /**
     * It tests the login function with the authorization code grant type.
     */
    public function test_login_type_authorization_code_flow(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::authorizationCode, $this->logoutRedirectUri);
        $this->client->login();
        $this->assertTrue(headers_sent());
    }
}
