<?php

namespace Kinde\KindeSDK\Sdk\OAuth2;

use GuzzleHttp\Client;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Sdk\Utils\Utils;
use Kinde\KindeSDK\Sdk\Enums\GrantType;

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
        $storage = Storage::getInstance('kinde_m2m');
        
        try {
            $client = new Client();
            $formData = [
                'client_id' => $clientSDK->clientId,
                'client_secret' => $clientSDK->clientSecret,
                'grant_type' => GrantType::clientCredentials
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
            
            
            // Store token before validation to maintain existing behavior
            $storage->setToken($token);
            
            $decodedToken = json_decode($token, true);
            if ($decodedToken === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode token: ' . json_last_error_msg());
            }
            
            // Simplified token validation
            if (empty($decodedToken['access_token'])) {
                throw new \RuntimeException('Invalid token format: missing access_token');
            }
            
            return $decodedToken;
        } catch (\Throwable $th) {
            error_log('Authentication error: ' . $th->getMessage());
            throw $th;
        }
    }
}ß