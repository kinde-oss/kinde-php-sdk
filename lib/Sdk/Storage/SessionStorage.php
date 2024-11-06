<?php

namespace Kinde\KindeSDK\Sdk\Storage;

class SessionStorage implements StorageInterface
{
    public function getItem(string $key): string
    {
        $this->startSession();
        if (!isset($_SESSION[$key])) {
            return '';
        }
        $rawData = $_SESSION[$key];
        /**
         * @var array{'exp': int, 'key': string, 'value': string} $data
         */
        $data = json_decode($rawData, true, 512, JSON_THROW_ON_ERROR);
        if ($data['exp'] > 0 && $data['exp'] < time()) {
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