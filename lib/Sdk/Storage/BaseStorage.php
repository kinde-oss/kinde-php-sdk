<?php

namespace Kinde\KindeSDK\Sdk\Storage;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;

class BaseStorage
{
    protected static $prefix = 'kinde';
    protected static $storage;
    private static $cookieHttpOnly = true;
    private static $cookiePath = "/";
    private static $cookieDomain = "";

    protected static function setPrefix($prefix)
    {
        if (!empty($prefix)) {
            self::$prefix = $prefix;
        }
    }

    static function getStorage()
    {
        if (empty(self::$storage)) {
            self::$storage = $_COOKIE[self::$prefix] ?? null;
        }
        return self::$storage;
    }

    public static function getItem(string $key)
    {
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

    private static function getKey($key)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Key cannot be empty');
        }
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        if (strlen($key) > 255) {
            throw new \InvalidArgumentException('Key length exceeds maximum limit');
        }
        return self::$prefix . '_' . $key;
    }

    public static function removeItem(string $key)
    {
        $newKey = self::getKey($key);
        if (isset($_COOKIE[$newKey])) {
            unset($_COOKIE[$newKey]);
            self::setItem($key, "", -1);
        }
    }

    public static function clear()
    {
        foreach ($_COOKIE as $key => $value) {
            if (strpos($key, self::$prefix) === 0) {
                self::removeItem($key);
            }
        }
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