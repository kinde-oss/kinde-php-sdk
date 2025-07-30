<?php

namespace Kinde\KindeSDK\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Exception;

class EntitlementsTest extends TestCase
{
    private KindeClientSDK $kindeClient;

    protected function setUp(): void
    {
        $this->kindeClient = new KindeClientSDK(
            domain: 'https://test.kinde.com',
            clientId: 'test_client_id',
            clientSecret: 'test_client_secret',
            redirectUri: 'http://localhost:8000/callback'
        );
    }

    public function testGetAllEntitlementsThrowsExceptionWhenNotAuthenticated()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User must be authenticated to get entitlements');
        
        $this->kindeClient->getAllEntitlements();
    }

    public function testGetEntitlementThrowsExceptionWhenNotAuthenticated()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User must be authenticated to get entitlements');
        
        $this->kindeClient->getEntitlement('test_key');
    }

    public function testHasEntitlementThrowsExceptionWhenNotAuthenticated()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User must be authenticated to get entitlements');
        
        $this->kindeClient->hasEntitlement('test_key');
    }

    public function testGetEntitlementLimitThrowsExceptionWhenNotAuthenticated()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User must be authenticated to get entitlements');
        
        $this->kindeClient->getEntitlementLimit('test_key');
    }

    public function testEntitlementsMethodsExist()
    {
        // Test that all entitlements methods exist and are callable
        $this->assertTrue(method_exists($this->kindeClient, 'getAllEntitlements'));
        $this->assertTrue(method_exists($this->kindeClient, 'getEntitlement'));
        $this->assertTrue(method_exists($this->kindeClient, 'hasEntitlement'));
        $this->assertTrue(method_exists($this->kindeClient, 'getEntitlementLimit'));
    }

    public function testEntitlementsMethodSignatures()
    {
        // Test method signatures using reflection
        $reflection = new \ReflectionClass($this->kindeClient);
        
        // getAllEntitlements should take no parameters and return array
        $getAllMethod = $reflection->getMethod('getAllEntitlements');
        $this->assertEquals(0, $getAllMethod->getNumberOfParameters());
        $this->assertEquals('array', $getAllMethod->getReturnType()->getName());
        
        // getEntitlement should take string parameter and return nullable object
        $getMethod = $reflection->getMethod('getEntitlement');
        $this->assertEquals(1, $getMethod->getNumberOfParameters());
        $this->assertEquals('string', $getMethod->getParameters()[0]->getType()->getName());
        $this->assertTrue($getMethod->getReturnType()->allowsNull());
        
        // hasEntitlement should take string parameter and return bool
        $hasMethod = $reflection->getMethod('hasEntitlement');
        $this->assertEquals(1, $hasMethod->getNumberOfParameters());
        $this->assertEquals('string', $hasMethod->getParameters()[0]->getType()->getName());
        $this->assertEquals('bool', $hasMethod->getReturnType()->getName());
        
        // getEntitlementLimit should take string parameter and return nullable int
        $limitMethod = $reflection->getMethod('getEntitlementLimit');
        $this->assertEquals(1, $limitMethod->getNumberOfParameters());
        $this->assertEquals('string', $limitMethod->getParameters()[0]->getType()->getName());
        $this->assertTrue($limitMethod->getReturnType()->allowsNull());
        $this->assertEquals('int', $limitMethod->getReturnType()->getName());
    }

    public function testPrivateMethodExists()
    {
        // Test that the private method exists
        $reflection = new \ReflectionClass($this->kindeClient);
        $this->assertTrue($reflection->hasMethod('getEntitlementsFromApi'));
        
        $method = $reflection->getMethod('getEntitlementsFromApi');
        $this->assertTrue($method->isPrivate());
        $this->assertEquals(3, $method->getNumberOfParameters());
    }
} 