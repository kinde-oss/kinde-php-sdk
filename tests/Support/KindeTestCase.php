<?php

namespace Kinde\KindeSDK\Tests\Support;

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use PHPUnit\Framework\TestCase;

/**
 * Base test case for Kinde SDK tests.
 * Provides common setup, teardown, and helper methods.
 */
abstract class KindeTestCase extends TestCase
{
    protected const TEST_DOMAIN = 'https://test.kinde.com';
    protected const TEST_REDIRECT_URI = 'http://localhost:8000/callback';
    protected const TEST_CLIENT_ID = 'test_client_id';
    protected const TEST_CLIENT_SECRET = 'test_client_secret';
    protected const TEST_LOGOUT_REDIRECT_URI = 'http://localhost:8000';

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearEnvironment();
        $this->clearStorage();
    }

    protected function tearDown(): void
    {
        $this->clearEnvironment();
        $this->clearStorage();
        parent::tearDown();
    }

    /**
     * Clear all Kinde-related environment variables.
     */
    protected function clearEnvironment(): void
    {
        $envVars = [
            'KINDE_DOMAIN',
            'KINDE_HOST',
            'KINDE_REDIRECT_URI',
            'KINDE_CLIENT_ID',
            'KINDE_CLIENT_SECRET',
            'KINDE_GRANT_TYPE',
            'KINDE_LOGOUT_REDIRECT_URI',
            'KINDE_SCOPES',
            'KINDE_PROTOCOL',
            'KINDE_FORCE_API',
            'KINDE_MANAGEMENT_ACCESS_TOKEN',
            'KINDE_MANAGEMENT_CLIENT_ID',
            'KINDE_MANAGEMENT_CLIENT_SECRET',
        ];

        foreach ($envVars as $var) {
            unset($_ENV[$var]);
            putenv($var);
        }
    }

    /**
     * Clear all storage (cookies).
     */
    protected function clearStorage(): void
    {
        $cookieKeys = [
            StorageEnums::TOKEN,
            StorageEnums::STATE,
            StorageEnums::CODE_VERIFIER,
            StorageEnums::JWKS_CACHE,
            StorageEnums::USER_PROFILE,
        ];

        foreach ($cookieKeys as $key) {
            $cookieName = 'kinde_' . $key;
            unset($_COOKIE[$cookieName]);
        }
    }

    /**
     * Create a KindeClientSDK instance with default test configuration.
     *
     * @param array $overrides Override default configuration values
     * @return KindeClientSDK
     */
    protected function createClient(array $overrides = []): KindeClientSDK
    {
        return new KindeClientSDK(
            $overrides['domain'] ?? self::TEST_DOMAIN,
            $overrides['redirectUri'] ?? self::TEST_REDIRECT_URI,
            $overrides['clientId'] ?? self::TEST_CLIENT_ID,
            $overrides['clientSecret'] ?? self::TEST_CLIENT_SECRET,
            $overrides['grantType'] ?? GrantType::authorizationCode,
            $overrides['logoutRedirectUri'] ?? self::TEST_LOGOUT_REDIRECT_URI,
            $overrides['scopes'] ?? 'openid profile email offline',
            $overrides['additionalParameters'] ?? [],
            $overrides['protocol'] ?? '',
            $overrides['forceApi'] ?? false
        );
    }

    /**
     * Set up storage with a mock token.
     *
     * @param array $accessTokenClaims Claims for the access token
     * @param array $idTokenClaims Claims for the ID token
     */
    protected function setMockToken(
        array $accessTokenClaims = [],
        array $idTokenClaims = []
    ): void {
        $this->seedJwksCache();
        $tokenResponse = MockTokenGenerator::createTokenResponse(
            $accessTokenClaims,
            $idTokenClaims
        );

        $_COOKIE['kinde_' . StorageEnums::TOKEN] = json_encode($tokenResponse);
    }

    /**
     * Seed JWKS cache for JWT parsing in tests.
     */
    protected function seedJwksCache(): void
    {
        Storage::setJwksUrl(self::TEST_DOMAIN . '/.well-known/jwks.json');
        $secret = MockTokenGenerator::getSecretKey();
        $encodedSecret = rtrim(strtr(base64_encode($secret), '+/', '-_'), '=');
        $jwks = [
            'keys' => [
                [
                    'kty' => 'oct',
                    'k' => $encodedSecret,
                    'alg' => MockTokenGenerator::getAlgorithm(),
                    'use' => 'sig',
                    'kid' => MockTokenGenerator::getKeyId(),
                ],
            ],
        ];
        Storage::setCachedJwks($jwks, 3600);
    }

    /**
     * Set mock token with roles.
     *
     * @param array $roles Array of role objects
     * @param array $additionalClaims Additional claims
     */
    protected function setMockTokenWithRoles(array $roles, array $additionalClaims = []): void
    {
        $this->setMockToken(array_merge(['roles' => $roles], $additionalClaims));
    }

    /**
     * Set mock token with permissions.
     *
     * @param array $permissions Array of permission strings
     * @param string|null $orgCode Organization code
     * @param array $additionalClaims Additional claims
     */
    protected function setMockTokenWithPermissions(
        array $permissions,
        ?string $orgCode = null,
        array $additionalClaims = []
    ): void {
        $claims = ['permissions' => $permissions];
        
        if ($orgCode !== null) {
            $claims['org_code'] = $orgCode;
        }

        $this->setMockToken(array_merge($claims, $additionalClaims));
    }

    /**
     * Set mock token with feature flags.
     *
     * @param array $featureFlags Associative array of flag configurations
     * @param array $additionalClaims Additional claims
     */
    protected function setMockTokenWithFeatureFlags(
        array $featureFlags,
        array $additionalClaims = []
    ): void {
        $this->setMockToken(array_merge(['feature_flags' => $featureFlags], $additionalClaims));
    }

    /**
     * Set mock token with combined authorization data.
     *
     * @param array $options Options with roles, permissions, feature_flags, org_code
     */
    protected function setMockTokenWithAll(array $options): void
    {
        $this->setMockToken($options);
    }

    /**
     * Create standard role objects for testing.
     *
     * @param array $roleKeys Array of role keys to create
     * @return array Array of role objects
     */
    protected function createRoles(array $roleKeys): array
    {
        return array_map(function ($key, $index) {
            return [
                'id' => (string) ($index + 1),
                'key' => $key,
                'name' => ucfirst($key),
            ];
        }, $roleKeys, array_keys($roleKeys));
    }

    /**
     * Create feature flag configuration for testing.
     *
     * @param string $key Flag key
     * @param mixed $value Flag value
     * @param string $type Flag type ('b' for boolean, 's' for string, 'i' for integer)
     * @return array Feature flag configuration
     */
    protected function createFeatureFlag(string $key, $value, string $type = 'b'): array
    {
        return [$key => ['v' => $value, 't' => $type]];
    }
}

