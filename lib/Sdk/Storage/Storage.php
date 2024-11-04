<?php

namespace Kinde\KindeSDK\Sdk\Storage;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Utils\Utils;

class Storage extends BaseStorage
{
    public static $instance;

    private $tokenTimeToLive;
    
    public static function getInstance()
    {
        if (empty(self::$instance) || !(self::$instance instanceof Storage)) {
            self::$instance = new Storage();
        }
        return self::$instance;
    }

    public function getToken($associative = true)
    {
        $token = $this->getItem(StorageEnums::TOKEN);

        return empty($token) ? null : json_decode($token, $associative);
    }

    public function setToken($token): void
    {
        $this->setItem(StorageEnums::TOKEN, gettype($token) == 'string' ? $token : json_encode($token), $this->getTokenTimeToLive());
    }

    public function getAccessToken()
    {
        $token = $this->getToken();
        return empty($token) ? null : $token['access_token'];
    }

    public function getIdToken()
    {
        $token = $this->getToken();
        return empty($token) ? null : $token['id_token'];
    }

    public function getRefreshToken()
    {
        $token = $this->getToken();
        return empty($token) ? null : $token['refresh_token'];
    }

    public function getExpiredAt()
    {
        $accessToken = $this->getAccessToken();

        if (empty($accessToken)) {
            return 0;
        } else {
            $parsedToken = Utils::parseJWT($accessToken);
            if (empty($parsedToken)) {
                return 0;
            } else {
                return $parsedToken['exp'];
            }
        }
    }

    public function getTokenTimeToLive()
    {
        return !empty($this->tokenTimeToLive) ? $this->tokenTimeToLive : time() + 3600 * 24 * 15; // Live in 15 days
    }

    public function setTokenTimeToLive($tokenTimeToLive)
    {
        $this->$tokenTimeToLive = $tokenTimeToLive;
    }

    public function getState()
    {
        return $this->getItem(StorageEnums::STATE);
    }

    public function setState($newState)
    {
        $this->setItem(StorageEnums::STATE, $newState, time() + 3600 * 2); // expired in 2hrs
    }

    public function getCodeVerifier()
    {
        return $this->getItem(StorageEnums::CODE_VERIFIER);
    }

    public function setCodeVerifier($newCodeVerifier)
    {
        $this->setItem(StorageEnums::CODE_VERIFIER, $newCodeVerifier, time() + 3600 * 2); // expired in 2hrs);
    }

    public function getUserProfile()
    {
        $token = $this->getToken();
        $payload = Utils::parseJWT($token['id_token'] ?? '');
        return [
            'id' => $payload['sub'] ?? '',
            'given_name' => $payload['given_name'] ?? '',
            'family_name' => $payload['family_name'] ?? '',
            'email' => $payload['email'] ?? '',
            'picture' => $payload['picture'] ?? '',
        ];
    }

    public function getDecodedIdToken()
    {
        $token = $this->getToken();
        $payload = Utils::parseJWT($token['id_token'] ?? '');
        return $payload;
    }

    public function getDecodedAccessToken()
    {
        $token = $this->getToken();
        $payload = Utils::parseJWT($token['access_token'] ?? '');
        return $payload;
    }

    public function getJwksUrl()
    {
        return $this->getItem(StorageEnums::JWKS_URL);
    }

    public function setJwksUrl($jwksUrl)
    {
        $this->setItem(StorageEnums::JWKS_URL, $jwksUrl);
    }
}
