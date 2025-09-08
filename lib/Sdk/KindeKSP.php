<?php

namespace Kinde\KindeSDK\Sdk;

/**
 * Kinde KSP - Minimal Key Storage Provider
 * 
 * Simple, secure token encryption with one-line activation.
 * Everything you need in a single file.
 */
class KindeKSP
{
    private const CIPHER_METHOD = 'aes-256-gcm';
    private const KEY_LENGTH = 32;
    private const IV_LENGTH = 16;
    
    private static ?self $instance = null;
    private static bool $enabled = false;
    private static ?string $key = null;
    private static string $keyId = 'default';

    /**
     * Enable KSP with automatic setup
     * 
     * @param array $options Configuration options:
     *                      - key: string (custom key)
     *                      - env_var: string (env variable name, default: KINDE_KSP_KEY)
     *                      - auto_generate: bool (auto-generate key if missing, default: true)
     */
    public static function enable(array $options = []): bool
    {
        if (self::$enabled) {
            return true;
        }

        $envVar = $options['env_var'] ?? 'KINDE_KSP_KEY';
        $autoGenerate = $options['auto_generate'] ?? true;

        try {
            // Use provided key
            if (isset($options['key'])) {
                self::$key = self::validateAndPrepareKey($options['key']);
            }
            // Try environment variable
            elseif ($envKey = getenv($envVar) ?: ($_ENV[$envVar] ?? null)) {
                self::$key = self::validateAndPrepareKey($envKey);
            }
            // Auto-generate if allowed
            elseif ($autoGenerate) {
                $newKey = self::generateKey();
                putenv("{$envVar}={$newKey}");
                $_ENV[$envVar] = $newKey;
                self::$key = self::validateAndPrepareKey($newKey);
            }

            if (!self::$key) {
                throw new \RuntimeException('No encryption key available');
            }

            self::$enabled = true;
            return true;

        } catch (\Throwable $e) {
            error_log("KSP initialization failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable KSP
     */
    public static function disable(): void
    {
        self::$enabled = false;
        self::$key = null;
    }

    /**
     * Check if KSP is enabled
     */
    public static function isEnabled(): bool
    {
        return self::$enabled && self::$key !== null;
    }

    /**
     * Encrypt data
     */
    public static function encrypt(string $data): string
    {
        if (!self::isEnabled()) {
            return $data; // Graceful fallback
        }

        try {
            $iv = random_bytes(self::IV_LENGTH);
            $tag = '';
            
            $encrypted = openssl_encrypt($data, self::CIPHER_METHOD, self::$key, OPENSSL_RAW_DATA, $iv, $tag);
            if ($encrypted === false) {
                throw new \RuntimeException('Encryption failed');
            }

            // Create payload with metadata
            $payload = [
                'v' => 1, // version
                'k' => self::$keyId, // key ID
                'iv' => base64_encode($iv),
                'tag' => base64_encode($tag),
                'data' => base64_encode($encrypted)
            ];

            return base64_encode(json_encode($payload));

        } catch (\Throwable $e) {
            error_log("KSP encryption failed: " . $e->getMessage());
            return $data; // Graceful fallback
        }
    }

    /**
     * Decrypt data
     */
    public static function decrypt(string $encryptedData): string
    {
        if (!self::isEnabled()) {
            return $encryptedData; // Graceful fallback
        }

        if (!self::looksEncrypted($encryptedData)) {
            return $encryptedData; // Not encrypted
        }

        try {
            $payload = json_decode(base64_decode($encryptedData), true);
            if (!$payload || !isset($payload['iv'], $payload['tag'], $payload['data'])) {
                throw new \RuntimeException('Invalid encrypted data format');
            }

            $iv = base64_decode($payload['iv']);
            $tag = base64_decode($payload['tag']);
            $data = base64_decode($payload['data']);

            $decrypted = openssl_decrypt($data, self::CIPHER_METHOD, self::$key, OPENSSL_RAW_DATA, $iv, $tag);
            if ($decrypted === false) {
                throw new \RuntimeException('Decryption failed');
            }

            return $decrypted;

        } catch (\Throwable $e) {
            error_log("KSP decryption failed: " . $e->getMessage());
            return $encryptedData; // Graceful fallback
        }
    }

    /**
     * Generate a new encryption key
     */
    public static function generateKey(): string
    {
        return base64_encode(random_bytes(self::KEY_LENGTH));
    }

    /**
     * Get status information
     */
    public static function getStatus(): array
    {
        return [
            'enabled' => self::$enabled,
            'key_available' => self::$key !== null,
            'key_id' => self::$keyId,
            'cipher_method' => self::CIPHER_METHOD,
            'requirements_met' => self::checkRequirements()['all_passed']
        ];
    }

    /**
     * Quick setup with status report
     */
    public static function quickSetup(): array
    {
        $requirements = self::checkRequirements();
        $result = [
            'requirements' => $requirements,
            'key_generated' => false,
            'key_existed' => false,
            'enabled' => false,
            'status' => []
        ];

        if (!$requirements['all_passed']) {
            $result['error'] = 'System requirements not met';
            return $result;
        }

        $keyExists = getenv('KINDE_KSP_KEY') ?: ($_ENV['KINDE_KSP_KEY'] ?? null);
        $result['key_existed'] = (bool) $keyExists;
        
        $enabled = self::enable();
        $result['enabled'] = $enabled;
        $result['status'] = self::getStatus();

        if (!$keyExists && $enabled) {
            $result['key_generated'] = true;
            $result['key_preview'] = substr($_ENV['KINDE_KSP_KEY'] ?? '', 0, 20) . '...';
        }

        return $result;
    }

    /**
     * Check system requirements
     */
    private static function checkRequirements(): array
    {
        $requirements = [
            'openssl' => extension_loaded('openssl'),
            'json' => extension_loaded('json'),
            'random_bytes' => function_exists('random_bytes'),
        ];
        
        $requirements['all_passed'] = array_reduce($requirements, function($carry, $item) {
            return $carry && $item;
        }, true);
        
        return $requirements;
    }

    /**
     * Validate and prepare encryption key
     */
    private static function validateAndPrepareKey(string $key): string
    {
        $decoded = base64_decode($key, true);
        if ($decoded === false || strlen($decoded) !== self::KEY_LENGTH) {
            throw new \InvalidArgumentException('Invalid key format or length');
        }
        return $decoded;
    }

    /**
     * Check if data looks encrypted
     */
    private static function looksEncrypted(string $data): bool
    {
        $decoded = base64_decode($data, true);
        if ($decoded === false) return false;
        
        $json = json_decode($decoded, true);
        return is_array($json) && isset($json['v'], $json['iv'], $json['tag'], $json['data']);
    }

    // =========================================================================
    // INTEGRATION HELPERS - For seamless integration with existing Kinde SDK
    // =========================================================================

    /**
     * Integration hook for storage operations
     * Call this from Storage class to automatically encrypt/decrypt
     */
    public static function storageHook(string $operation, string $data): string
    {
        return match($operation) {
            'store' => self::encrypt($data),
            'retrieve' => self::decrypt($data),
            default => $data
        };
    }

    /**
     * Wrap existing storage for automatic encryption
     */
    public static function wrapStorage($storage)
    {
        return new class($storage) {
            private $storage;

            public function __construct($storage) {
                $this->storage = $storage;
            }

            public function setItem(string $key, $value): void
            {
                if (is_string($value)) {
                    $value = KindeKSP::encrypt($value);
                }
                $this->storage->setItem($key, $value);
            }

            public function getItem(string $key, $default = null)
            {
                $value = $this->storage->getItem($key, $default);
                if (is_string($value) && $value !== $default) {
                    $value = KindeKSP::decrypt($value);
                }
                return $value;
            }

            public function removeItem(string $key): void
            {
                $this->storage->removeItem($key);
            }

            public function clear(): void
            {
                $this->storage->clear();
            }

            // Proxy all other methods to original storage
            public function __call($method, $args)
            {
                return call_user_func_array([$this->storage, $method], $args);
            }
        };
    }
}
