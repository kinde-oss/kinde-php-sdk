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
     * Helper method to create a base JWT payload with standard claims
     * 
     * @param string $userId User identifier for the sub claim
     * @return array Base JWT payload structure
     */
    private function createBaseJwtPayload(string $userId = 'user_default'): array
    {
        return [
            'sub' => $userId,
            'aud' => ['https://example.kinde.com'],
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour from now
        ];
    }

    /**
     * Helper method to create a JWT payload with KSP configuration
     * 
     * @param mixed $kspConfig The KSP claim configuration (can be array, null, etc.)
     * @param string $userId Optional user identifier
     * @return array Complete JWT payload with KSP claim
     */
    private function createJwtPayloadWithKsp($kspConfig, string $userId = 'user_default'): array
    {
        $payload = $this->createBaseJwtPayload($userId);
        
        if ($kspConfig !== null) {
            $payload['ksp'] = $kspConfig;
        }
        
        return $payload;
    }

    /**
     * Helper method to create a mock JWT token with specified payload
     * 
     * @param array $payload The payload to encode in the JWT
     * @return string A mock JWT token in the format header.payload.signature
     */
    private function createMockJWT(array $payload): string
    {
        // Create a simple JWT structure: header.payload.signature
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payloadJson = json_encode($payload);
        
        // Base64url encode (matching the format Utils::parseJWT expects)
        $headerEncoded = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');
        
        // Mock signature (not validated in tests)
        $signature = 'mock_signature';
        
        return "{$headerEncoded}.{$payloadEncoded}.{$signature}";
    }

    /**
     * Helper method to create a complete token structure with access, id, and refresh tokens
     * 
     * @param string $accessToken The access token JWT
     * @return array Token structure with all required tokens
     */
    private function createTokenStructure(string $accessToken): array
    {
        return [
            'access_token' => $accessToken,
            'id_token' => 'mock_id_token',
            'refresh_token' => 'mock_refresh_token',
        ];
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

    /**
     * Test valid JWT with ksp.persistent = true
     * Should result in a persistent session (cookie expiration > 0)
     */
    public function testValidJwtWithKspPersistentTrue()
    {
        // Create a JWT payload with ksp.persistent = true
        $payload = $this->createJwtPayloadWithKsp(['persistent' => true], 'user_123');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        // Verify the session is persistent
        $this->assertTrue(
            Storage::isSessionPersistent(), 
            'Session should be persistent when ksp.persistent = true'
        );
        
        // Verify cookie expiration is set to persistent duration
        $expiration = Storage::getCookieExpiration();
        $expectedExpiration = time() + Storage::getPersistentCookieDuration();
        
        $this->assertEqualsWithDelta(
            $expectedExpiration, 
            $expiration, 
            3,
            'Cookie expiration should be set to persistent duration (29 days) when ksp.persistent = true'
        );
        
        $this->assertGreaterThan(
            0, 
            $expiration,
            'Cookie expiration should be greater than 0 for persistent sessions'
        );
    }

    /**
     * Test valid JWT with ksp.persistent = false
     * Should result in a session cookie (cookie expiration = 0)
     */
    public function testValidJwtWithKspPersistentFalse()
    {
        // Create a JWT payload with ksp.persistent = false
        $payload = $this->createJwtPayloadWithKsp(['persistent' => false], 'user_456');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        // Verify the session is NOT persistent
        $this->assertFalse(
            Storage::isSessionPersistent(), 
            'Session should NOT be persistent when ksp.persistent = false'
        );
        
        // Verify cookie expiration is 0 (session cookie)
        $expiration = Storage::getCookieExpiration();
        
        $this->assertEquals(
            0, 
            $expiration,
            'Cookie expiration should be 0 (session cookie) when ksp.persistent = false'
        );
    }

    /**
     * Test valid JWT with ksp object but no persistent property
     * Should default to persistent session (following TypeScript SDK logic: ksp?.persistent ?? true)
     */
    public function testValidJwtWithKspObjectButNoPersistentProperty()
    {
        // Create a JWT payload with ksp object but no persistent property
        $payload = $this->createJwtPayloadWithKsp(
            ['some_other_property' => 'value'], 
            'user_789'
        );
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        // Verify the session defaults to persistent
        $this->assertTrue(
            Storage::isSessionPersistent(), 
            'Session should default to persistent when ksp object exists but persistent property is missing'
        );
        
        // Verify cookie expiration is set to persistent duration
        $expiration = Storage::getCookieExpiration();
        $expectedExpiration = time() + Storage::getPersistentCookieDuration();
        
        $this->assertEqualsWithDelta(
            $expectedExpiration, 
            $expiration, 
            3,
            'Cookie expiration should default to persistent duration when ksp.persistent is not specified'
        );
        
        $this->assertGreaterThan(
            0, 
            $expiration,
            'Cookie expiration should be greater than 0 when defaulting to persistent session'
        );
    }

    /**
     * Priority 1: Test JWT with ksp.persistent as string "true"
     * Tests type coercion - string "true" should be treated as truthy and result in persistent session
     */
    public function testValidJwtWithKspPersistentAsStringTrue()
    {
        $payload = $this->createJwtPayloadWithKsp(['persistent' => 'true'], 'user_string_true');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        // PHP's (bool) "true" evaluates to true
        $this->assertTrue(
            Storage::isSessionPersistent(),
            'Session should be persistent when ksp.persistent = "true" (string) due to type coercion'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertGreaterThan(
            0, 
            $expiration,
            'Cookie expiration should be > 0 for string "true" coerced to boolean true'
        );
    }

    /**
     * Priority 1: Test JWT with ksp.persistent as string "false"
     * Critical: Tests type coercion behavior - string "false" is truthy in PHP!
     * This test documents a potential security concern where "false" string would be treated as true
     */
    public function testValidJwtWithKspPersistentAsStringFalse()
    {
        $payload = $this->createJwtPayloadWithKsp(['persistent' => 'false'], 'user_string_false');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        // CRITICAL: (bool) "false" in PHP evaluates to TRUE (non-empty string)
        // This test verifies the current implementation behavior
        $this->assertTrue(
            Storage::isSessionPersistent(),
            'Session will be persistent when ksp.persistent = "false" (string) - this is a type coercion gotcha'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertGreaterThan(
            0, 
            $expiration,
            'Cookie expiration will be > 0 because string "false" is truthy in PHP'
        );
    }

    /**
     * Priority 1: Test JWT with ksp.persistent as numeric 1
     * Tests type coercion - numeric 1 should be treated as true
     */
    public function testValidJwtWithKspPersistentAsNumericOne()
    {
        $payload = $this->createJwtPayloadWithKsp(['persistent' => 1], 'user_numeric_1');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        $this->assertTrue(
            Storage::isSessionPersistent(),
            'Session should be persistent when ksp.persistent = 1 (numeric)'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertGreaterThan(
            0, 
            $expiration,
            'Cookie expiration should be > 0 for numeric 1 coerced to boolean true'
        );
    }

    /**
     * Priority 1: Test JWT with ksp.persistent as numeric 0
     * Tests type coercion - numeric 0 should be treated as false
     */
    public function testValidJwtWithKspPersistentAsNumericZero()
    {
        $payload = $this->createJwtPayloadWithKsp(['persistent' => 0], 'user_numeric_0');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        $this->assertFalse(
            Storage::isSessionPersistent(),
            'Session should NOT be persistent when ksp.persistent = 0 (numeric)'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertEquals(
            0, 
            $expiration,
            'Cookie expiration should be 0 (session cookie) for numeric 0 coerced to boolean false'
        );
    }

    /**
     * Priority 1: Test JWT with empty ksp object
     * Should default to persistent when ksp = {}
     */
    public function testValidJwtWithEmptyKspObject()
    {
        $payload = $this->createJwtPayloadWithKsp([], 'user_empty_ksp');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        $this->assertTrue(
            Storage::isSessionPersistent(),
            'Session should default to persistent when ksp is empty object'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertGreaterThan(
            0, 
            $expiration,
            'Cookie expiration should be > 0 when ksp object is empty (defaults to persistent)'
        );
    }

    /**
     * Priority 1: Test JWT with ksp.persistent as null
     * Important: In PHP, isset() returns false when value is null
     * This means null is treated the same as "property not set" → defaults to persistent
     */
    public function testValidJwtWithKspPersistentAsNull()
    {
        $payload = $this->createJwtPayloadWithKsp(['persistent' => null], 'user_null_persistent');
        $accessToken = $this->createMockJWT($payload);
        $token = $this->createTokenStructure($accessToken);

        Storage::setToken($token);

        // In PHP, isset($array['key']) returns FALSE when $array['key'] = null
        // This means the implementation treats null as "not set" and defaults to persistent
        $this->assertTrue(
            Storage::isSessionPersistent(),
            'Session should default to persistent when ksp.persistent = null (isset returns false for null values)'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertGreaterThan(
            0, 
            $expiration,
            'Cookie expiration should be > 0 when persistent is null (defaults to persistent)'
        );
    }

    /**
     * Critical: Test JWT with token passed as stdClass object
     * Regression test for CodeRabbit issue: stdClass tokens must be normalized to arrays
     * Without normalization, is_array() check fails and KSP logic never runs
     */
    public function testValidJwtWithTokenAsStdClassObject()
    {
        // Create token as stdClass (simulating json_decode($json) without true parameter)
        $payload = $this->createJwtPayloadWithKsp(['persistent' => false], 'user_stdclass');
        $accessToken = $this->createMockJWT($payload);
        
        // Create stdClass object (mimics json_decode without associative flag)
        $tokenObject = json_decode(json_encode([
            'access_token' => $accessToken,
            'id_token' => 'mock_id_token',
            'refresh_token' => 'mock_refresh_token',
        ]));
        
        $this->assertInstanceOf(\stdClass::class, $tokenObject, 'Token should be stdClass for this test');
        
        Storage::setToken($tokenObject);
        
        // Critical: KSP logic MUST run even with stdClass input
        $this->assertFalse(
            Storage::isSessionPersistent(),
            'KSP logic must run for stdClass tokens - persistent=false should result in non-persistent session'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertEquals(
            0,
            $expiration,
            'stdClass token with ksp.persistent=false must set session cookie (expiration=0)'
        );
    }

    /**
     * Critical: Test JWT with token passed as stdClass with persistent=true
     * Ensures stdClass normalization works for both true and false cases
     */
    public function testValidJwtWithTokenAsStdClassObjectPersistentTrue()
    {
        $payload = $this->createJwtPayloadWithKsp(['persistent' => true], 'user_stdclass_persistent');
        $accessToken = $this->createMockJWT($payload);
        
        $tokenObject = json_decode(json_encode([
            'access_token' => $accessToken,
            'id_token' => 'mock_id_token',
            'refresh_token' => 'mock_refresh_token',
        ]));
        
        Storage::setToken($tokenObject);
        
        $this->assertTrue(
            Storage::isSessionPersistent(),
            'stdClass token with ksp.persistent=true should result in persistent session'
        );
        
        $expiration = Storage::getCookieExpiration();
        $this->assertGreaterThan(
            0,
            $expiration,
            'stdClass token with ksp.persistent=true must set persistent cookie'
        );
    }

    /**
     * Priority 2: End-to-end integration test
     * Tests the complete flow: setToken → JWT parsing → KSP logic → isSessionPersistent → getCookieExpiration
     */
    public function testKspEndToEndIntegrationFlow()
    {
        // Test Case 1: Persistent session flow
        $persistentPayload = $this->createJwtPayloadWithKsp(['persistent' => true], 'user_e2e_persistent');
        $persistentAccessToken = $this->createMockJWT($persistentPayload);
        $persistentToken = $this->createTokenStructure($persistentAccessToken);
        
        Storage::setToken($persistentToken);
        
        $this->assertTrue(Storage::isSessionPersistent());
        $this->assertGreaterThan(0, Storage::getCookieExpiration());
        $this->assertEquals($persistentAccessToken, Storage::getAccessToken());
        
        // Test Case 2: Non-persistent session flow
        $nonPersistentPayload = $this->createJwtPayloadWithKsp(['persistent' => false], 'user_e2e_session');
        $nonPersistentAccessToken = $this->createMockJWT($nonPersistentPayload);
        $nonPersistentToken = $this->createTokenStructure($nonPersistentAccessToken);
        
        Storage::setToken($nonPersistentToken);
        
        $this->assertFalse(Storage::isSessionPersistent());
        $this->assertEquals(0, Storage::getCookieExpiration());
        $this->assertEquals($nonPersistentAccessToken, Storage::getAccessToken());
        
        // Test Case 3: Default persistent flow (no ksp claim)
        $defaultPayload = $this->createBaseJwtPayload('user_e2e_default');
        $defaultAccessToken = $this->createMockJWT($defaultPayload);
        $defaultToken = $this->createTokenStructure($defaultAccessToken);
        
        Storage::setToken($defaultToken);
        
        $this->assertTrue(Storage::isSessionPersistent(), 'Should default to persistent when no ksp claim');
        $this->assertGreaterThan(0, Storage::getCookieExpiration());
    }

    /**
     * Priority 2: Test helper method validation
     * Verifies that our createMockJWT helper produces valid parseable tokens
     */
    public function testCreateMockJwtHelperProducesValidTokens()
    {
        $payload = $this->createJwtPayloadWithKsp(['persistent' => true], 'user_helper_test');
        $jwt = $this->createMockJWT($payload);
        
        // Verify JWT structure (3 parts separated by dots)
        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts, 'JWT should have exactly 3 parts (header.payload.signature)');
        
        // Verify each part is non-empty
        $this->assertNotEmpty($parts[0], 'Header should not be empty');
        $this->assertNotEmpty($parts[1], 'Payload should not be empty');
        $this->assertNotEmpty($parts[2], 'Signature should not be empty');
        
        // Verify the payload can be decoded back (using test Utils class)
        $decodedPayload = \Kinde\KindeSDK\Test\Sdk\Utils\Utils::parseJWT($jwt);
        $this->assertIsArray($decodedPayload, 'Decoded payload should be an array');
        $this->assertEquals('user_helper_test', $decodedPayload['sub'], 'Decoded payload should match original');
        $this->assertTrue($decodedPayload['ksp']['persistent'], 'KSP persistent should be preserved');
    }
}