<?php

namespace Kinde\KindeSDK\Sdk\Utils;

use InvalidArgumentException;
use Kinde\KindeSDK\Sdk\Enums\AdditionalParameters;
use Exception;

class Utils
{
    static public $listType = [
        's' => 'string',
        'i' => 'integer',
        'b' => 'boolean'
    ];
    /**
     * It converts a string to base64url format.
     * 
     * @param string str The string to be encoded.
     * 
     * @return The base64url encoded string.
     */
    static public function base64UrlEncode(string $str)
    {
        $base64 = base64_encode($str);
        $base64 = trim($base64, "=");
        $base64url = strtr($base64, '+/', '-_');
        return ($base64url);
    }

    /**
     * It returns the SHA256 hash of the input string.
     * 
     * @param str The string to be hashed.
     * 
     * @return hash The hash of the string.
     */
    static public function sha256($str)
    {
        return hash('sha256', $str);
    }

    /**
     * It generates a random string of a given length.
     * 
     * @param int length The length of the string to be generated.
     * 
     * @return string A random string of 32 characters.
     */
    static public function randomString(int $length = 32)
    {
        return self::base64UrlEncode(pack('H*', bin2hex(openssl_random_pseudo_bytes($length))));
    }

    /**
     * It generates a random string, hashes it, and then encodes it
     * 
     * @return array An array with the state, codeVerifier, and codeChallenge.
     */
    static public function generateChallenge()
    {
        $state = self::randomString();
        $codeVerifier = self::randomString();
        $codeChallenge = self::base64UrlEncode(pack('H*', self::sha256($codeVerifier)));
        return [
            'state' => $state,
            'codeVerifier' => $codeVerifier,
            'codeChallenge' => $codeChallenge
        ];
    }

    /**
     * It checks if the string is a valid URL
     * 
     * @param string url The URL to validate.
     * 
     * @return boolean A boolean value.
     */
    static public function validationURL(string $url)
    {
        $pattern = "/https?:\/\/(?:w{1,3}\.)?[^\s.]+(?:\.[a-z]+)*(?::\d+)?(?![^<]*(?:<\/\w+>|\/?>))/";
        return preg_match($pattern, $url);
    }

    /**
     * It parse payload jwt
     *
     * @param string token jwt
     *
     * @return array A array value.
     */
    static public function parseJWT(string $token)
    {
        try {
            return json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))), true);
        } catch (Exception $e) {
            return null;
        }
    }

    static public function checkAdditionalParameters(array $additionalParameters)
    {
        $keyExists = array_keys($additionalParameters);
        if (empty($keyExists)) {
            return [];
        }
        $additionalParametersValid = AdditionalParameters::ADDITIONAL_PARAMETER;
        $keysAvailable = array_keys($additionalParametersValid);
        foreach ($keyExists as $key) {
            if (!in_array($key, $keysAvailable)) {
                throw new InvalidArgumentException("Please provide correct additional, $key");
            }
            if (gettype($additionalParameters[$key]) != $additionalParametersValid[$key]) {
                throw new InvalidArgumentException("Please supply a valid $key. Expected: $additionalParametersValid[$key]");
            }
        }
        return $additionalParameters;
    }

    static public function addAdditionalParameters(array $target, array $additionalParameters)
    {
        $newAdditionalParameters = self::checkAdditionalParameters($additionalParameters);
        if (!empty($newAdditionalParameters)) {
            $target = array_merge($target, $newAdditionalParameters);
        }
        return $target;
    }
}
