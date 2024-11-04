<?php

namespace Kinde\KindeSDK\Sdk\Storage;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;

class BaseStorage
{
    static $storage;
    public static StorageInterface|null $innerStorage = null;
    
    protected function __construct()
    {
        if (self::$innerStorage === null) {
            self::$innerStorage = new CookieStorage();
        }
    }
    
    // @deprecated not used in sdk, low level storage, should be removed
    static function getStorage()
    {
        if (empty(self::$storage)) {
            self::$storage = $_COOKIE['kinde'];
        }
        return self::$storage;
    }

    public function getItem(string $key): string
    {
        return self::$innerStorage->getItem($key);
    }

    public function setItem(
        string $key,
        string $value,
        int $expires = 0,
    ): void
    {
        self::$innerStorage->setItem($key, $value, $expires);
    }

    public function removeItem(string $key): void
    {
        self::$innerStorage->removeItem($key);
    }

    public function clear()
    {
        self::removeItem(StorageEnums::TOKEN);
        self::removeItem(StorageEnums::STATE);
        self::removeItem(StorageEnums::CODE_VERIFIER);
        self::removeItem(StorageEnums::USER_PROFILE);
    }

    public function setCookieHttpOnly(bool $httpOnly)
    {
        if (self::$innerStorage instanceof CookieStorage) {
            self::$innerStorage::$cookieHttpOnly = $httpOnly;
        }
    }

    public function setCookiePath($cookiePath)
    {
        if (self::$innerStorage instanceof CookieStorage) {
            self::$innerStorage::$cookiePath = $cookiePath;
        }
    }

    public function setCookieDomain($cookieDomain)
    {
        if (self::$innerStorage instanceof CookieStorage) {
            self::$innerStorage::$cookieDomain = $cookieDomain;
        }
    }
}