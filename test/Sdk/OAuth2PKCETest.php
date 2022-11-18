<?php

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\KindeClientSDK;
use PHPUnit\Framework\TestCase;

class OAuth2PKCETest extends TestCase
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
     * It tests that the login method redirects to the authorization endpoint when the grant type is
     * PKCE
     */
    public function test_login_type_pkce(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::PKCE, $this->logoutRedirectUri);
        $this->client->login();
        $this->assertTrue(headers_sent());
    }
}
