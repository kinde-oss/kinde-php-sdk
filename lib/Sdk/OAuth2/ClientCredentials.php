<?php

namespace Kinde\KindeSDK\Sdk\OAuth2;

use GuzzleHttp\Client;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Sdk\Utils\Utils;

class ClientCredentials
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
        try {
            $client = new Client();
            $formData = [
                'client_id' => $clientSDK->clientId,
                'client_secret' => $clientSDK->clientSecret,
                'grant_type' => GrantType::clientCredentials,
                'scope' => $clientSDK->scopes
            ];
            $mergedAdditionalParameters = Utils::addAdditionalParameters($clientSDK->additionalParameters, $additionalParameters);
            $formData = array_merge($formData, $mergedAdditionalParameters);

            $response =
                $client->request('POST', $clientSDK->tokenEndpoint, [
                    'form_params' => $formData
                ]);
            $token = $response->getBody()->getContents();
            $this->storage->setToken($token);
            return json_decode($token);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
