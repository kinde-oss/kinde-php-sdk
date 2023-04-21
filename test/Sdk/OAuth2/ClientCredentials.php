<?php

namespace Kinde\KindeSDK\Test\Sdk\OAuth2;

use Kinde\KindeSDK\Test\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Test\Sdk\KindeClientSDK;
use Kinde\KindeSDK\Test\Sdk\Storage\Storage;
use Kinde\KindeSDK\Test\Sdk\Utils\Utils;
use stdClass;

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
            $formData = [
                'client_id' => $clientSDK->clientId,
                'client_secret' => $clientSDK->clientSecret,
                'grant_type' => GrantType::clientCredentials,
                'scope' => $clientSDK->scopes
            ];
            $mergedAdditionalParameters = Utils::addAdditionalParameters($clientSDK->additionalParameters, $additionalParameters);
            $formData = array_merge($formData, $mergedAdditionalParameters);
            $obj = new stdClass();
            $obj->access_token = 'ok';
            $obj->expires_in = 123;
            $obj->scope = 'ok';
            $obj->token_type = 'ok';
            return $obj;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
