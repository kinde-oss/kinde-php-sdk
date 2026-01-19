<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Kinde\KindeSDK\Tests\Support\MockTokenGenerator;

/**
 * Unit tests for Storage class.
 * Tests token storage, JWKS caching, and session persistence.
 * 
 * Note: Tests that require actual cookie operations are limited in CLI environment.
 * Cookie-based tests should be run in integration tests with proper HTTP context.
 *
 * @covers \Kinde\KindeSDK\Sdk\Storage\Storage
 */
class StorageTest extends KindeTestCase
{
    private function seedJwksCache(): void
    {
        Storage::setJwksUrl('https://example.com/jwks.json');
        $secret = MockTokenGenerator::getSecretKey();
        $encodedSecret = rtrim(strtr(base64_encode($secret), '+/', '-_'), '=');
        $jwks = [
            'keys' => [
                [
                    'kty' => 'oct',
                    'k' => $encodedSecret,
                    'alg' => MockTokenGenerator::getAlgorithm(),
                    'use' => 'sig',
                ],
            ],
        ];
        Storage::setCachedJwks($jwks, 3600);
    }

    // =========================================================================
    // JWKS Caching Tests (Read-only, no cookie writes)
    // =========================================================================

    public function testGetCachedJwksReturnsNullWhenEmpty(): void
    {
        $this->clearStorage();
        
        $result = Storage::getCachedJwks();

        $this->assertNull($result);
    }

    // =========================================================================
    // Session Persistence Tests
    // =========================================================================

    public function testIsSessionPersistentDefaultsToTrueWithoutToken(): void
    {
        $this->clearStorage();
        
        $result = Storage::isSessionPersistent();
        
        $this->assertTrue($result);
    }

    public function testIsSessionPersistentFalseWhenKspClaimIsFalse(): void
    {
        $this->seedJwksCache();
        $tokenResponse = MockTokenGenerator::createTokenResponse([
            'ksp' => ['persistent' => false],
        ]);

        Storage::setToken($tokenResponse);

        $this->assertFalse(Storage::isSessionPersistent());
        $this->assertSame(0, Storage::getCookieExpiration());
    }

    public function testIsSessionPersistentTrueWhenKspClaimIsTrue(): void
    {
        $this->seedJwksCache();
        $tokenResponse = MockTokenGenerator::createTokenResponse([
            'ksp' => ['persistent' => true],
        ]);

        Storage::setToken($tokenResponse);

        $this->assertTrue(Storage::isSessionPersistent());
        $this->assertGreaterThan(time(), Storage::getCookieExpiration());
    }

    public function testGetCookieExpirationReturnsFutureTimestamp(): void
    {
        $this->clearStorage();
        
        $expiration = Storage::getCookieExpiration();

        $this->assertGreaterThan(time(), $expiration);
    }

    public function testGetPersistentCookieDuration(): void
    {
        $duration = Storage::getPersistentCookieDuration();
        
        // Default is 29 days (2505600 seconds)
        $this->assertEquals(2505600, $duration);
    }

    public function testSetPersistentCookieDuration(): void
    {
        $originalDuration = Storage::getPersistentCookieDuration();
        
        Storage::setPersistentCookieDuration(86400); // 1 day
        
        $this->assertEquals(86400, Storage::getPersistentCookieDuration());
        
        // Restore original
        Storage::setPersistentCookieDuration($originalDuration);
    }

    // =========================================================================
    // Token Storage Tests
    // =========================================================================

    public function testGetTokenReturnsNullWhenEmpty(): void
    {
        $this->clearStorage();
        
        $result = Storage::getToken();
        
        $this->assertNull($result);
    }

    public function testSetTokenStoresCookieAndRetrievesToken(): void
    {
        $this->seedJwksCache();
        $tokenResponse = MockTokenGenerator::createTokenResponse();

        Storage::setToken($tokenResponse);

        $cookieKey = 'kinde_' . StorageEnums::TOKEN;
        $this->assertNotEmpty($_COOKIE[$cookieKey] ?? null);

        $stored = Storage::getToken();
        $this->assertIsArray($stored);
        $this->assertEquals($tokenResponse['access_token'], $stored['access_token']);
    }

    public function testGetAccessTokenReturnsNullWhenEmpty(): void
    {
        $this->clearStorage();
        
        $result = Storage::getAccessToken();
        
        $this->assertNull($result);
    }

    public function testGetIdTokenReturnsNullWhenEmpty(): void
    {
        $this->clearStorage();
        
        $result = Storage::getIdToken();
        
        $this->assertNull($result);
    }

    public function testGetRefreshTokenReturnsNullWhenEmpty(): void
    {
        $this->clearStorage();
        
        $result = Storage::getRefreshToken();
        
        $this->assertNull($result);
    }

    // =========================================================================
    // State and Code Verifier Tests (Read-only)
    // =========================================================================

    public function testGetStateReturnsEmptyWhenNoCookie(): void
    {
        $this->clearStorage();
        
        $result = Storage::getState();
        
        // Returns empty string when no cookie is set
        $this->assertEmpty($result);
    }

    public function testGetCodeVerifierReturnsEmptyWhenNoCookie(): void
    {
        $this->clearStorage();
        
        $result = Storage::getCodeVerifier();
        
        // Returns empty string when no cookie is set
        $this->assertEmpty($result);
    }

    // =========================================================================
    // Token Time To Live Tests
    // =========================================================================

    public function testGetTokenTimeToLiveReturnsDefault(): void
    {
        $ttl = Storage::getTokenTimeToLive();
        
        // Default is 15 days from now
        $expected = time() + 3600 * 24 * 15;
        
        // Allow 5 second margin for test execution time
        $this->assertGreaterThan($expected - 5, $ttl);
        $this->assertLessThan($expected + 5, $ttl);
    }

    public function testSetTokenTimeToLive(): void
    {
        $customTtl = time() + 86400; // 1 day from now
        
        Storage::setTokenTimeToLive($customTtl);
        
        $this->assertEquals($customTtl, Storage::getTokenTimeToLive());
    }

    // =========================================================================
    // Singleton Pattern Tests
    // =========================================================================

    public function testGetInstanceReturnsSameInstance(): void
    {
        $instance1 = Storage::getInstance();
        $instance2 = Storage::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    public function testGetInstanceReturnsStorageClass(): void
    {
        $instance = Storage::getInstance();
        
        $this->assertInstanceOf(Storage::class, $instance);
    }

    // =========================================================================
    // Expired At Tests
    // =========================================================================

    public function testGetExpiredAtReturnsZeroWhenNoToken(): void
    {
        $this->clearStorage();
        
        $result = Storage::getExpiredAt();
        
        $this->assertEquals(0, $result);
    }
}
