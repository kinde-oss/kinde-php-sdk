<?php

namespace Kinde\KindeSDK\Sdk\Storage;

interface StorageInterface
{
    public function getItem(string $key): string;

    public function setItem(
        string $key,
        string $value,
        int $expires = 0,
    ): void;

    public function removeItem(string $key): void;
}