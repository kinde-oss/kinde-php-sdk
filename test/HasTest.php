<?php

namespace Kinde\KindeSDK\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Model\Frontend\GetEntitlementsResponseDataEntitlementsInner;

class HasTest extends TestCase
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

    public function testHasReturnsTrueWhenNoConditionsProvided()
    {
        $result = $this->kindeClient->has([]);
        $this->assertTrue($result);
    }

    public function testHasReturnsTrueWhenAllConditionsAreMet()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles', 'hasPermissions', 'hasFeatureFlags', 'hasBillingEntitlements'])
            ->getMock();

        $this->kindeClient->method('hasRoles')->willReturn(true);
        $this->kindeClient->method('hasPermissions')->willReturn(true);
        $this->kindeClient->method('hasFeatureFlags')->willReturn(true);
        $this->kindeClient->method('hasBillingEntitlements')->willReturn(true);

        $result = $this->kindeClient->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode'],
            'billingEntitlements' => ['premium']
        ]);

        $this->assertTrue($result);
    }

    public function testHasReturnsFalseWhenAnyConditionFails()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles', 'hasPermissions', 'hasFeatureFlags', 'hasBillingEntitlements'])
            ->getMock();

        $this->kindeClient->method('hasRoles')->willReturn(true);
        $this->kindeClient->method('hasPermissions')->willReturn(false); // This fails
        // With early exit, these methods should never be called after permissions fail
        $this->kindeClient->expects($this->never())->method('hasFeatureFlags');
        $this->kindeClient->expects($this->never())->method('hasBillingEntitlements');

        $result = $this->kindeClient->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode'],
            'billingEntitlements' => ['premium']
        ]);

        $this->assertFalse($result);
    }

    public function testHasReturnsTrueWhenOnlyRolesProvided()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasRoles')
            ->with(['admin'], null)
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'roles' => ['admin']
        ]);

        $this->assertTrue($result);
    }

    public function testHasReturnsTrueWhenOnlyPermissionsProvided()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasPermissions'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasPermissions')
            ->with(['canEdit'], null)
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'permissions' => ['canEdit']
        ]);

        $this->assertTrue($result);
    }

    public function testHasReturnsTrueWhenOnlyFeatureFlagsProvided()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasFeatureFlags'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasFeatureFlags')
            ->with(['darkMode'], null)
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'featureFlags' => ['darkMode']
        ]);

        $this->assertTrue($result);
    }

    public function testHasReturnsTrueWhenOnlyBillingEntitlementsProvided()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasBillingEntitlements'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasBillingEntitlements')
            ->with(['premium'])
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'billingEntitlements' => ['premium']
        ]);

        $this->assertTrue($result);
    }

    public function testHasUsesForceApiBooleanParameter()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles', 'hasPermissions', 'hasFeatureFlags'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasRoles')
            ->with(['admin'], true)
            ->willReturn(true);

        $this->kindeClient->expects($this->once())
            ->method('hasPermissions')
            ->with(['canEdit'], true)
            ->willReturn(true);

        $this->kindeClient->expects($this->once())
            ->method('hasFeatureFlags')
            ->with(['darkMode'], true)
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode']
        ], true);

        $this->assertTrue($result);
    }

    public function testHasUsesForceApiArrayParameter()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles', 'hasPermissions', 'hasFeatureFlags'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasRoles')
            ->with(['admin'], true)
            ->willReturn(true);

        $this->kindeClient->expects($this->once())
            ->method('hasPermissions')
            ->with(['canEdit'], false)
            ->willReturn(true);

        $this->kindeClient->expects($this->once())
            ->method('hasFeatureFlags')
            ->with(['darkMode'], null)
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode']
        ], [
            'roles' => true,
            'permissions' => false
        ]);

        $this->assertTrue($result);
    }

    public function testHasUsesDefaultForceApiSettings()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles', 'hasPermissions', 'hasFeatureFlags'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasRoles')
            ->with(['admin'], null)
            ->willReturn(true);

        $this->kindeClient->expects($this->once())
            ->method('hasPermissions')
            ->with(['canEdit'], null)
            ->willReturn(true);

        $this->kindeClient->expects($this->once())
            ->method('hasFeatureFlags')
            ->with(['darkMode'], null)
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode']
        ]);

        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsAlwaysUsesApiInForceApiSettings()
    {
        // This test verifies that billing entitlements always use API
        // regardless of forceApi settings
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasBillingEntitlements'])
            ->getMock();

        $this->kindeClient->expects($this->once())
            ->method('hasBillingEntitlements')
            ->with(['premium'])
            ->willReturn(true);

        $result = $this->kindeClient->has([
            'billingEntitlements' => ['premium']
        ], false); // Even with false, billing entitlements use API

        $this->assertTrue($result);
    }

    public function testHasWithComplexConditions()
    {
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles', 'hasPermissions', 'hasFeatureFlags', 'hasBillingEntitlements'])
            ->getMock();

        $this->kindeClient->method('hasRoles')->willReturn(true);
        $this->kindeClient->method('hasPermissions')->willReturn(true);
        $this->kindeClient->method('hasFeatureFlags')->willReturn(true);
        $this->kindeClient->method('hasBillingEntitlements')->willReturn(true);

        $result = $this->kindeClient->has([
            'roles' => [
                'admin',
                ['role' => 'manager', 'condition' => function($role) { return true; }]
            ],
            'permissions' => [
                'canEdit',
                ['permission' => 'canDelete', 'condition' => function($context) { return true; }]
            ],
            'featureFlags' => [
                'darkMode',
                ['flag' => 'theme', 'value' => 'dark']
            ],
            'billingEntitlements' => [
                'premium',
                ['entitlement' => 'api-access', 'condition' => function($entitlement) { return true; }]
            ]
        ]);

        $this->assertTrue($result);
    }

    public function testParseForceApiParameterWithBooleanTrue()
    {
        $reflection = new \ReflectionClass($this->kindeClient);
        $method = $reflection->getMethod('parseForceApiParameter');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->kindeClient, [true]);

        $expected = [
            'roles' => true,
            'permissions' => true,
            'featureFlags' => true,
            'billingEntitlements' => true
        ];

        $this->assertEquals($expected, $result);
    }

    public function testParseForceApiParameterWithBooleanFalse()
    {
        $reflection = new \ReflectionClass($this->kindeClient);
        $method = $reflection->getMethod('parseForceApiParameter');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->kindeClient, [false]);

        $expected = [
            'roles' => false,
            'permissions' => false,
            'featureFlags' => false,
            'billingEntitlements' => true // Always true for billing entitlements
        ];

        $this->assertEquals($expected, $result);
    }

    public function testParseForceApiParameterWithArray()
    {
        $reflection = new \ReflectionClass($this->kindeClient);
        $method = $reflection->getMethod('parseForceApiParameter');
        $method->setAccessible(true);

        $input = [
            'roles' => true,
            'permissions' => false
        ];

        $result = $method->invokeArgs($this->kindeClient, [$input]);

        $expected = [
            'roles' => true,
            'permissions' => false,
            'featureFlags' => null,
            'billingEntitlements' => true // Always true for billing entitlements
        ];

        $this->assertEquals($expected, $result);
    }

    public function testParseForceApiParameterWithNull()
    {
        $reflection = new \ReflectionClass($this->kindeClient);
        $method = $reflection->getMethod('parseForceApiParameter');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->kindeClient, [null]);

        $expected = [
            'roles' => null,
            'permissions' => null,
            'featureFlags' => null,
            'billingEntitlements' => true // Always true for billing entitlements
        ];

        $this->assertEquals($expected, $result);
    }

    public function testHasHandlesEmptyChecksArray()
    {
        // This shouldn't happen in normal usage but tests the edge case
        $this->kindeClient = $this->getMockBuilder(KindeClientSDK::class)
            ->setConstructorArgs([
                'https://test.kinde.com',
                'http://localhost:3000/callback',
                'test-client-id',
                'test-client-secret',
                'authorization_code'
            ])
            ->onlyMethods(['hasRoles', 'hasPermissions', 'hasFeatureFlags', 'hasBillingEntitlements'])
            ->getMock();

        // None of the individual methods should be called
        $this->kindeClient->expects($this->never())->method('hasRoles');
        $this->kindeClient->expects($this->never())->method('hasPermissions');
        $this->kindeClient->expects($this->never())->method('hasFeatureFlags');
        $this->kindeClient->expects($this->never())->method('hasBillingEntitlements');

        $result = $this->kindeClient->has([]);
        $this->assertTrue($result);
    }
}
