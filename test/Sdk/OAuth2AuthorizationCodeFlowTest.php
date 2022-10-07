<?php

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\KindeClientSDK;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;


class OAuth2AuthorizationCodeFlowTest extends TestCase
{
    private $client;

    private $domain;

    private $redirectUri;

    private $clientId;

    private $clientSecret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->domain = $_ENV['KINDE_HOST'];

        $this->redirectUri = $_ENV['KINDE_REDIRECT_URI'];

        $this->clientId = $_ENV['KINDE_CLIENT_ID'];

        $this->clientSecret = $_ENV['KINDE_CLIENT_SECRET'];
    }

    /**
     * It tests the login function with the authorization code grant type.
     */
    public function test_login_type_authorization_code_flow(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::authorizationCode);
        $this->client->login();
        $this->assertTrue(headers_sent());
    }
}