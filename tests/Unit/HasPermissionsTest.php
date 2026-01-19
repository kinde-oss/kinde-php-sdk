<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Exception;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Kinde\KindeSDK\Tests\Support\TestableKindeClientSDK;

/**
 * Comprehensive tests for hasPermissions method.
 * Mirrors js-utils hasPermissions.test.ts test coverage.
 */
class HasPermissionsTest extends KindeTestCase
{
    private TestableKindeClientSDK $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = new TestableKindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );
    }

    // =========================================================================
    // Basic Permission Checks
    // =========================================================================

    public function testReturnsTrueWhenNoPermissionsProvided(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $this->assertTrue($this->client->hasPermissions([]));
    }

    public function testReturnsTrueWhenUserHasAllRequiredPermissions(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canDelete', 'canView'],
        ]);

        $this->assertTrue($this->client->hasPermissions(['canEdit', 'canView']));
    }

    public function testReturnsFalseWhenUserHasSomeButNotAllRequiredPermissions(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $this->assertFalse($this->client->hasPermissions(['canEdit', 'canDelete']));
    }

    public function testReturnsFalseWhenUserHasNoRequiredPermissions(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canView'],
        ]);

        $this->assertFalse($this->client->hasPermissions(['canEdit', 'canDelete']));
    }

    public function testReturnsTrueWhenUserHasSingleRequiredPermission(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $this->assertTrue($this->client->hasPermissions(['canEdit']));
    }

    public function testReturnsFalseWhenPermissionsArrayIsEmpty(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => [],
        ]);

        $this->assertFalse($this->client->hasPermissions(['canEdit']));
    }

    public function testReturnsFalseWhenPermissionsIsNull(): void
    {
        $this->client->setMockAccessTokenClaims([
            'org_code' => 'org_123',
            'permissions' => null,
        ]);

        $this->assertFalse($this->client->hasPermissions(['canEdit']));
    }

    // =========================================================================
    // Custom Conditions
    // =========================================================================

    public function testCustomConditionReturnsTrueWhenConditionPasses(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn(array $context) => $context['permissionKey'] === 'canEdit',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCustomConditionReturnsFalseWhenConditionFails(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn() => false, // Always false
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testCustomConditionCanAccessOrgCode(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn(array $context) => $context['orgCode'] === 'org_123',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCustomConditionFailsWithWrongOrgCode(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_456',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn(array $context) => $context['orgCode'] === 'org_123',
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testCombiningStringPermissionsAndCustomConditions(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canDelete'],
        ]);

        $result = $this->client->hasPermissions([
            'canEdit', // string permission - check existence
            [
                'permission' => 'canDelete',
                'condition' => fn(array $context) => $context['permissionKey'] === 'canDelete',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testOneConditionFailsInMixedTypes(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions([
            'canEdit', // string permission - passes
            [
                'permission' => 'canDelete',
                'condition' => fn() => true, // Won't be called because permission doesn't exist
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testMultipleCustomConditionsAllPassing(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canDelete', 'canView'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn(array $context) => str_contains($context['permissionKey'], 'Edit'),
            ],
            [
                'permission' => 'canView',
                'condition' => fn(array $context) => str_contains($context['permissionKey'], 'View'),
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testMultipleCustomConditionsOneFailing(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canView'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn() => true, // passes
            ],
            [
                'permission' => 'canView',
                'condition' => fn() => false, // fails
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testCustomConditionForNonGrantedPermission(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canView'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn(array $context) => $context['permissionKey'] === 'canEdit',
            ],
        ]);

        // Custom condition is not called when permission is not granted
        $this->assertFalse($result);
    }

    // =========================================================================
    // Error Handling
    // =========================================================================

    public function testReturnsFalseOnException(): void
    {
        $this->client->setPermissionsException(new Exception('API Error'));

        $this->assertFalse($this->client->hasPermissions(['canEdit']));
    }

    public function testGracefullyHandlesApiFailure(): void
    {
        $this->client->setPermissionsException(new Exception('Network timeout'));

        // Should not throw, should return false
        $result = $this->client->hasPermissions(['canEdit', 'canDelete']);
        
        $this->assertFalse($result);
    }

    // =========================================================================
    // ForceApi Option
    // =========================================================================

    public function testForceApiFalseUsesTokenData(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions(['canEdit'], false);

        $this->assertTrue($result);
    }

    public function testForceApiNullUsesTokenData(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions(['canEdit']);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testPermissionKeysAreCaseSensitive(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['CanEdit'],
        ]);

        $this->assertFalse($this->client->hasPermissions(['canEdit']));
        $this->assertTrue($this->client->hasPermissions(['CanEdit']));
    }

    public function testLargeNumberOfPermissions(): void
    {
        $permissions = [];
        for ($i = 1; $i <= 100; $i++) {
            $permissions[] = "permission_$i";
        }
        
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => $permissions,
        ]);

        $this->assertTrue($this->client->hasPermissions(['permission_1', 'permission_50', 'permission_100']));
        $this->assertFalse($this->client->hasPermissions(['permission_1', 'permission_101']));
    }

    public function testSpecialCharactersInPermissionKeys(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['permission:admin', 'permission.editor', 'permission-viewer'],
        ]);

        $this->assertTrue($this->client->hasPermissions(['permission:admin', 'permission.editor', 'permission-viewer']));
    }

    public function testNullOrgCodeHandling(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => null,
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->hasPermissions([
            [
                'permission' => 'canEdit',
                'condition' => fn(array $context) => $context['orgCode'] === null,
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testPermissionsFromClaimsWhenMockNotSet(): void
    {
        $this->client->setMockAccessTokenClaims([
            'org_code' => 'org_123',
            'permissions' => ['canEdit', 'canView'],
        ]);

        $this->assertTrue($this->client->hasPermissions(['canEdit']));
        $this->assertTrue($this->client->hasPermissions(['canView']));
        $this->assertFalse($this->client->hasPermissions(['canDelete']));
    }

    public function testEarlyExitOnFirstFailure(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        // Should stop checking after 'missing' fails
        $result = $this->client->hasPermissions(['missing', 'canEdit']);

        $this->assertFalse($result);
    }
}

