<?php

namespace Kinde\KindeSDK\Tests\Unit;

use Exception;
use InvalidArgumentException;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\GrantType;
use Kinde\KindeSDK\Sdk\Enums\StorageEnums;
use Kinde\KindeSDK\Sdk\Enums\TokenType;
use PHPUnit\Framework\TestCase;

class KindeClientSDKBehaviorTest extends TestCase
{
    private TestableKindeClientSDK $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new TestableKindeClientSDK(
            'https://test-domain.kinde.com',
            'http://localhost:8000/auth/callback',
            'test_client_id',
            'test_client_secret',
            GrantType::authorizationCode,
            'http://localhost:8000'
        );
    }

    public function testHasRolesReturnsTrueWhenNoRolesProvided(): void
    {
        $this->assertTrue($this->client->hasRoles());
    }

    public function testHasRolesReturnsFalseWhenRoleMissing(): void
    {
        $this->client->rolesResult = [
            ['key' => 'admin', 'id' => null, 'name' => 'admin'],
        ];

        $this->assertFalse($this->client->hasRoles(['user']));
    }

    public function testHasRolesSupportsCustomCondition(): void
    {
        $this->client->rolesResult = [
            ['key' => 'admin', 'id' => '1', 'name' => 'Admin'],
        ];

        $result = $this->client->hasRoles([
            [
                'role' => 'admin',
                'condition' => fn(array $role) => $role['name'] === 'Admin',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testHasRolesReturnsFalseOnException(): void
    {
        $this->client->rolesException = new Exception('roles failed');
        $this->assertFalse($this->client->hasRoles(['admin']));
    }

    public function testHasPermissionsUsesOrgCodeForCustomCondition(): void
    {
        $this->client->permissionsResult = [
            'orgCode' => 'org_123',
            'permissions' => ['read', 'write'],
        ];

        $result = $this->client->hasPermissions([
            [
                'permission' => 'read',
                'condition' => fn(array $context) => $context['orgCode'] === 'org_123',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testHasPermissionsReturnsFalseWhenMissingPermission(): void
    {
        $this->client->permissionsResult = [
            'orgCode' => 'org_123',
            'permissions' => ['read'],
        ];

        $this->assertFalse($this->client->hasPermissions(['write']));
    }

    public function testHasFeatureFlagsSupportsValueConditions(): void
    {
        $this->client->claimOverrides['feature_flags'] = [
            'name' => 'feature_flags',
            'value' => [
                'flag_a' => ['v' => true, 't' => 'b'],
                'flag_b' => ['v' => 'beta', 't' => 's'],
            ],
        ];

        $result = $this->client->hasFeatureFlags([
            ['flag' => 'flag_b', 'value' => 'beta'],
        ]);

        $this->assertTrue($result);
    }

    public function testHasFeatureFlagsReturnsFalseWhenFlagMissing(): void
    {
        $this->client->claimOverrides['feature_flags'] = [
            'name' => 'feature_flags',
            'value' => ['flag_a' => ['v' => true, 't' => 'b']],
        ];

        $this->assertFalse($this->client->hasFeatureFlags(['flag_missing']));
    }

    public function testHasBillingEntitlementsUsesCustomCondition(): void
    {
        $this->client->entitlementsResult = [
            new class('pro') {
                public function __construct(private string $key) {}
                public function getFeatureKey(): string { return $this->key; }
            },
        ];

        $result = $this->client->hasBillingEntitlements([
            [
                'entitlement' => 'pro',
                'condition' => fn($entitlement) => $entitlement->getFeatureKey() === 'pro',
            ],
        ]);

        $this->assertTrue($result);
    }

    public function testHasBillingEntitlementsReturnsFalseOnException(): void
    {
        $this->client->entitlementsException = new Exception('entitlements failed');
        $this->assertFalse($this->client->hasBillingEntitlements(['pro']));
    }

    public function testHasCombinesChecksAndStopsOnFailure(): void
    {
        $this->client->rolesResult = [];
        $this->client->permissionsResult = [
            'orgCode' => 'org_123',
            'permissions' => ['read'],
        ];

        $result = $this->client->has([
            'roles' => ['admin'],
            'permissions' => ['read'],
        ]);

        $this->assertFalse($result);
    }

    public function testGeneratePortalUrlThrowsWhenAccessTokenMissing(): void
    {
        unset($_COOKIE['kinde_' . StorageEnums::TOKEN]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('generatePortalUrl: Access Token not found');

        $this->client->generatePortalUrl('https://example.com');
    }

    public function testGeneratePortalUrlRejectsInvalidReturnUrl(): void
    {
        $_COOKIE['kinde_' . StorageEnums::TOKEN] = json_encode(['access_token' => 'test_token']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('generatePortalUrl: returnUrl must be an absolute URL');

        $this->client->generatePortalUrl('not-a-url');
    }
}

class TestableKindeClientSDK extends KindeClientSDK
{
    public array $rolesResult = [];
    public ?Exception $rolesException = null;
    public array $permissionsResult = [];
    public ?Exception $permissionsException = null;
    public array $claimOverrides = [];
    public array $entitlementsResult = [];
    public ?Exception $entitlementsException = null;

    public function getRoles(?bool $forceApi = null): array
    {
        if ($this->rolesException) {
            throw $this->rolesException;
        }

        return $this->rolesResult;
    }

    public function getPermissions()
    {
        if ($this->permissionsException) {
            throw $this->permissionsException;
        }

        return $this->permissionsResult;
    }

    public function getClaim(string $keyName, string $tokenType = TokenType::ACCESS_TOKEN)
    {
        return $this->claimOverrides[$keyName] ?? ['name' => $keyName, 'value' => null];
    }

    public function getAllEntitlements(): array
    {
        if ($this->entitlementsException) {
            throw $this->entitlementsException;
        }

        return $this->entitlementsResult;
    }
}

