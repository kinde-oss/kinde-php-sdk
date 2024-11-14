<?php

namespace Kinde\KindeSDK\Sdk\OAuth2;

use GuzzleHttp\Client;
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
    
    /**
     * Authenticates the Kinde client SDK using the client credentials grant type.
     *
     * @param KindeClientSDK $clientSDK           The Kinde client SDK instance.
     * @param array          $additionalParameters An associative array of additional parameters (optional).
     *
     * @return stdClass The decoded token response.
     *
     * @throws Throwable If an error occurs during the authentication process.
     */
    public function authenticate(KindeClientSDK $clientSDK, array $additionalParameters = [])
    {
        $this->storage->setM2MMode(true);
        error_log('Starting M2M authentication');
        
        try {
            $client = new Client();
            $formData = [
                'client_id' => $clientSDK->clientId,
                'client_secret' => $clientSDK->clientSecret,
                'grant_type' => 'client_credentials'
            ];
    
            if (!empty($clientSDK->scopes)) {
                $formData['scope'] = $clientSDK->scopes;
            }
    
            $response = $client->request('POST', $clientSDK->tokenEndpoint, [
                'form_params' => $formData,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ]
            ]);
    
            $token = $response->getBody()->getContents();
            error_log('M2M Token received');
            $this->storage->setToken($token);
            
            return json_decode($token);
        } catch (\Throwable $th) {
            error_log('M2M authentication error: ' . $th->getMessage());
            throw $th;
        } finally {
            error_log('Completing M2M authentication');
            $this->storage->setM2MMode(false);
        }
    }
}