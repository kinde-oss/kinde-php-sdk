<?php

namespace Kinde\KindeSDK\Tests\Support;

use Exception;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\Sdk\Enums\TokenType;

/**
 * Testable version of KindeClientSDK that allows injecting mock data.
 * Used for unit testing without HTTP/JWKS dependencies.
 */
class TestableKindeClientSDK extends KindeClientSDK
{
    /**
     * Mock claims to return from getClaims.
     */
    private ?array $mockAccessTokenClaims = null;
    private ?array $mockIdTokenClaims = null;

    /**
     * Mock data for various methods.
     */
    private ?array $mockRoles = null;
    private ?array $mockApiRoles = null;
    private ?array $mockPermissions = null;
    private ?array $mockApiPermissions = null;
    private ?array $mockFeatureFlags = null;
    private ?array $mockApiFeatureFlags = null;
    private ?array $mockEntitlements = null;

    /**
     * Exceptions to throw (for error handling tests).
     */
    private ?Exception $rolesException = null;
    private ?Exception $permissionsException = null;
    private ?Exception $featureFlagsException = null;
    private ?Exception $entitlementsException = null;

    /**
     * Track whether certain methods were called (for behavior verification).
     */
    private array $methodCalls = [];

    /**
     * Set mock claims for access token.
     *
     * @param array $claims Mock claims
     * @return self
     */
    public function setMockAccessTokenClaims(array $claims): self
    {
        $this->mockAccessTokenClaims = $claims;
        return $this;
    }

    /**
     * Set mock claims for ID token.
     *
     * @param array $claims Mock claims
     * @return self
     */
    public function setMockIdTokenClaims(array $claims): self
    {
        $this->mockIdTokenClaims = $claims;
        return $this;
    }

    /**
     * Set mock roles data.
     *
     * @param array $roles Array of role objects
     * @return self
     */
    public function setMockRoles(array $roles): self
    {
        $this->mockRoles = $roles;
        return $this;
    }

    /**
     * Set mock roles data for API path.
     *
     * @param array $roles Array of role objects
     * @return self
     */
    public function setMockApiRoles(array $roles): self
    {
        $this->mockApiRoles = $roles;
        return $this;
    }

    /**
     * Set mock permissions data.
     *
     * @param array $permissions Permissions response array
     * @return self
     */
    public function setMockPermissions(array $permissions): self
    {
        $this->mockPermissions = $permissions;
        return $this;
    }

    /**
     * Set mock permissions data for API path.
     *
     * @param array $permissions Permissions response array
     * @return self
     */
    public function setMockApiPermissions(array $permissions): self
    {
        $this->mockApiPermissions = $permissions;
        return $this;
    }

    /**
     * Set mock feature flags data.
     *
     * @param array $featureFlags Feature flags array
     * @return self
     */
    public function setMockFeatureFlags(array $featureFlags): self
    {
        $this->mockFeatureFlags = $featureFlags;
        return $this;
    }

    /**
     * Set mock feature flags data for API path.
     *
     * @param array $featureFlags Feature flags array
     * @return self
     */
    public function setMockApiFeatureFlags(array $featureFlags): self
    {
        $this->mockApiFeatureFlags = $featureFlags;
        return $this;
    }

    /**
     * Set mock entitlements data.
     *
     * @param array $entitlements Entitlements array
     * @return self
     */
    public function setMockEntitlements(array $entitlements): self
    {
        $this->mockEntitlements = $entitlements;
        return $this;
    }

    /**
     * Set an exception to throw when getRoles is called.
     *
     * @param Exception $exception Exception to throw
     * @return self
     */
    public function setRolesException(Exception $exception): self
    {
        $this->rolesException = $exception;
        return $this;
    }

    /**
     * Set an exception to throw when getPermissions is called.
     *
     * @param Exception $exception Exception to throw
     * @return self
     */
    public function setPermissionsException(Exception $exception): self
    {
        $this->permissionsException = $exception;
        return $this;
    }

    /**
     * Set an exception to throw when getting feature flags.
     *
     * @param Exception $exception Exception to throw
     * @return self
     */
    public function setFeatureFlagsException(Exception $exception): self
    {
        $this->featureFlagsException = $exception;
        return $this;
    }

    /**
     * Set an exception to throw when getAllEntitlements is called.
     *
     * @param Exception $exception Exception to throw
     * @return self
     */
    public function setEntitlementsException(Exception $exception): self
    {
        $this->entitlementsException = $exception;
        return $this;
    }

    /**
     * Override getRoles to use mock data.
     *
     * @param bool|null $forceApi Ignored in test mode
     * @return array
     */
    public function getRoles(?bool $forceApi = null): array
    {
        $this->recordMethodCall('getRoles', ['forceApi' => $forceApi]);

        $useApi = $forceApi ?? $this->forceApi;
        if ($useApi) {
            if ($this->mockApiRoles !== null) {
                return $this->mockApiRoles;
            }
            if ($this->mockRoles !== null) {
                return $this->mockRoles;
            }
        }

        if ($this->rolesException) {
            throw $this->rolesException;
        }

        if ($this->mockRoles !== null) {
            return $this->mockRoles;
        }

        // Fall back to claims-based if mock data not set
        if ($this->mockAccessTokenClaims !== null) {
            $roles = $this->mockAccessTokenClaims['roles'] ?? [];
            return array_map(function ($role) {
                if (is_string($role)) {
                    return ['key' => $role, 'id' => null, 'name' => $role];
                }
                return $role;
            }, $roles);
        }

        return [];
    }

    /**
     * Override getPermissions to use mock data.
     *
     * @return array
     */
    public function getPermissions()
    {
        $this->recordMethodCall('getPermissions');

        if ($this->permissionsException) {
            throw $this->permissionsException;
        }

        if ($this->mockPermissions !== null) {
            return $this->mockPermissions;
        }

        if ($this->mockAccessTokenClaims !== null) {
            return [
                'orgCode' => $this->mockAccessTokenClaims['org_code'] ?? null,
                'permissions' => $this->mockAccessTokenClaims['permissions'] ?? [],
            ];
        }

        return ['orgCode' => null, 'permissions' => []];
    }

    /**
     * Override hasPermissions to record calls while exercising real logic.
     * For forceApi=true, use mocked API data to avoid network calls.
     *
     * @param array $permissions Array of permission keys or permission condition objects
     * @param bool|null $forceApi Force API call (recorded and handled)
     * @return bool True if user has all specified permissions
     */
    public function hasPermissions(array $permissions = [], ?bool $forceApi = null): bool
    {
        $this->recordMethodCall('hasPermissions', ['forceApi' => $forceApi]);
        return parent::hasPermissions($permissions, $forceApi);
    }

    /**
     * Override hasFeatureFlags to record calls while exercising real logic.
     * For forceApi=true, use mocked API data to avoid network calls.
     *
     * @param array $featureFlags Array of feature flag keys or flag condition objects
     * @param bool|null $forceApi Force API call (recorded and handled)
     * @return bool True if user has all specified feature flags
     */
    public function hasFeatureFlags(array $featureFlags = [], ?bool $forceApi = null): bool
    {
        $this->recordMethodCall('hasFeatureFlags', ['forceApi' => $forceApi]);
        return parent::hasFeatureFlags($featureFlags, $forceApi);
    }

    /**
     * Override getClaim to use mock data.
     *
     * @param string $keyName Claim key name
     * @param string $tokenType Token type
     * @return array
     */
    public function getClaim(string $keyName, string $tokenType = TokenType::ACCESS_TOKEN)
    {
        $this->recordMethodCall('getClaim', ['keyName' => $keyName, 'tokenType' => $tokenType]);

        if ($this->featureFlagsException && $keyName === 'feature_flags') {
            throw $this->featureFlagsException;
        }

        $claims = $tokenType === TokenType::ACCESS_TOKEN 
            ? $this->mockAccessTokenClaims 
            : $this->mockIdTokenClaims;

        if ($claims === null) {
            return ['name' => $keyName, 'value' => null];
        }

        return ['name' => $keyName, 'value' => $claims[$keyName] ?? null];
    }

    /**
     * Override getAllEntitlements to use mock data.
     *
     * @return array
     */
    public function getAllEntitlements(): array
    {
        $this->recordMethodCall('getAllEntitlements');

        if ($this->entitlementsException) {
            throw $this->entitlementsException;
        }

        if ($this->mockEntitlements !== null) {
            return $this->mockEntitlements;
        }

        return [];
    }

    /**
     * Override hasBillingEntitlements to record method calls in test mode.
     * Uses parent logic with mocked entitlements.
     *
     * @param array $billingEntitlements Array of entitlement keys or entitlement condition objects
     * @return bool True if user has all specified billing entitlements
     */
    public function hasBillingEntitlements(array $billingEntitlements = []): bool
    {
        $this->recordMethodCall('hasBillingEntitlements', ['billingEntitlements' => $billingEntitlements]);

        return parent::hasBillingEntitlements($billingEntitlements);
    }


    /**
     * Record a method call for verification.
     *
     * @param string $method Method name
     * @param array $args Method arguments
     */
    private function recordMethodCall(string $method, array $args = []): void
    {
        if (!isset($this->methodCalls[$method])) {
            $this->methodCalls[$method] = [];
        }
        $this->methodCalls[$method][] = $args;
    }

    /**
     * Get recorded method calls.
     *
     * @param string|null $method Specific method to get calls for
     * @return array
     */
    public function getMethodCalls(?string $method = null): array
    {
        if ($method !== null) {
            return $this->methodCalls[$method] ?? [];
        }
        return $this->methodCalls;
    }

    /**
     * Check if a method was called.
     *
     * @param string $method Method name
     * @return bool
     */
    public function wasMethodCalled(string $method): bool
    {
        return isset($this->methodCalls[$method]) && count($this->methodCalls[$method]) > 0;
    }

    /**
     * Get the number of times a method was called.
     *
     * @param string $method Method name
     * @return int
     */
    public function getMethodCallCount(string $method): int
    {
        return count($this->methodCalls[$method] ?? []);
    }

    /**
     * Reset all mock data and recorded calls.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->mockAccessTokenClaims = null;
        $this->mockIdTokenClaims = null;
        $this->mockRoles = null;
        $this->mockApiRoles = null;
        $this->mockPermissions = null;
        $this->mockApiPermissions = null;
        $this->mockFeatureFlags = null;
        $this->mockApiFeatureFlags = null;
        $this->mockEntitlements = null;
        $this->rolesException = null;
        $this->permissionsException = null;
        $this->featureFlagsException = null;
        $this->entitlementsException = null;
        $this->methodCalls = [];
        return $this;
    }

    /**
     * Mock API path for permissions.
     *
     * @return array
     */
    protected function getPermissionsFromApi(): array
    {
        $this->recordMethodCall('getPermissionsFromApi');
        if ($this->mockApiPermissions !== null) {
            return $this->mockApiPermissions;
        }
        if ($this->mockPermissions !== null) {
            return $this->mockPermissions;
        }
        return ['orgCode' => null, 'permissions' => []];
    }

    /**
     * Mock API path for feature flags.
     *
     * @return array
     */
    protected function getFeatureFlagsFromApi(): array
    {
        $this->recordMethodCall('getFeatureFlagsFromApi');
        if ($this->mockApiFeatureFlags !== null) {
            return $this->mockApiFeatureFlags;
        }
        if ($this->mockFeatureFlags !== null) {
            return $this->mockFeatureFlags;
        }
        return [];
    }

    /**
     * Expose feature flag mapping for tests without hitting API.
     *
     * @param mixed $data Feature flags response data
     * @return array
     */
    public function mapFeatureFlagsDataForTest($data): array
    {
        return $this->processFeatureFlagsData($data);
    }
}

