<?php

use Kinde\KindeSDK\Test\Sdk\Storage\Storage;
use PHPUnit\Framework\TestCase;

class SessionPersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear any existing cookies/session data
        $_COOKIE = [];
        
        // Reset Storage singleton for clean tests
        $reflection = new \ReflectionClass(Storage::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_COOKIE = [];
    }

    /**
     * Test that getPersistentCookieDuration returns the correct value (29 days)
     */
    public function testGetPersistentCookieDuration()
    {
        $duration = Storage::getPersistentCookieDuration();
        $this->assertEquals(2505600, $duration, 'Persistent duration should be 29 days (2505600 seconds)');
        $this->assertEquals(29, $duration / (24 * 3600), 'Duration should equal 29 days when converted');
    }

    /**
     * Test that setPersistentCookieDuration works correctly
     */
    public function testSetPersistentCookieDuration()
    {
        $originalDuration = Storage::getPersistentCookieDuration();
        $newDuration = 1800; // 30 minutes
        
        Storage::setPersistentCookieDuration($newDuration);
        $this->assertEquals($newDuration, Storage::getPersistentCookieDuration());
        
        // Reset to original
        Storage::setPersistentCookieDuration($originalDuration);
        $this->assertEquals($originalDuration, Storage::getPersistentCookieDuration());
    }

    /**
     * Test isSessionPersistent with no access token (should default to persistent)
     */
    public function testIsSessionPersistentWithNoToken()
    {
        // No token set in cookies
        $result = Storage::isSessionPersistent();
        $this->assertTrue($result, 'Should default to persistent when no access token exists');
    }

    /**
     * Test isSessionPersistent with empty token (should default to persistent)
     */
    public function testIsSessionPersistentWithEmptyToken()
    {
        $_COOKIE['kinde_token'] = '';
        
        $result = Storage::isSessionPersistent();
        $this->assertTrue($result, 'Should default to persistent when token is empty');
    }

    /**
     * Test isSessionPersistent with invalid JWT token (should default to persistent)
     */
    public function testIsSessionPersistentWithInvalidJWT()
    {
        // Set up a token that will fail JWT parsing
        $_COOKIE['kinde_token'] = json_encode([
            'access_token' => 'clearly.invalid.jwt',
            'id_token' => 'test_id_token',
            'refresh_token' => 'test_refresh_token'
        ]);

        // The actual Utils::parseJWT will fail on this invalid token and return null
        $result = Storage::isSessionPersistent();
        $this->assertTrue($result, 'Should default to persistent when JWT is invalid');
    }

    /**
     * Test getCookieExpiration logic based on isSessionPersistent result
     */
    public function testGetCookieExpirationLogic()
    {
        // When session is persistent (default case with no valid token)
        $expiration = Storage::getCookieExpiration();
        $expectedExpiration = time() + Storage::getPersistentCookieDuration();
        
        // Allow for 1 second difference due to test execution time
        $this->assertEqualsWithDelta($expectedExpiration, $expiration, 1, 
            'Cookie expiration should be ~29 days from now for persistent sessions');
    }

    /**
     * Test that constants match Next.js SDK values
     */
    public function testConstantsMatchNextJsSDK()
    {
        $duration = Storage::getPersistentCookieDuration();
        
        // TWENTY_NINE_DAYS constant from Next.js SDK
        $nextjsConstant = 2505600;
        
        $this->assertEquals($nextjsConstant, $duration, 
            'PHP SDK persistent duration must match Next.js SDK TWENTY_NINE_DAYS constant');
        
        // Verify it's exactly 29 days
        $daysFromSeconds = $duration / (24 * 3600);
        $this->assertEquals(29, $daysFromSeconds, 'Duration must be exactly 29 days');
    }

    /**
     * Test setToken with string vs array input
     */
    public function testSetTokenHandlesDifferentInputTypes()
    {
        // Test with JSON string
        $tokenString = json_encode([
            'access_token' => 'test_access_token',
            'id_token' => 'test_id_token',
            'refresh_token' => 'test_refresh_token'
        ]);

        $this->expectNotToPerformAssertions();
        Storage::setToken($tokenString);

        // Test with array
        $tokenArray = [
            'access_token' => 'test_access_token_2',
            'id_token' => 'test_id_token_2',
            'refresh_token' => 'test_refresh_token_2'
        ];

        Storage::setToken($tokenArray);
    }

    /**
     * Test error handling in setToken with malformed data
     */
    public function testSetTokenErrorHandling()
    {
        // Test with invalid JSON string
        $invalidJson = '{"access_token": invalid}';
        $this->expectNotToPerformAssertions();
        Storage::setToken($invalidJson);

        // Test with null
        Storage::setToken(null);

        // Test with empty string
        Storage::setToken('');
    }

    /**
     * Test Storage singleton pattern
     */
    public function testStorageSingleton()
    {
        $instance1 = Storage::getInstance();
        $instance2 = Storage::getInstance();
        
        $this->assertSame($instance1, $instance2, 'Storage should implement singleton pattern');
        $this->assertInstanceOf(Storage::class, $instance1);
    }

    /**
     * Test token retrieval methods
     */
    public function testTokenRetrievalMethods()
    {
        // Set up a token
        $token = [
            'access_token' => 'test_access_token',
            'id_token' => 'test_id_token',  
            'refresh_token' => 'test_refresh_token'
        ];

        Storage::setToken($token);

        // Test individual token retrieval
        $this->assertEquals('test_access_token', Storage::getAccessToken());
        $this->assertEquals('test_id_token', Storage::getIdToken());
        $this->assertEquals('test_refresh_token', Storage::getRefreshToken());

        // Test full token retrieval
        $retrievedToken = Storage::getToken();
        $this->assertIsArray($retrievedToken);
        $this->assertEquals($token['access_token'], $retrievedToken['access_token']);
    }
}