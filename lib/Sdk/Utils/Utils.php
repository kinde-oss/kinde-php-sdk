<?php

namespace Kinde\KindeSDK\Sdk\Utils;

use InvalidArgumentException;
use Kinde\KindeSDK\Sdk\Enums\AdditionalParameters;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Exception;

class Utils
{
    static public $listType = [
        's' => 'string',
        'i' => 'integer',
        'b' => 'boolean'
    ];
    
    /**
     * Encodes a string using Base64 URL encoding.
     *
     * @param string $str The string to be encoded.
     *
     * @return string The Base64 URL encoded string.
     */
    static public function base64UrlEncode(string $str)
    {
        $base64 = base64_encode($str);
        $base64 = trim($base64, "=");
        $base64url = strtr($base64, '+/', '-_');
        return ($base64url);
    }

    /**
     * Calculates the SHA-256 hash of a string.
     *
     * @param string $str The string to calculate the hash for.
     *
     * @return string The SHA-256 hash of the string.
     */
    static public function sha256($str)
    {
        return hash('sha256', $str);
    }

    /**
     * Generates a random string of the specified length.
     *
     * @param int $length The length of the random string. Default is 32.
     *
     * @return string The randomly generated string.
     */
    static public function randomString(int $length = 32)
    {
        return self::base64UrlEncode(pack('H*', bin2hex(openssl_random_pseudo_bytes($length))));
    }

    /**
     * Generates a challenge for OAuth 2.0 PKCE (Proof Key for Code Exchange).
     *
     * @return array An associative array containing the generated state, code verifier, and code challenge.
     *               The array structure is ['state' => $state, 'codeVerifier' => $codeVerifier, 'codeChallenge' => $codeChallenge].
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
     * Validates a URL using a regular expression pattern.
     *
     * @param string $url The URL to validate.
     *
     * @return bool Returns true if the URL is valid, false otherwise.
     */
    static public function validationURL(string $url)
    {
        $pattern = "/https?:\/\/(?:w{1,3}\.)?[^\s.]+(?:\.[a-z]+)*(?::\d+)?(?![^<]*(?:<\/\w+>|\/?>))/";
        return preg_match($pattern, $url);
    }

    /**
     * Parses a JSON Web Token (JWT) and returns the decoded payload.
     *
     * @param string      $token   The JWT to parse.
     * @param string|null $jwksUrl JWKS endpoint URL. Falls back to the configured URL when null.
     *
     * @return array|null The decoded payload as an associative array, or null if the token is invalid.
     */
    static public function parseJWT(string $token, ?string $jwksUrl = null)
    {
        $jwks = null;
        $jwks_url = $jwksUrl;

        try {
            if ($jwks_url === null) {
                $jwks_url = Storage::getInstance()->getJwksUrl();
            }
            
            // Try to get cached JWKS first
            $jwks = Storage::getInstance()->getCachedJwks($jwks_url);
            
            if ($jwks === null) {
                // Cache miss - fetch from server
                $jwks_json = file_get_contents($jwks_url);
                $jwks = json_decode($jwks_json, true);
                
                if ($jwks && isset($jwks['keys'])) {
                    // Cache the JWKS for 1 hour (3600 seconds)
                    Storage::getInstance()->setCachedJwks($jwks, 3600, $jwks_url);
                }
            }

            if (!$jwks || !isset($jwks['keys'])) {
                throw new Exception('Invalid JWKS data');
            }

            return json_decode(json_encode(JWT::decode($token, JWK::parseKeySet($jwks))), true);
        } catch (Exception $e) {
            // If parsing fails with cached JWKS, try to refresh from server
            if ($jwks !== null) {
                try {
                    Storage::getInstance()->clearCachedJwks($jwks_url);
                    $jwks_json = file_get_contents($jwks_url);
                    $jwks = json_decode($jwks_json, true);
                    
                    if ($jwks && isset($jwks['keys'])) {
                        Storage::getInstance()->setCachedJwks($jwks, 3600, $jwks_url);
                        return json_decode(json_encode(JWT::decode($token, JWK::parseKeySet($jwks))), true);
                    }
                } catch (Exception $refreshException) {
                    // If refresh also fails, return null
                    return null;
                }
            }
            return null;
        }
    }

    /**
     * Checks and validates additional parameters provided as an associative array.
     *
     * @param array $additionalParameters An associative array of additional parameters to check.
     *
     * @return array The validated additional parameters.
     *
     * @throws InvalidArgumentException If any additional parameter is incorrect or has an invalid type.
     */
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
    
    /**
     * Adds additional parameters to a target array after validating them.
     *
     * @param array $target              The target array to which additional parameters will be added.
     * @param array $additionalParameters An associative array of additional parameters to add.
     *
     * @return array The updated target array with the added additional parameters.
     *
     * @throws InvalidArgumentException If any additional parameter is incorrect or has an invalid type.
     */
    static public function addAdditionalParameters(array $target, array $additionalParameters)
    {
        $newAdditionalParameters = self::checkAdditionalParameters($additionalParameters);
        if (!empty($newAdditionalParameters)) {
            $target = array_merge($target, $newAdditionalParameters);
        }
        return $target;
    }
}
