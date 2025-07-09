<?php

namespace Kinde\KindeSDK\Test\UnitTests;

use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Test\Sdk\Storage\Storage;
use Kinde\KindeSDK\Test\Sdk\Enums\StorageEnums;
use PHPUnit\Framework\TestCase;

class JwksCachingTest extends TestCase
{
    private KindeClientSDK $kindeClient;

    protected function setUp(): void
    {
        $this->kindeClient = new KindeClientSDK(
            'https://test.kinde.com',
            'http://localhost/callback',
            'test_client_id',
            'test_client_secret',
            GrantType::authorizationCode,
            'http://localhost/logout'
        );
        
        // Set up the test storage with JWKS URL
        $testStorage = Storage::getInstance();
        $testStorage->setJwksUrl('https://test.kinde.com/.well-known/jwks.json');
    }

    protected function tearDown(): void
    {
        // Clear any cached data after each test
        Storage::getInstance()->clear();
    }

    public function testJwksUrlIsSet()
    {
        $this->assertEquals(
            'https://test.kinde.com/.well-known/jwks.json',
            Storage::getInstance()->getJwksUrl()
        );
    }

    public function testJwksCacheStorage()
    {
        $storage = Storage::getInstance();
        
        // Test that cache is initially empty
        $this->assertNull($storage->getCachedJwks());
        
        // Test setting cache
        $testJwks = ['keys' => [['kid' => 'test-key']]];
        $storage->setCachedJwks($testJwks, 3600);
        
        // Test that cache is now available
        $cachedJwks = $storage->getCachedJwks();
        $this->assertNotNull($cachedJwks);
        $this->assertEquals($testJwks, $cachedJwks);
    }

    public function testJwksCacheExpiration()
    {
        $storage = Storage::getInstance();
        
        // Set cache with short TTL
        $testJwks = ['keys' => [['kid' => 'test-key']]];
        $storage->setCachedJwks($testJwks, 1); // 1 second TTL
        
        // Cache should be available immediately
        $this->assertNotNull($storage->getCachedJwks());
        
        // Wait for cache to expire
        sleep(2);
        
        // Cache should now be expired
        $this->assertNull($storage->getCachedJwks());
    }

    public function testClearJwksCache()
    {
        $storage = Storage::getInstance();
        
        // Set cache
        $testJwks = ['keys' => [['kid' => 'test-key']]];
        $storage->setCachedJwks($testJwks, 3600);
        
        // Verify cache exists
        $this->assertNotNull($storage->getCachedJwks());
        
        // Clear cache
        $storage->clearCachedJwks();
        
        // Verify cache is cleared
        $this->assertNull($storage->getCachedJwks());
    }

    public function testJwksCacheDataStructure()
    {
        $storage = Storage::getInstance();
        
        // Test setting cache with proper data structure
        $testJwks = [
            'keys' => [
                [
                    'kid' => 'test-key-1',
                    'kty' => 'RSA',
                    'n' => 'test-n',
                    'e' => 'AQAB'
                ],
                [
                    'kid' => 'test-key-2',
                    'kty' => 'RSA',
                    'n' => 'test-n-2',
                    'e' => 'AQAB'
                ]
            ]
        ];
        
        $storage->setCachedJwks($testJwks, 3600);
        
        // Verify cache structure is preserved
        $cachedJwks = $storage->getCachedJwks();
        $this->assertNotNull($cachedJwks);
        $this->assertEquals($testJwks, $cachedJwks);
        $this->assertCount(2, $cachedJwks['keys']);
        $this->assertEquals('test-key-1', $cachedJwks['keys'][0]['kid']);
        $this->assertEquals('test-key-2', $cachedJwks['keys'][1]['kid']);
    }
} 