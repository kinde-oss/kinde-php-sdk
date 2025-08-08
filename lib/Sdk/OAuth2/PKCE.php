<?php

namespace Kinde\KindeSDK\Sdk\OAuth2;

use Kinde\KindeSDK\Sdk\Utils\Utils;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Storage\Storage;

class PKCE
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
     * Initiates the authentication process for the Kinde client SDK.
     *
     * @param KindeClientSDK $clientSDK           The Kinde client SDK instance.
     * @param array          $additionalParameters An associative array of additional parameters (optional).
     *
     * @return void
     */
    public function authenticate(KindeClientSDK $clientSDK, array $additionalParameters = [])
    {
        $this->storage->removeItem(StorageEnums::CODE_VERIFIER);
        $challenge = Utils::generateChallenge();
        $state = $challenge['state'];
        $this->storage->setState($state);
        $searchParams = [
            'redirect_uri' => $clientSDK->redirectUri,
            'client_id' => $clientSDK->clientId,
            'response_type' => 'code',
            'scope' => $clientSDK->scopes,
            'code_challenge' => $challenge['codeChallenge'],
            'code_challenge_method' => 'S256',
            'state' => $state
        ];
        $mergedAdditionalParameters = Utils::addAdditionalParameters($clientSDK->additionalParameters, $additionalParameters);
        $searchParams = array_merge($searchParams, $mergedAdditionalParameters);
        $this->storage->setCodeVerifier($challenge['codeVerifier']);

        if (!headers_sent()) {
            exit(header('Location: ' . $clientSDK->authorizationEndpoint . '?' . http_build_query($searchParams)));
        }
    }
}
