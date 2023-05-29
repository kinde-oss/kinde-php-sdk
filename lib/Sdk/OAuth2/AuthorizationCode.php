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

    public function authenticate(KindeClientSDK $clientSDK, array $additionalParameters = [])
    {
        $state = Utils::randomString();
        $this->storage->setState($state);
        $searchParams = [
            'client_id' => $clientSDK->clientId,
            'grant_type' => GrantType::authorizationCode,
            'redirect_uri' => $clientSDK->redirectUri,
            'response_type' => 'code',
            'scope' => $clientSDK->scopes,
            'state' => $state,
            'start_page' => 'login'
        ];
        $mergedAdditionalParameters = Utils::addAdditionalParameters($clientSDK->additionalParameters, $additionalParameters);
        $searchParams = array_merge($searchParams, $mergedAdditionalParameters);
        if (!headers_sent()) {
            exit(header('Location: ' . $clientSDK->authorizationEndpoint . '?' . http_build_query($searchParams)));
        }
    }
}
