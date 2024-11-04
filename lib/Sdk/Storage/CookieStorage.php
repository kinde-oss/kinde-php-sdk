<?php

namespace Kinde\KindeSDK\Sdk\Storage;

class CookieStorage implements StorageInterface
{
    static $prefix = 'kinde';
    public static $cookieHttpOnly = true;
    public static $cookiePath = "/";
    public static $cookieDomain = "";
    public static bool $cookieSecure = true;
    
    public function getItem(string $key): string
    {
        return $_COOKIE[self::getKey($key)] ?? "";
    }

    public function setItem(string $key, string $value, int $expires = 0): void
    {
        $newKey = self::getKey($key);
        $_COOKIE[$newKey] = $value;
        setcookie($newKey, $value, [
            'expires' => $expires,
            'path' => self::$cookiePath,
            'domain' => self::$cookieDomain,
            'samesite' => 'Lax',
            'secure' => self::$cookieSecure,
            'httponly' => self::$cookieHttpOnly
        ]);
    }

    public function removeItem(string $key): void
    {
        $newKey = self::getKey($key);
        if (isset($_COOKIE[$newKey])) {
            unset($_COOKIE[$newKey]);
            self::setItem($key, "", -1);
        }
        self::setItem($key, "", -1);
    }

    private function getKey($key)
    {
        return self::$prefix . '_' . $key;
    }
}