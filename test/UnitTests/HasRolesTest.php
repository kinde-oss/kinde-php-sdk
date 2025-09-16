<?php

namespace Kinde\KindeSDK\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Sdk\Enums\StorageEnums;

class HasRolesTest extends TestCase
{
    private $kindeClient;
    private $mockStorage;

    protected function setUp(): void
    {
        $this->mockStorage = $this->createMock(Storage::class);
        
        $this->kindeClient = new KindeClientSDK(
            domain: 'https://test.kinde.com',
            redirectUri: 'http://localhost:3000/callback',
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            grantType: 'authorization_code'
        );
        
        // Use reflection to inject mock storage
        $reflection = new \ReflectionClass($this->kindeClient);
        $storageProperty = $reflection->getProperty('storage');
        $storageProperty->setAccessible(true);
        $storageProperty->setValue($this->kindeClient, $this->mockStorage);
    }

    public function testHasRolesReturnsTrueWhenNoRolesProvided()
    {
        $result = $this->kindeClient->hasRoles();
        $this->assertTrue($result);
    }

    public function testHasRolesReturnsTrueWhenEmptyArrayProvided()
    {
        $result = $this->kindeClient->hasRoles([]);
        $this->assertTrue($result);
    }

    public function testHasRolesReturnsTrueWhenUserHasAllRequiredRoles()
    {
        $this->mockStorage->method('getAccessToken')
            ->willReturn('mock.jwt.token');

        // Mock getRoles to return roles
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getRoles'])
            ->getMock();

        $this->kindeClient->method('getRoles')->willReturn([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
            ['id' => '2', 'key' => 'user', 'name' => 'User']
        ]);

        $result = $this->kindeClient->hasRoles(['admin']);
        $this->assertTrue($result);
    }

    public function testHasRolesReturnsFalseWhenUserMissingRoles()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getRoles'])
            ->getMock();

        $this->kindeClient->method('getRoles')->willReturn([
            ['id' => '2', 'key' => 'user', 'name' => 'User']
        ]);

        $result = $this->kindeClient->hasRoles(['admin']);
        $this->assertFalse($result);
    }

    public function testHasRolesReturnsTrueWhenUserHasAllMultipleRoles()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getRoles'])
            ->getMock();

        $this->kindeClient->method('getRoles')->willReturn([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
            ['id' => '2', 'key' => 'user', 'name' => 'User'],
            ['id' => '3', 'key' => 'manager', 'name' => 'Manager']
        ]);

        $result = $this->kindeClient->hasRoles(['admin', 'user']);
        $this->assertTrue($result);
    }

    public function testHasRolesReturnsFalseWhenUserHasSomeButNotAllRoles()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getRoles'])
            ->getMock();

        $this->kindeClient->method('getRoles')->willReturn([
                    ['id' => '1', 'key' => 'admin', 'name' => 'Administrator']
        ]);

        $result = $this->kindeClient->hasRoles(['admin', 'user']);
        $this->assertFalse($result);
    }

    public function testHasRolesWithCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getRoles'])
            ->getMock();

        $this->kindeClient->method('getRoles')->willReturn([
                    ['id' => '1', 'key' => 'admin', 'name' => 'Administrator']
        ]);

        $result = $this->kindeClient->hasRoles([
            [
                'role' => 'admin',
                'condition' => function($role) {
                    return $role['id'] === '1';
                }
            ]
        ]);
        $this->assertTrue($result);
    }

    public function testHasRolesWithFailingCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getRoles'])
            ->getMock();

        $this->kindeClient->method('getRoles')->willReturn([
                    ['id' => '1', 'key' => 'admin', 'name' => 'Administrator']
        ]);

        $result = $this->kindeClient->hasRoles([
            [
                'role' => 'admin',
                'condition' => function($role) {
                    return $role['id'] === '999';
                }
            ]
        ]);
        $this->assertFalse($result);
    }

    public function testHasRolesWithMissingRoleForCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getRoles'])
            ->getMock();

        $this->kindeClient->method('getRoles')->willReturn([
                    ['id' => '1', 'key' => 'admin', 'name' => 'Administrator']
        ]);

        $result = $this->kindeClient->hasRoles([
            [
                'role' => 'manager',
                'condition' => function($role) {
                    return true;
                }
            ]
        ]);
        $this->assertFalse($result);
    }

    public function testHasRolesWithStringRolesInToken()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaims'])
            ->getMock();

        $this->kindeClient->method('getClaims')->willReturn(['roles' => ['admin', 'user']]);

        $result = $this->kindeClient->hasRoles(['admin']);
        $this->assertTrue($result);
    }

    public function testGetRolesFormatsStringRolesToObjects()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getClaims'])
            ->getMock();

        $this->kindeClient->method('getClaims')->willReturn(['roles' => ['admin', 'user']]);

        $roles = $this->kindeClient->getRoles();
        
        $this->assertCount(2, $roles);
        $this->assertEquals([
            ['key' => 'admin', 'id' => null, 'name' => 'admin'],
            ['key' => 'user', 'id' => null, 'name' => 'user']
        ], $roles);
    }
}
