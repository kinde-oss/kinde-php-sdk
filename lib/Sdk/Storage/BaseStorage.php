<?php

namespace Kinde\KindeSDK\Sdk\Storage;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;

class BaseStorage
{
   protected static $prefix = 'kinde';
    protected static $m2mPrefix = 'kinde_m2m';
    protected static $storage;
    protected static $useM2M = false;
    private static $cookieHttpOnly = true;
    private static $cookiePath = "/";
    private static $cookieDomain = "";

    static function getStorage()
    {
        if (self::$useM2M) {
            return self::$storage ?? null;
        }
        
        if (empty(self::$storage)) {
            self::$storage = $_COOKIE[self::$prefix] ?? null;
        }
        return self::$storage;
    }

    public static function getItem(string $key)
    {
       if (self::$useM2M) {
           return self::$storage ?? "";
       }
        $value = $_COOKIE[self::getKey($key)] ?? "";
        return $value;
    }

    public static function setItem(
        string $key,
        string $value,
        int $expires = 0,
        string $path = null,
        string $domain = null,
        bool $secure = true,
        bool $httpOnly = null
    ) {
        $newKey = self::getKey($key);
        
        if (self::$useM2M) {
            self::$storage = $value;
            return;
        }

        $_COOKIE[$newKey] = $value;
        setcookie($newKey, $value, [
            'expires' => $expires,
            'path' => $path ?? self::$cookiePath,
            'domain' => $domain ?? self::$cookieDomain,
            'samesite' => 'Lax',
            'secure' => $secure,
            'httponly' => $httpOnly ?? self::$cookieHttpOnly
        ]);
    }

    public static function removeItem(string $key)
    {
        if (Storage::isM2MMode() && $key === StorageEnums::TOKEN) {
            return;
        }
        
        $newKey = self::getKey($key);
        if (isset($_COOKIE[$newKey])) {
            unset($_COOKIE[$newKey]);
            self::setItem($key, "", -1);
        }
    }

    public static function clear()
    {
        $prefix = self::$useM2M ? self::$m2mPrefix : self::$prefix;
        foreach ($_COOKIE as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                self::removeItem($key);
            }
        }
       if (self::$useM2M) {
           self::$storage = null;
       }
    }

    private static function getKey($key)
    {
         if (empty($key)) {
             throw new \InvalidArgumentException('Key cannot be empty');
         }
         // Sanitize key to prevent injection
         $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
         if (strlen($key) > 255) {
             throw new \InvalidArgumentException('Key length exceeds maximum limit');
         }
        $prefix = self::$useM2M ? self::$m2mPrefix : self::$prefix;
        $finalKey = $prefix . '_' . $key;
        return $finalKey;
    }

    public static function setCookieHttpOnly(bool $httpOnly)
    {
        self::$cookieHttpOnly = $httpOnly;
    }

    public static function setCookiePath($cookiePath)
    {
        self::$cookiePath = $cookiePath;
    }

    public static function setCookieDomain($cookieDomain)
    {
        self::$cookieDomain = $cookieDomain;
    }
}