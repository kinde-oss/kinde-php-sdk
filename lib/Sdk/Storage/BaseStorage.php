<?php

namespace Kinde\KindeSDK\Sdk\Storage;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;

class BaseStorage
{
    static $prefix = 'kinde';
    static $storage;

    static function getStorage()
    {
        if (empty(self::$storage)) {
            self::$storage = $_COOKIE['kinde'];
        }
        return self::$storage;
    }

    public static function getItem(string $key)
    {
        return $_COOKIE[self::getKey($key)] ?? "";
    }

    public static function setItem(
        string $key,
        $value,
        $expires_or_options = 0,
        $path = null,
        $domain = null,
        $secure = true,
        $httpOnly = false
    ) {
        $newKey = self::getKey($key);
        $_COOKIE[$newKey] = $value;
        setcookie($newKey, $value ?? "", $expires_or_options, $path, $domain, $secure, $httpOnly);
    }

    public static function removeItem(string $key)
    {
        $newKey = self::getKey($key);
        if (isset($_COOKIE[$newKey])) {
            unset($_COOKIE[$newKey]);
            self::setItem($key, null, -1);
        }
    }

    public static function clear()
    {
        self::removeItem(StorageEnums::TOKEN);
        self::removeItem(StorageEnums::STATE);
        self::removeItem(StorageEnums::CODE_VERIFIER);
        self::removeItem(StorageEnums::AUTH_STATUS);
        self::removeItem(StorageEnums::USER_PROFILE);
    }

    private static function getKey($key)
    {
        return self::$prefix . '_' . $key;
    }
}
