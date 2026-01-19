<?php

namespace Kinde\KindeSDK\Tests\Support;

use Firebase\JWT\JWT;

/**
 * Mock token generator for testing purposes.
 * Mirrors the js-utils createMockAccessToken utility.
 */
class MockTokenGenerator
{
    private const SECRET_KEY = 'test-secret-key-for-unit-tests';
    private const ALGORITHM = 'HS256';

    /**
     * Base mock access token payload.
     * Matches js-utils baseMockAccessToken structure.
     */
    private static array $baseAccessTokenPayload = [
        'aud' => [],
        'azp' => 'test_client_id',
        'billing' => [
            'has_payment_details' => false,
        ],
        'iss' => 'https://test.kinde.com',
        'jti' => '27daa125-2fb2-4e14-9270-742cd56e764b',
        'org_code' => 'org_123456789',
        'scp' => ['openid', 'profile', 'email', 'offline'],
        'sub' => 'kp_test_user_id',
    ];

    /**
     * Base mock ID token payload.
     */
    private static array $baseIdTokenPayload = [
        'aud' => 'test_client_id',
        'iss' => 'https://test.kinde.com',
        'sub' => 'kp_test_user_id',
        'given_name' => 'Test',
        'family_name' => 'User',
        'email' => 'test@example.com',
        'picture' => 'https://example.com/avatar.jpg',
    ];

    /**
     * Creates a mock access token JWT.
     *
     * @param array $claims Additional claims to merge into the token
     * @return string The encoded JWT
     */
    public static function createAccessToken(array $claims = []): string
    {
        $payload = array_merge(
            self::$baseAccessTokenPayload,
            [
                'iat' => time(),
                'exp' => time() + 3600,
            ],
            $claims
        );

        return JWT::encode($payload, self::SECRET_KEY, self::ALGORITHM);
    }

    /**
     * Creates a mock ID token JWT.
     *
     * @param array $claims Additional claims to merge into the token
     * @return string The encoded JWT
     */
    public static function createIdToken(array $claims = []): string
    {
        $payload = array_merge(
            self::$baseIdTokenPayload,
            [
                'iat' => time(),
                'exp' => time() + 3600,
            ],
            $claims
        );

        return JWT::encode($payload, self::SECRET_KEY, self::ALGORITHM);
    }

    /**
     * Creates a complete token response object.
     *
     * @param array $accessTokenClaims Claims for access token
     * @param array $idTokenClaims Claims for ID token
     * @return array Token response structure
     */
    public static function createTokenResponse(
        array $accessTokenClaims = [],
        array $idTokenClaims = []
    ): array {
        return [
            'access_token' => self::createAccessToken($accessTokenClaims),
            'id_token' => self::createIdToken($idTokenClaims),
            'refresh_token' => 'mock_refresh_token_' . bin2hex(random_bytes(16)),
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ];
    }

    /**
     * Creates an access token with roles.
     *
     * @param array $roles Array of role objects with 'id', 'key', 'name'
     * @param array $additionalClaims Additional claims
     * @return string The encoded JWT
     */
    public static function createAccessTokenWithRoles(array $roles, array $additionalClaims = []): string
    {
        return self::createAccessToken(array_merge(
            ['roles' => $roles],
            $additionalClaims
        ));
    }

    /**
     * Creates an access token with permissions.
     *
     * @param array $permissions Array of permission strings
     * @param string|null $orgCode Organization code
     * @param array $additionalClaims Additional claims
     * @return string The encoded JWT
     */
    public static function createAccessTokenWithPermissions(
        array $permissions,
        ?string $orgCode = null,
        array $additionalClaims = []
    ): string {
        $claims = ['permissions' => $permissions];
        
        if ($orgCode !== null) {
            $claims['org_code'] = $orgCode;
        }

        return self::createAccessToken(array_merge($claims, $additionalClaims));
    }

    /**
     * Creates an access token with feature flags.
     *
     * @param array $featureFlags Associative array of flag_name => ['v' => value, 't' => type]
     * @param array $additionalClaims Additional claims
     * @return string The encoded JWT
     */
    public static function createAccessTokenWithFeatureFlags(
        array $featureFlags,
        array $additionalClaims = []
    ): string {
        return self::createAccessToken(array_merge(
            ['feature_flags' => $featureFlags],
            $additionalClaims
        ));
    }

    /**
     * Creates an access token with KSP (Kinde Session Persistence) claim.
     *
     * @param bool $persistent Whether session should be persistent
     * @param array $additionalClaims Additional claims
     * @return string The encoded JWT
     */
    public static function createAccessTokenWithKsp(
        bool $persistent = true,
        array $additionalClaims = []
    ): string {
        return self::createAccessToken(array_merge(
            ['ksp' => ['persistent' => $persistent]],
            $additionalClaims
        ));
    }

    /**
     * Creates a full mock token for testing.
     * Combines roles, permissions, and feature flags in one token.
     *
     * @param array $options Options array with roles, permissions, feature_flags, org_code
     * @return string The encoded JWT
     */
    public static function createMockAccessToken(array $options = []): string
    {
        $claims = [];

        if (isset($options['roles'])) {
            $claims['roles'] = $options['roles'];
        }

        if (isset($options['permissions'])) {
            $claims['permissions'] = $options['permissions'];
        }

        if (isset($options['feature_flags'])) {
            $claims['feature_flags'] = $options['feature_flags'];
        }

        if (isset($options['org_code'])) {
            $claims['org_code'] = $options['org_code'];
        }

        // Allow passing additional arbitrary claims
        foreach ($options as $key => $value) {
            if (!in_array($key, ['roles', 'permissions', 'feature_flags', 'org_code'])) {
                $claims[$key] = $value;
            }
        }

        return self::createAccessToken($claims);
    }

    /**
     * Creates an expired access token for testing expiration scenarios.
     *
     * @param array $claims Additional claims
     * @return string The encoded JWT
     */
    public static function createExpiredAccessToken(array $claims = []): string
    {
        return self::createAccessToken(array_merge(
            [
                'iat' => time() - 7200,
                'exp' => time() - 3600, // Expired 1 hour ago
            ],
            $claims
        ));
    }

    /**
     * Get the secret key used for signing (for JWKS mock).
     *
     * @return string The secret key
     */
    public static function getSecretKey(): string
    {
        return self::SECRET_KEY;
    }

    /**
     * Get the algorithm used for signing.
     *
     * @return string The algorithm
     */
    public static function getAlgorithm(): string
    {
        return self::ALGORITHM;
    }

    /**
     * Decode a token payload without verification (for test assertions).
     *
     * @param string $token The JWT to decode
     * @return array The payload
     */
    public static function decodePayload(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid JWT format');
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/'));
        return json_decode($payload, true);
    }
}

