<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Exception;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Kinde\KindeSDK\Tests\Support\MockEntitlement;
use Kinde\KindeSDK\Tests\Support\TestableKindeClientSDK;

/**
 * Comprehensive tests for the combined has() method.
 * Mirrors js-utils has.test.ts test coverage.
 * Tests the unified method that checks roles, permissions, feature flags, and billing entitlements.
 *
 * @covers \Kinde\KindeSDK\KindeClientSDK::has
 * @covers \Kinde\KindeSDK\KindeClientSDK::hasRoles
 * @covers \Kinde\KindeSDK\KindeClientSDK::hasPermissions
 * @covers \Kinde\KindeSDK\KindeClientSDK::hasFeatureFlags
 * @covers \Kinde\KindeSDK\KindeClientSDK::hasBillingEntitlements
 */
class HasCombinedTest extends KindeTestCase
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
    // No Token Scenarios (mirrors js-utils "when no token" tests)
    // =========================================================================

    public function testReturnsFalseWhenNoTokenAndRolesRequired(): void
    {
        // Create a fresh client without any mock data set
        $freshClient = new TestableKindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );

        // No mock data set - simulates no token scenario
        $result = $freshClient->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
        ]);

        $this->assertFalse($result);
    }

    public function testReturnsTrueWhenNoTokenButNoConditionsRequired(): void
    {
        // Create a fresh client without any mock data set
        $freshClient = new TestableKindeClientSDK(
            self::TEST_DOMAIN,
            self::TEST_REDIRECT_URI,
            self::TEST_CLIENT_ID,
            self::TEST_CLIENT_SECRET,
            GrantType::authorizationCode,
            self::TEST_LOGOUT_REDIRECT_URI
        );

        // Empty conditions should return true
        $result = $freshClient->has([]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Basic Combined Checks
    // =========================================================================

    public function testReturnsTrueWhenNoConditionsProvided(): void
    {
        $this->assertTrue($this->client->has([]));
    }

    public function testReturnsTrueWhenAllConditionsAreMet(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);
        $this->client->setMockEntitlements([
            MockEntitlement::simple('premium'),
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode'],
            'billingEntitlements' => ['premium'],
        ]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Single Condition Type Tests
    // =========================================================================

    public function testOnlyRolesProvidedAndUserHasAllRoles(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
            ['id' => '2', 'key' => 'user', 'name' => 'User'],
        ]);

        $result = $this->client->has(['roles' => ['admin']]);

        $this->assertTrue($result);
    }

    public function testOnlyRolesProvidedAndUserMissingRoles(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'user', 'name' => 'User'],
        ]);

        $result = $this->client->has(['roles' => ['admin']]);

        $this->assertFalse($result);
    }

    public function testOnlyPermissionsProvidedAndUserHasAllPermissions(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canDelete'],
        ]);

        $result = $this->client->has(['permissions' => ['canEdit']]);

        $this->assertTrue($result);
    }

    public function testOnlyPermissionsProvidedAndUserMissingPermissions(): void
    {
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canView'],
        ]);

        $result = $this->client->has(['permissions' => ['canEdit']]);

        $this->assertFalse($result);
    }

    public function testOnlyFeatureFlagsProvidedAndUserHasAllFlags(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
                'newDashboard' => ['v' => 'enabled', 't' => 's'],
            ],
        ]);

        $result = $this->client->has(['featureFlags' => ['darkMode']]);

        $this->assertTrue($result);
    }

    public function testOnlyFeatureFlagsProvidedAndUserMissingFlags(): void
    {
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'otherFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has(['featureFlags' => ['darkMode']]);

        $this->assertFalse($result);
    }

    public function testOnlyBillingEntitlementsProvidedAndUserHasAll(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
            MockEntitlement::simple('premium_features'),
        ]);

        $result = $this->client->has(['billingEntitlements' => ['pro_gym']]);

        $this->assertTrue($result);
    }

    public function testOnlyBillingEntitlementsProvidedAndUserMissing(): void
    {
        $this->client->setMockEntitlements([
            MockEntitlement::simple('basic_plan'),
        ]);

        $result = $this->client->has(['billingEntitlements' => ['pro_gym']]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // Combined Conditions - Partial Failures
    // =========================================================================

    public function testBothRolesAndPermissionsProvidedButUserMissingRoles(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'user', 'name' => 'User'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canDelete'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
        ]);

        $this->assertFalse($result);
    }

    public function testBothRolesAndPermissionsProvidedButUserMissingPermissions(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canView'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
        ]);

        $this->assertFalse($result);
    }

    public function testBothRolesAndPermissionsProvidedButUserMissingBoth(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'user', 'name' => 'User'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canView'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
        ]);

        $this->assertFalse($result);
    }

    public function testAllTypesProvidedButMissingFeatureFlags(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'otherFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);
        $this->client->setMockEntitlements([
            MockEntitlement::simple('premium'),
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode'],
            'billingEntitlements' => ['premium'],
        ]);

        $this->assertFalse($result);
    }

    public function testAllTypesProvidedButMissingBillingEntitlements(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);
        $this->client->setMockEntitlements([
            MockEntitlement::simple('basic'),
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode'],
            'billingEntitlements' => ['premium'],
        ]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // Multiple Items Per Type
    // =========================================================================

    public function testMultipleRolesAndPermissionsRequiredAndUserHasAll(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
            ['id' => '2', 'key' => 'user', 'name' => 'User'],
            ['id' => '3', 'key' => 'viewer', 'name' => 'Viewer'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canDelete', 'canView', 'canCreate'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin', 'user'],
            'permissions' => ['canEdit', 'canDelete'],
        ]);

        $this->assertTrue($result);
    }

    public function testMultipleRolesAndPermissionsRequiredButUserMissingSomeRoles(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'user', 'name' => 'User'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit', 'canDelete', 'canView'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin', 'user'],
            'permissions' => ['canEdit', 'canDelete'],
        ]);

        $this->assertFalse($result);
    }

    public function testMultipleRolesAndPermissionsRequiredButUserMissingSomePermissions(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
            ['id' => '2', 'key' => 'user', 'name' => 'User'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin', 'user'],
            'permissions' => ['canEdit', 'canDelete'],
        ]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // Custom Conditions in Combined has()
    // =========================================================================

    public function testCustomConditionsForRolesInCombined(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->has([
            'roles' => [
                [
                    'role' => 'admin',
                    'condition' => fn(array $role) => $role['name'] === 'Administrator',
                ],
            ],
            'permissions' => ['canEdit'],
        ]);

        $this->assertTrue($result);
    }

    public function testCustomConditionsForPermissionsInCombined(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => [
                [
                    'permission' => 'canEdit',
                    'condition' => fn(array $context) => $context['orgCode'] === 'org_123',
                ],
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testFeatureFlagValueConditionsInCombined(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => [
                'darkMode', // string flag
                ['flag' => 'theme', 'value' => 'dark'], // KV condition
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCustomConditionsForBillingEntitlementsInCombined(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockEntitlements([
            new MockEntitlement(
                featureKey: 'pro_gym',
                featureName: 'Pro Gym',
                id: 'ent_1',
                fixedCharge: 35,
                priceName: 'Pro gym'
            ),
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'billingEntitlements' => [
                [
                    'entitlement' => 'pro_gym',
                    'condition' => fn($e) => $e->getFixedCharge() >= 30,
                ],
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testAllAdvancedConditionTypesAndAllPass(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => [
                [
                    'role' => 'admin',
                    'condition' => fn(array $role) => $role['name'] === 'Administrator',
                ],
            ],
            'permissions' => [
                [
                    'permission' => 'canEdit',
                    'condition' => fn(array $context) => $context['orgCode'] === 'org_123',
                ],
            ],
            'featureFlags' => [
                'darkMode', // string flag
                ['flag' => 'theme', 'value' => 'dark'], // KV condition
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testAllAdvancedConditionsButRoleConditionFails(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'admin'], // Note: lowercase name
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => [
                [
                    'role' => 'admin',
                    'condition' => fn(array $role) => $role['name'] === 'Administrator', // Fails
                ],
            ],
            'permissions' => [
                [
                    'permission' => 'canEdit',
                    'condition' => fn(array $context) => $context['orgCode'] === 'org_123',
                ],
            ],
            'featureFlags' => ['darkMode', ['flag' => 'theme', 'value' => 'dark']],
        ]);

        $this->assertFalse($result);
    }

    public function testAllAdvancedConditionsButPermissionConditionFails(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_456', // Different org code
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'dark', 't' => 's'],
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => [
                [
                    'role' => 'admin',
                    'condition' => fn(array $role) => $role['name'] === 'Administrator',
                ],
            ],
            'permissions' => [
                [
                    'permission' => 'canEdit',
                    'condition' => fn(array $context) => $context['orgCode'] === 'org_123', // Fails
                ],
            ],
            'featureFlags' => ['darkMode', ['flag' => 'theme', 'value' => 'dark']],
        ]);

        $this->assertFalse($result);
    }

    public function testAllAdvancedConditionsButFeatureFlagConditionFails(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'theme' => ['v' => 'light', 't' => 's'], // Different value
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => [
                [
                    'role' => 'admin',
                    'condition' => fn(array $role) => $role['name'] === 'Administrator',
                ],
            ],
            'permissions' => [
                [
                    'permission' => 'canEdit',
                    'condition' => fn(array $context) => $context['orgCode'] === 'org_123',
                ],
            ],
            'featureFlags' => [
                'darkMode',
                ['flag' => 'theme', 'value' => 'dark'], // Fails - value mismatch
            ],
        ]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // Early Exit Behavior
    // =========================================================================

    public function testEarlyExitWhenRolesFail(): void
    {
        $this->client->setMockRoles([]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        // Roles check fails first, so permissions should not be checked
        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
        ]);

        $this->assertFalse($result);
        
        // Verify roles were checked
        $this->assertTrue($this->client->wasMethodCalled('getRoles'));
    }

    public function testEarlyExitWhenPermissionsFail(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => [], // No permissions
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        // Permissions check fails, so feature flags should not be checked
        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode'],
        ]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // ForceApi Option
    // =========================================================================

    public function testForceApiAsBooleanFalse(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'darkMode' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
            'featureFlags' => ['darkMode'],
        ], false);

        $this->assertTrue($result);
        
        // Verify forceApi was passed to getRoles as false
        $calls = $this->client->getMethodCalls('getRoles');
        $this->assertFalse($calls[0]['forceApi'] ?? true);
    }

    public function testForceApiNullUsesDefault(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
        ]);

        $this->assertTrue($result);
        
        // Verify getRoles was called (forceApi parameter handling is implementation detail)
        $calls = $this->client->getMethodCalls('getRoles');
        $this->assertCount(1, $calls);
    }

    public function testForceApiAsEmptyArray(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['canEdit'],
        ]);

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['canEdit'],
        ], []);

        $this->assertTrue($result);
    }

    public function testForceApiAsObjectWithSpecificFlagsForEachType(): void
    {
        // Set up mock data for token-based checks
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'tokenFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);
        // Roles and permissions will be "from API" (mocked)
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'apiAdmin', 'name' => 'API Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['apiCanEdit'],
        ]);

        $result = $this->client->has([
            'roles' => ['apiAdmin'],
            'permissions' => ['apiCanEdit'],
            'featureFlags' => ['tokenFlag'],
        ], [
            'roles' => true,
            'permissions' => true,
            'featureFlags' => false,
        ]);

        $this->assertTrue($result);

        // Verify forceApi settings were passed correctly
        $rolesCalls = $this->client->getMethodCalls('getRoles');
        $this->assertTrue($rolesCalls[0]['forceApi']);
    }

    public function testForceApiObjectWithMixedBooleanValues(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'apiAdmin', 'name' => 'API Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['tokenCanEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'tokenFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => ['apiAdmin'],
            'permissions' => ['tokenCanEdit'],
            'featureFlags' => ['tokenFlag'],
        ], [
            'roles' => true,      // Force API for roles
            'permissions' => false, // Use token for permissions
            'featureFlags' => false, // Use token for feature flags
        ]);

        $this->assertTrue($result);
    }

    public function testForceApiObjectWithUndefinedValuesUsesDefault(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'tokenAdmin', 'name' => 'Token Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['tokenCanEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'tokenFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);

        // Pass an object with no explicit values - should use defaults (token behavior)
        $result = $this->client->has([
            'roles' => ['tokenAdmin'],
            'permissions' => ['tokenCanEdit'],
            'featureFlags' => ['tokenFlag'],
        ], [
            // No explicit values set, should use default (false/token behavior)
        ]);

        $this->assertTrue($result);
    }

    public function testForceApiObjectWithCustomConditions(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'apiAdmin', 'name' => 'API Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_456',
            'permissions' => ['apiCanManage'],
        ]);

        $result = $this->client->has([
            'roles' => [
                [
                    'role' => 'apiAdmin',
                    'condition' => fn(array $role) => str_contains($role['name'], 'Administrator'),
                ],
            ],
            'permissions' => [
                [
                    'permission' => 'apiCanManage',
                    'condition' => fn(array $context) => $context['orgCode'] === 'org_456',
                ],
            ],
        ], [
            'roles' => true,
            'permissions' => true,
        ]);

        $this->assertTrue($result);
    }

    public function testForceApiObjectWhenOneApiCheckFails(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'apiUser', 'name' => 'API User'], // User doesn't have admin role
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['apiCanEdit'],
        ]);

        $result = $this->client->has([
            'roles' => ['apiAdmin'], // User doesn't have this role
            'permissions' => ['apiCanEdit'],
        ], [
            'roles' => true,
            'permissions' => true,
        ]);

        $this->assertFalse($result);
    }

    public function testForceApiObjectWithBillingEntitlements(): void
    {
        // Billing entitlements always use API, but forceApi object should still work
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'tokenAdmin', 'name' => 'Token Administrator'],
        ]);
        $this->client->setMockEntitlements([
            MockEntitlement::simple('pro_gym'),
        ]);

        $result = $this->client->has([
            'roles' => ['tokenAdmin'],
            'billingEntitlements' => ['pro_gym'],
        ], [
            'roles' => false, // Use token for roles
            'billingEntitlements' => true, // Redundant but allowed
        ]);

        $this->assertTrue($result);
    }

    public function testForceApiAsBooleanTrue(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'apiAdmin', 'name' => 'API Administrator'],
        ]);
        $this->client->setMockPermissions([
            'orgCode' => 'org_123',
            'permissions' => ['apiCanEdit'],
        ]);
        $this->client->setMockAccessTokenClaims([
            'feature_flags' => [
                'apiFlag' => ['v' => true, 't' => 'b'],
            ],
        ]);

        $result = $this->client->has([
            'roles' => ['apiAdmin'],
            'permissions' => ['apiCanEdit'],
            'featureFlags' => ['apiFlag'],
        ], true);

        $this->assertTrue($result);

        // Verify forceApi was passed to getRoles as true
        $calls = $this->client->getMethodCalls('getRoles');
        $this->assertTrue($calls[0]['forceApi']);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testEmptyArraysForAllConditionTypes(): void
    {
        $result = $this->client->has([
            'roles' => [],
            'permissions' => [],
            'featureFlags' => [],
            'billingEntitlements' => [],
        ]);

        $this->assertTrue($result);
    }

    public function testNullConditionTypes(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        // Only roles provided, others not present in array
        $result = $this->client->has([
            'roles' => ['admin'],
        ]);

        $this->assertTrue($result);
    }

    public function testUnknownConditionTypeIsIgnored(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        // Unknown condition type should be ignored
        $result = $this->client->has([
            'roles' => ['admin'],
            'unknownType' => ['something'],
        ]);

        $this->assertTrue($result);
    }
}

