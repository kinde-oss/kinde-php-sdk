<?php

namespace Kinde\KindeSDK\Test\Sdk\Storage;

use Kinde\KindeSDK\Test\Sdk\Enums\StorageEnums;

class BaseStorage
{
    static $prefix = 'kinde';
    static $storage;

    static $COOKIE_FAKE = [];

    static function getStorage()
    {
        if (empty(self::$storage)) {
            self::$storage = self::$COOKIE_FAKE['kinde'];
        }
        return self::$storage;
    }

    public static function getItem(string $key)
    {
        return self::$COOKIE_FAKE[self::getKey($key)] ?? "";
    }

    public static function setItem(
        string $key,
        string $value,
        int $expires_or_options = 0,
        string $path = "",
        string $domain = "",
        bool $secure = true,
        bool $httpOnly = false
    ) {
        $newKey = self::getKey($key);
        self::$COOKIE_FAKE[$newKey] = $value;
        return 'ok';
    }

    public static function removeItem(string $key)
    {
        $newKey = self::getKey($key);
        if (isset(self::$COOKIE_FAKE[$newKey])) {
            unset(self::$COOKIE_FAKE[$newKey]);
            self::setItem($key, "", -1);
        }
        self::setItem($key, "", -1);
    }

    public static function clear()
    {
        self::removeItem(StorageEnums::TOKEN);
        self::removeItem(StorageEnums::STATE);
        self::removeItem(StorageEnums::CODE_VERIFIER);
        self::removeItem(StorageEnums::USER_PROFILE);
    }

    private static function getKey($key)
    {
        return self::$prefix . '_' . $key;
    }
}
