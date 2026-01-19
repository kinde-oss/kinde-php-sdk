<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Exception;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Tests\Support\KindeTestCase;
use Kinde\KindeSDK\Tests\Support\TestableKindeClientSDK;

/**
 * Comprehensive tests for hasRoles method.
 * Mirrors js-utils hasRoles.test.ts test coverage.
 */
class HasRolesTest extends KindeTestCase
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
    // Basic Role Checks
    // =========================================================================

    public function testReturnsTrueWhenNoRolesProvided(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        $this->assertTrue($this->client->hasRoles([]));
    }

    public function testReturnsTrueWhenUserHasAllRequiredRoles(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
            ['id' => '2', 'key' => 'user', 'name' => 'User'],
            ['id' => '3', 'key' => 'viewer', 'name' => 'Viewer'],
        ]);

        $this->assertTrue($this->client->hasRoles(['admin', 'user']));
    }

    public function testReturnsFalseWhenUserHasSomeButNotAllRequiredRoles(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        $this->assertFalse($this->client->hasRoles(['admin', 'user']));
    }

    public function testReturnsFalseWhenUserHasNoRequiredRoles(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'viewer', 'name' => 'Viewer'],
        ]);

        $this->assertFalse($this->client->hasRoles(['admin', 'user']));
    }

    public function testReturnsTrueWhenUserHasSingleRequiredRole(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        $this->assertTrue($this->client->hasRoles(['admin']));
    }

    public function testReturnsFalseWhenTokenHasNoRoles(): void
    {
        $this->client->setMockRoles([]);

        $this->assertFalse($this->client->hasRoles(['admin']));
    }

    public function testReturnsFalseWhenRolesIsNull(): void
    {
        $this->client->setMockAccessTokenClaims(['roles' => null]);

        $this->assertFalse($this->client->hasRoles(['admin']));
    }

    // =========================================================================
    // No Token Scenarios (mirrors js-utils "when no token" tests)
    // =========================================================================

    public function testReturnsFalseWhenNoTokenAndNoMockData(): void
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
        $result = $freshClient->hasRoles(['admin']);

        $this->assertFalse($result);
    }

    public function testReturnsTrueWhenNoTokenButEmptyRolesRequired(): void
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

        // Empty roles array should return true (no roles required)
        $result = $freshClient->hasRoles([]);

        $this->assertTrue($result);
    }

    // =========================================================================
    // Custom Conditions
    // =========================================================================

    public function testCustomConditionReturnsTrueWhenConditionPasses(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
        ]);

        $result = $this->client->hasRoles([
            [
                'role' => 'admin',
                'condition' => fn(array $role) => $role['name'] === 'Administrator',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCustomConditionReturnsFalseWhenConditionFails(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'admin'],
        ]);

        $result = $this->client->hasRoles([
            [
                'role' => 'admin',
                'condition' => fn(array $role) => $role['name'] === 'Administrator',
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testCustomConditionCanAccessFullRoleObject(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'superAdmin', 'name' => 'Super Administrator'],
        ]);

        $result = $this->client->hasRoles([
            [
                'role' => 'superAdmin',
                'condition' => function (array $role) {
                    return str_contains($role['key'], 'Admin') &&
                           str_contains($role['name'], 'Administrator');
                },
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testCombiningStringRolesAndCustomConditions(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
            ['id' => '2', 'key' => 'editor', 'name' => 'Editor'],
        ]);

        $result = $this->client->hasRoles([
            'admin', // string role - check existence
            [
                'role' => 'editor',
                'condition' => fn(array $role) => $role['key'] === 'editor',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testOneConditionFailsInMixedTypes(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'admin'],
        ]);

        $result = $this->client->hasRoles([
            'admin', // string role - passes
            [
                'role' => 'editor',
                'condition' => fn() => true, // condition won't be called because role doesn't exist
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testCustomConditionForNonExistentRole(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'admin'],
        ]);

        $result = $this->client->hasRoles([
            [
                'role' => 'nonExistentRole',
                'condition' => fn() => true, // Won't be called because role doesn't exist
            ],
        ]);

        $this->assertFalse($result);
    }

    public function testMultipleCustomConditionsAllPassing(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
            ['id' => '2', 'key' => 'manager', 'name' => 'Manager'],
        ]);

        $result = $this->client->hasRoles([
            [
                'role' => 'admin',
                'condition' => fn(array $role) => $role['id'] === '1',
            ],
            [
                'role' => 'manager',
                'condition' => fn(array $role) => $role['name'] === 'Manager',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testMultipleCustomConditionsOneFailing(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Administrator'],
            ['id' => '2', 'key' => 'manager', 'name' => 'Manager'],
        ]);

        $result = $this->client->hasRoles([
            [
                'role' => 'admin',
                'condition' => fn(array $role) => $role['id'] === '1', // passes
            ],
            [
                'role' => 'manager',
                'condition' => fn(array $role) => $role['name'] === 'Director', // fails
            ],
        ]);

        $this->assertFalse($result);
    }

    // =========================================================================
    // Error Handling
    // =========================================================================

    public function testReturnsFalseOnException(): void
    {
        $this->client->setRolesException(new Exception('API Error'));

        $this->assertFalse($this->client->hasRoles(['admin']));
    }

    public function testGracefullyHandlesApiFailure(): void
    {
        $this->client->setRolesException(new Exception('Network timeout'));

        // Should not throw, should return false
        $result = $this->client->hasRoles(['admin', 'user']);
        
        $this->assertFalse($result);
    }

    // =========================================================================
    // ForceApi Option
    // =========================================================================

    public function testForceApiParameterIsPassed(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        $this->client->hasRoles(['admin'], true);

        $calls = $this->client->getMethodCalls('getRoles');
        $this->assertCount(1, $calls);
        $this->assertTrue($calls[0]['forceApi']);
    }

    public function testForceApiFalseParameterIsPassed(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        $this->client->hasRoles(['admin'], false);

        $calls = $this->client->getMethodCalls('getRoles');
        $this->assertCount(1, $calls);
        $this->assertFalse($calls[0]['forceApi']);
    }

    public function testForceApiNullUsesDefault(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        $this->client->hasRoles(['admin']);

        $calls = $this->client->getMethodCalls('getRoles');
        $this->assertCount(1, $calls);
        $this->assertNull($calls[0]['forceApi']);
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    public function testEmptyRoleKeyHandling(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => '', 'name' => 'Empty Key Role'],
        ]);

        $this->assertFalse($this->client->hasRoles(['admin']));
        $this->assertTrue($this->client->hasRoles(['']));
    }

    public function testRoleKeysAreCaseSensitive(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'Admin', 'name' => 'Admin'],
        ]);

        $this->assertFalse($this->client->hasRoles(['admin']));
        $this->assertTrue($this->client->hasRoles(['Admin']));
    }

    public function testLargeNumberOfRoles(): void
    {
        $roles = [];
        for ($i = 1; $i <= 100; $i++) {
            $roles[] = ['id' => (string) $i, 'key' => "role_$i", 'name' => "Role $i"];
        }
        $this->client->setMockRoles($roles);

        $this->assertTrue($this->client->hasRoles(['role_1', 'role_50', 'role_100']));
        $this->assertFalse($this->client->hasRoles(['role_1', 'role_101']));
    }

    public function testSpecialCharactersInRoleKeys(): void
    {
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'role:admin', 'name' => 'Role Admin'],
            ['id' => '2', 'key' => 'role.editor', 'name' => 'Role Editor'],
            ['id' => '3', 'key' => 'role-viewer', 'name' => 'Role Viewer'],
        ]);

        $this->assertTrue($this->client->hasRoles(['role:admin', 'role.editor', 'role-viewer']));
    }

    public function testEarlyExitOnFirstFailure(): void
    {
        $callCount = 0;
        
        $this->client->setMockRoles([
            ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
        ]);

        // Check for 'admin' (exists) and 'missing' (doesn't exist)
        // Should stop checking after 'missing' fails
        $result = $this->client->hasRoles(['missing', 'admin']);

        $this->assertFalse($result);
    }

    public function testRolesFromClaimsWhenMockRolesNotSet(): void
    {
        $this->client->setMockAccessTokenClaims([
            'roles' => [
                ['id' => '1', 'key' => 'admin', 'name' => 'Admin'],
            ],
        ]);

        $this->assertTrue($this->client->hasRoles(['admin']));
    }

    public function testStringRolesInClaimsAreNormalized(): void
    {
        $this->client->setMockAccessTokenClaims([
            'roles' => ['admin', 'user'], // String roles instead of objects
        ]);

        $this->assertTrue($this->client->hasRoles(['admin']));
    }
}

