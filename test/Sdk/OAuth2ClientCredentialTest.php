<?php

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\KindeClientSDK;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Exception\ClientException;

class OAuth2ClientCredentialTest extends TestCase
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
     * It tests the login function with the client credentials grant type.
     */
    public function test_login_type_client_credential(): void
    {
        $this->client = new KindeClientSDK($this->domain, $this->redirectUri, $this->clientId, $this->clientSecret, GrantType::clientCredentials);
        $response = $this->client->login();
        $this->assertObjectHasAttribute('access_token', $response);
        $this->assertObjectHasAttribute('expires_in', $response);
        $this->assertObjectHasAttribute('scope', $response);
        $this->assertObjectHasAttribute('token_type', $response);
    }
}
