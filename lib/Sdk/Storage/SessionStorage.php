<?php

namespace Kinde\KindeSDK\Sdk\Storage;

class SessionStorage implements StorageInterface
{
    public function getItem(string $key): string
    {
        $this->startSession();
        $rawData = $_SESSION[$key];
        if ($rawData === null) {
            return '';
        }
        $data = json_decode($rawData, true);
        if ($data['exp'] < time()) {
            $this->removeItem($key);
            return '';
        }
        return $data['value'];
    }

    public function setItem(string $key, string $value, int $expires = 0): void
    {
        $this->startSession();
        $_SESSION[$key] = 
            json_encode([
                'key' => $key,
                'value' => $value,
                'exp' => $expires,
            ]
        );
    }

    public function removeItem(string $key): void
    {
        $this->startSession();
        unset($_SESSION[$key]);
    }
    
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}