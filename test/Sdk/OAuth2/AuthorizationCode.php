<?php

namespace Kinde\KindeSDK\Test\Sdk\OAuth2;

use Kinde\KindeSDK\Test\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Test\Sdk\KindeClientSDK;
use Kinde\KindeSDK\Test\Sdk\Utils\Utils;
use Kinde\KindeSDK\Test\Sdk\Storage\Storage;

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
            'state' => $state
        ];
        $mergedAdditionalParameters = Utils::addAdditionalParameters($clientSDK->additionalParameters, $additionalParameters);
        $searchParams = array_merge($searchParams, $mergedAdditionalParameters);

        return 'redirecting...';
    }
}
