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
    private const IV_LENGTH = 12; // GCM best practice (96-bit)
    
    private static ?self $instance = null;
    private static bool $enabled = false;
    private static bool $strict = false;
    private static ?string $key = null;
    private static string $keyId = 'default';

    /**
     * Enable KSP with automatic setup
     * 
     * @param array $options Configuration options:
     *                      - key: string (custom key)
     *                      - env_var: string (env variable name, default: KINDE_KSP_KEY)
     *                      - auto_generate: bool (auto-generate key if missing, default: true)
     *                      - strict: bool (fail closed if KSP cannot start, default: false)
     */
    public static function enable(array $options = []): bool
    {
        if (self::$enabled) {
            return true;
        }

        $envVar = $options['env_var'] ?? 'KINDE_KSP_KEY';
        $autoGenerate = $options['auto_generate'] ?? true;
        self::$strict = (bool)($options['strict'] ?? false);

        // Require system capabilities before proceeding
        $requirements = self::checkRequirements();
        if (!$requirements['all_passed']) {
            $missing = implode(', ', array_keys(array_filter($requirements, fn($v) => $v === false)));
            $message = "System requirements not met: {$missing}";
            if (self::$strict) {
                throw new \RuntimeException($message);
            }
            error_log("KSP: {$message}");
            return false;
        }

        try {
            // Use provided key
            if (isset($options['key'])) {
                self::$key = self::validateAndPrepareKey($options['key']);
            }
            // Try environment variable
            elseif ($envKey = getenv($envVar) ?: ($_ENV[$envVar] ?? null)) {
                self::$key = self::validateAndPrepareKey($envKey);
            }
            // Auto-generate if allowed (WARNING: process-local unless env is persisted)
            elseif ($autoGenerate && !self::$strict) {
                $newKey = self::generateKey();
                putenv("{$envVar}={$newKey}");
                $_ENV[$envVar] = $newKey;
                self::$key = self::validateAndPrepareKey($newKey);
                error_log("KSP: Auto-generated ephemeral key. For production, set {$envVar} in your environment.");
            } elseif ($autoGenerate && self::$strict) {
                throw new \RuntimeException("No {$envVar} provided in strict mode");
            }

            if (!self::$key) {
                $message = 'No encryption key available';
                if (self::$strict) {
                    throw new \RuntimeException($message);
                }
                error_log("KSP: {$message}");
                return false;
            }

            // Derive a short, non-secret fingerprint for status/rotation (first 8 hex chars)
            self::$keyId = substr(bin2hex(hash('sha256', self::$key, true)), 0, 8);
            self::$enabled = true;
            return true;

        } catch (\Throwable $e) {
            $message = "KSP initialization failed: " . $e->getMessage();
            if (self::$strict) {
                throw new \RuntimeException($message, 0, $e);
            }
            error_log($message);
            return false;
        }
    }

    /**
     * Disable KSP
     */
    public static function disable(): void
    {
        self::$enabled = false;
        self::$strict = false;  // Reset strict mode on disable
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
            if (self::$strict) {
                throw new \RuntimeException('KSP is disabled - cannot encrypt in strict mode');
            }
            return $data; // non-strict fallback
        }

        try {
            $ivLen = openssl_cipher_iv_length(self::CIPHER_METHOD) ?: self::IV_LENGTH;
            $iv = random_bytes($ivLen);
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

            $json = json_encode($payload, JSON_THROW_ON_ERROR);
            return base64_encode($json);

        } catch (\Throwable $e) {
            $msg = "KSP encryption failed: " . $e->getMessage();
            if (self::$strict || self::isEnabled()) {
                throw new \RuntimeException($msg, 0, $e);
            }
            error_log($msg);
            return $data; // only reachable when not enabled
        }
    }

    /**
     * Decrypt data
     */
    public static function decrypt(string $encryptedData): string
    {
        if (!self::isEnabled()) {
            if (self::$strict) {
                throw new \RuntimeException('KSP is disabled - cannot decrypt in strict mode');
            }
            return $encryptedData; // non-strict fallback
        }

        if (!self::looksEncrypted($encryptedData)) {
            if (self::$strict) {
                throw new \RuntimeException('Data does not appear encrypted in strict mode');
            }
            return $encryptedData; // Not encrypted (non-strict fallback)
        }

        try {
            $envelope = base64_decode($encryptedData, true);
            if ($envelope === false) {
                throw new \RuntimeException('Invalid base64 envelope');
            }
            $payload = json_decode($envelope, true, 512, JSON_THROW_ON_ERROR);
            if (!isset($payload['iv'], $payload['tag'], $payload['data'])) {
                throw new \RuntimeException('Invalid encrypted data format');
            }

            $iv = base64_decode($payload['iv'], true);
            $tag = base64_decode($payload['tag'], true);
            $data = base64_decode($payload['data'], true);
            
            // Validate IV/tag lengths for security hardening
            $ivLen = openssl_cipher_iv_length(self::CIPHER_METHOD) ?: self::IV_LENGTH;
            if ($iv === false || strlen($iv) !== $ivLen) {
                throw new \RuntimeException('Invalid IV length');
            }
            $tagLen = is_string($tag) ? strlen($tag) : 0;
            if ($tag === false || $tagLen < 12 || $tagLen > 16) {
                throw new \RuntimeException('Invalid tag length');
            }
            if ($data === false || $data === '') {
                throw new \RuntimeException('Invalid ciphertext');
            }

            $decrypted = openssl_decrypt($data, self::CIPHER_METHOD, self::$key, OPENSSL_RAW_DATA, $iv, $tag);
            if ($decrypted === false) {
                throw new \RuntimeException('Decryption failed');
            }

            return $decrypted;

        } catch (\Throwable $e) {
            $msg = "KSP decryption failed: " . $e->getMessage();
            if (self::$strict) {
                throw new \RuntimeException($msg, 0, $e);
            }
            error_log($msg);
            return $encryptedData; // non-strict fallback
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
            // Provide non-sensitive fingerprint instead
            $result['key_fingerprint'] = self::$keyId ?? null;
        } elseif ($enabled && self::$keyId) {
            // Always provide fingerprint when enabled
            $result['key_fingerprint'] = self::$keyId;
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
