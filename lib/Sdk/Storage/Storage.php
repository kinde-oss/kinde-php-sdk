<?php

namespace Kinde\KindeSDK\Sdk\Storage;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Utils\Utils;

class Storage extends BaseStorage
{
    public static $instance;
    private static $jwksUrl;
    private static $tokenTimeToLive;
    private static $m2mToken = null;
    private static $m2mTokenExpiry = null;

    
    public static function getInstance()
    {
        if (empty(self::$instance) || !(self::$instance instanceof Storage)) {
            self::$instance = new Storage();
        }
        return self::$instance;
    }

    static function getToken($associative = true)
    {
        if (self::isM2MMode()) {
            if (self::$m2mToken !== null && self::$m2mTokenExpiry > time()) {
                return json_decode(self::$m2mToken, $associative);
            }
            return null;
        }
        
        $token = self::getItem(StorageEnums::TOKEN);
        return empty($token) ? null : json_decode($token, $associative);
    }

    static function setToken($token)
    {
        if (self::isM2MMode()) {
            self::$m2mToken = gettype($token) == 'string' ? $token : json_encode($token);
            self::$m2mTokenExpiry = time() + 3600;
            return true;
        }
        
        return self::setItem(
            StorageEnums::TOKEN, 
            gettype($token) == 'string' ? $token : json_encode($token), 
            self::getTokenTimeToLive()
        );
    }

    public static function setM2MMode($isM2M)
    {
        self::$useM2M = $isM2M;
    }

    public static function isM2MMode()
    {
        return self::$useM2M;
    }

    static function getAccessToken()
    {
        $token = self::getToken();
        return empty($token) ? null : $token['access_token'];
    }

    static function getIdToken()
    {
        $token = self::getToken();
        return empty($token) ? null : $token['id_token'];
    }

    static function getRefreshToken()
    {
        $token = self::getToken();
        return empty($token) ? null : $token['refresh_token'];
    }

    static function getExpiredAt()
    {
        $accessToken = self::getAccessToken();

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

    static function getTokenTimeToLive()
    {
        return !empty(self::$tokenTimeToLive) ? self::$tokenTimeToLive : time() + 3600 * 24 * 15; // Live in 15 days
    }

    static function setTokenTimeToLive($tokenTimeToLive)
    {
        self::$tokenTimeToLive = $tokenTimeToLive;
    }

    static function getState()
    {
        return self::getItem(StorageEnums::STATE);
    }

    static function setState($newState)
    {
        return self::setItem(StorageEnums::STATE, $newState, time() + 3600 * 2); // expired in 2hrs
    }

    static function getCodeVerifier()
    {
        return self::getItem(StorageEnums::CODE_VERIFIER);
    }

    static function setCodeVerifier($newCodeVerifier)
    {
        return self::setItem(StorageEnums::CODE_VERIFIER, $newCodeVerifier, time() + 3600 * 2); // expired in 2hrs);
    }

    static function getUserProfile()
    {
        $token = self::getToken();
        $payload = Utils::parseJWT($token['id_token'] ?? '');
        return [
            'id' => $payload['sub'] ?? '',
            'given_name' => $payload['given_name'] ?? '',
            'family_name' => $payload['family_name'] ?? '',
            'email' => $payload['email'] ?? '',
            'picture' => $payload['picture'] ?? '',
        ];
    }

    static function getDecodedIdToken()
    {
        $token = self::getToken();
        $payload = Utils::parseJWT($token['id_token'] ?? '');
        return $payload;
    }

    static function getDecodedAccessToken()
    {
        $token = self::getToken();
        $payload = Utils::parseJWT($token['access_token'] ?? '');
        return $payload;
    }

    static function getJwksUrl()
    {
        if (!self::$jwksUrl) {
          throw new \LogicException('No jwks url has been specified');
        }

        return self::$jwksUrl;
    }

    static function setJwksUrl($jwksUrl)
    {
        self::$jwksUrl = $jwksUrl;
    }
}