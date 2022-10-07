<?php

namespace Kinde\KindeSDK\Sdk\OAuth2;

use GuzzleHttp\Client;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\KindeClientSDK;

class ClientCredentials
{
    public function login(KindeClientSDK $clientSDK)
    {
        try {
            $client = new Client();
            $response =
                $client->request('POST', $clientSDK->tokenEndpoint, [
                    'form_params' => [
                        'client_id' => $clientSDK->clientId,
                        'client_secret' => $clientSDK->clientSecret,
                        'grant_type' => GrantType::clientCredentials,
                        'scope' => $clientSDK->scopes
                    ]
                ]);
            $token = $response->getBody()->getContents();
            $_SESSION['token'] = $token;
            return json_decode($token);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
