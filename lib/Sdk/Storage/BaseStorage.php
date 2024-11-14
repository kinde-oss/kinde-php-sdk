<?php

namespace Kinde\KindeSDK\Sdk\Storage;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;

class BaseStorage
{
    static $prefix = 'kinde';
    static $m2mPrefix = 'kinde_m2m';
    static $storage;
    protected static $useM2M = false;
    private static $cookieHttpOnly = true;
    private static $cookiePath = "/";
    private static $cookieDomain = "";

    static function getStorage()
    {
        $prefix = self::$useM2M ? self::$m2mPrefix : self::$prefix;
        if (empty(self::$storage)) {
            self::$storage = $_COOKIE[$prefix] ?? null;
        }
        return self::$storage;
    }

    public static function getItem(string $key)
    {
        return $_COOKIE[self::getKey($key)] ?? "";
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
        error_log('BaseStorage::setItem called - Key: ' . $key);
        error_log('Value: ' . substr($value, 0, 50) . '...');

        $newKey = self::getKey($key);
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
        error_log('BaseStorage::removeItem called - Key: ' . $key);
        
        if (Storage::isM2MMode() && $key === StorageEnums::TOKEN) {
            error_log('Skipping token removal due to M2M mode');
            return;
        }
        
        $newKey = self::getKey($key);
        if (isset($_COOKIE[$newKey])) {
            error_log('Removing cookie: ' . $newKey);
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
    }

    private static function getKey($key)
    {
        $prefix = self::$useM2M ? self::$m2mPrefix : self::$prefix;
        return $prefix . '_' . $key;
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