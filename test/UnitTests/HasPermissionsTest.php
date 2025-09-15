<?php

namespace Kinde\KindeSDK\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;

class HasPermissionsTest extends TestCase
{
    private $kindeClient;

    protected function setUp(): void
    {
        $this->kindeClient = new KindeClientSDK(
            domain: 'https://test.kinde.com',
            redirectUri: 'http://localhost:3000/callback',
            clientId: 'test-client-id',
            clientSecret: 'test-client-secret',
            grantType: 'authorization_code'
        );
    }

    public function testHasPermissionsReturnsTrueWhenNoPermissionsProvided()
    {
        $result = $this->kindeClient->hasPermissions();
        $this->assertTrue($result);
    }

    public function testHasPermissionsReturnsTrueWhenEmptyArrayProvided()
    {
        $result = $this->kindeClient->hasPermissions([]);
        $this->assertTrue($result);
    }

    public function testHasPermissionsReturnsTrueWhenUserHasAllRequiredPermissions()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canEdit', 'canDelete', 'canView']
            ]);

        $result = $this->kindeClient->hasPermissions(['canEdit']);
        $this->assertTrue($result);
    }

    public function testHasPermissionsReturnsFalseWhenUserMissingPermissions()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canView']
            ]);

        $result = $this->kindeClient->hasPermissions(['canEdit']);
        $this->assertFalse($result);
    }

    public function testHasPermissionsReturnsTrueWhenUserHasAllMultiplePermissions()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canEdit', 'canDelete', 'canView']
            ]);

        $result = $this->kindeClient->hasPermissions(['canEdit', 'canDelete']);
        $this->assertTrue($result);
    }

    public function testHasPermissionsReturnsFalseWhenUserHasSomeButNotAllPermissions()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canEdit']
            ]);

        $result = $this->kindeClient->hasPermissions(['canEdit', 'canDelete']);
        $this->assertFalse($result);
    }

    public function testHasPermissionsWithCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canEdit']
            ]);

        $result = $this->kindeClient->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => function($context) {
                    return $context['orgCode'] === 'org_123';
                }
            ]
        ]);
        $this->assertTrue($result);
    }

    public function testHasPermissionsWithFailingCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canEdit']
            ]);

        $result = $this->kindeClient->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => function($context) {
                    return $context['orgCode'] === 'org_different';
                }
            ]
        ]);
        $this->assertFalse($result);
    }

    public function testHasPermissionsWithMissingPermissionForCustomCondition()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canView']
            ]);

        $result = $this->kindeClient->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => function($context) {
                    return true;
                }
            ]
        ]);
        $this->assertFalse($result);
    }

    public function testHasPermissionsUsesForceApiParameter()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissionsFromApi'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('getPermissionsFromApi')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => ['canEdit']
            ]);

        $result = $this->kindeClient->hasPermissions(['canEdit'], true);
        $this->assertTrue($result);
    }

    public function testHasPermissionsWithEmptyPermissionsArray()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => 'org_123',
                'permissions' => []
            ]);

        $result = $this->kindeClient->hasPermissions(['canEdit']);
        $this->assertFalse($result);
    }

    public function testHasPermissionsWithNullOrgCode()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['getPermissions'])
            ->getMock();

        $this->kindeClient->method('getPermissions')
            ->willReturn([
                'orgCode' => null,
                'permissions' => ['canEdit']
            ]);

        $result = $this->kindeClient->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => function($context) {
                    return $context['orgCode'] === null;
                }
            ]
        ]);
        $this->assertTrue($result);
    }
}
