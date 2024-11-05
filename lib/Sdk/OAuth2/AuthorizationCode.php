<?php

namespace Kinde\KindeSDK\Sdk\OAuth2;

use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Storage\Storage;

class AuthorizationCode
{
    /**
     * @var Storage
     */
    protected $storage;

    function __construct()
    {
        $this->storage = Storage::getInstance();
    }
    
    /**
     * Initiates the authentication process for the Kinde client SDK using the authorization code grant type.
     *
     * @param KindeClientSDK $clientSDK           The Kinde client SDK instance.
     * @param array          $additionalParameters An associative array of additional parameters (optional).
     *
     * @return void
     */
    public function authenticate(KindeClientSDK $clientSDK, array $additionalParameters = [])
    {
        if (!headers_sent()) {
            exit(header('Location: ' . $this->buildAuthorizationURI($clientSDK->authorizationEndpoint, $clientSDK->clientId, $clientSDK->redirectUri, $clientSDK->scopes, $additionalParameters, $clientSDK->additionalParameters)));
        }
    }
    
    public function buildAuthorizationURI(
        string $authorizationEndpoint,
        string $clientId,
        string $redirectUri,
        string $scopes,
        array $additionalParameters = [],
        array $sdkAdditionalParameters = []
    ): string
    {
        $state = Utils::randomString();
        $this->storage->setState($state);
        $searchParams = [
            'client_id' => $clientId,
            'grant_type' => GrantType::authorizationCode,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scopes,
            'state' => $state,
            'start_page' => 'login'
        ];
        $mergedAdditionalParameters = Utils::addAdditionalParameters($sdkAdditionalParameters, $additionalParameters);
        $searchParams = array_merge($searchParams, $mergedAdditionalParameters);
        return $authorizationEndpoint . '?' . http_build_query($searchParams);
    }
}
