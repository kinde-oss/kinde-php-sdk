<?php

namespace Kinde\KindeSDK\Sdk\Agents;

/**
 * The outcome of verifying an agent's access token: either the verified identity and grants, or
 * the reason the request must be refused.
 *
 * Scope, permission and role checks are case-sensitive.
 */
final class KindeAuthContext
{
    public const MISSING_TOKEN = 'missing_token';
    public const INVALID_TOKEN = 'invalid_token';
    public const INSUFFICIENT_SCOPE = 'insufficient_scope';
    public const UNAVAILABLE = 'authorization_server_unavailable';
    // A missing Kinde permission. Answered with a plain 403: permissions are not OAuth scopes, so
    // an insufficient_scope challenge would send the agent into a login loop.
    public const FORBIDDEN = 'forbidden';

    private ?string $failureKind;
    private ?string $failureReason;
    /** @var string[] */
    private array $requiredScopes = [];
    /** @var array<string, mixed> */
    private array $claims = [];
    private ?string $token = null;

    private function __construct(?string $failureKind, ?string $failureReason, array $claims = [])
    {
        $this->failureKind = $failureKind;
        $this->failureReason = $failureReason;
        $this->claims = $claims;
    }

    /**
     * @param array<string, mixed> $claims Verified token claims
     * @param string|null          $token  The raw access token, for calls made on the user's behalf
     */
    public static function fromClaims(array $claims, ?string $token = null): self
    {
        $context = new self(null, null, $claims);
        $context->token = $token;
        return $context;
    }

    public static function missingToken(string $reason = 'No bearer token was presented.'): self
    {
        return new self(self::MISSING_TOKEN, $reason);
    }

    public static function invalid(string $reason): self
    {
        return new self(self::INVALID_TOKEN, $reason);
    }

    public static function unavailable(string $reason): self
    {
        return new self(self::UNAVAILABLE, $reason);
    }

    /**
     * @param string[] $requiredScopes
     */
    public static function insufficientScope(array $requiredScopes, string $reason): self
    {
        $context = new self(self::INSUFFICIENT_SCOPE, $reason);
        $context->requiredScopes = array_values($requiredScopes);
        return $context;
    }

    public static function forbidden(string $reason): self
    {
        return new self(self::FORBIDDEN, $reason);
    }

    public function isAuthenticated(): bool
    {
        return $this->failureKind === null;
    }

    /**
     * One of the class constants, or null when authenticated.
     */
    public function getFailureKind(): ?string
    {
        return $this->failureKind;
    }

    /**
     * Diagnostic detail for logs. Not meant for the response body.
     */
    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    /**
     * @return string[]
     */
    public function getRequiredScopes(): array
    {
        return $this->requiredScopes;
    }

    /**
     * @return array<string, mixed>
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @return mixed
     */
    public function getClaim(string $name)
    {
        return $this->claims[$name] ?? null;
    }

    /**
     * The raw access token. Never log it.
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getSubject(): ?string
    {
        return self::scalar($this->claims['sub'] ?? null);
    }

    /**
     * The agent acting on the user's behalf (act.sub), when the token is delegated.
     */
    public function getActorSubject(): ?string
    {
        $act = $this->claims['act'] ?? null;
        return is_array($act) ? self::scalar($act['sub'] ?? null) : null;
    }

    public function getClientId(): ?string
    {
        return self::scalar($this->claims['client_id'] ?? null) ?? self::scalar($this->claims['azp'] ?? null);
    }

    public function getEmail(): ?string
    {
        return self::scalar($this->claims['email'] ?? null);
    }

    public function getOrganizationCode(): ?string
    {
        return self::scalar($this->claims['org_code'] ?? null);
    }

    public function getExpiresAt(): ?int
    {
        $exp = $this->claims['exp'] ?? null;
        return is_numeric($exp) ? (int) $exp : null;
    }

    /**
     * @return string[] From the space-delimited "scope" claim, falling back to the "scp" array.
     */
    public function getScopes(): array
    {
        $scopes = self::spaceDelimited($this->claims['scope'] ?? null);
        return $scopes !== [] ? $scopes : self::spaceDelimited($this->claims['scp'] ?? null);
    }

    /**
     * @return string[]
     */
    public function getPermissions(): array
    {
        return self::stringList($this->claims['permissions'] ?? null);
    }

    /**
     * @return string[] Role keys; roles given as objects use "key", falling back to "name".
     */
    public function getRoles(): array
    {
        $roles = [];
        foreach ((array) ($this->claims['roles'] ?? []) as $role) {
            if (is_array($role)) {
                $role = self::scalar($role['key'] ?? null) ?? self::scalar($role['name'] ?? null);
            }
            if (is_string($role) && trim($role) !== '') {
                $roles[] = $role;
            }
        }
        return array_values(array_unique($roles));
    }

    /**
     * @return string[]
     */
    public function getAudiences(): array
    {
        return self::stringList($this->claims['aud'] ?? null);
    }

    public function hasScope(string $scope): bool
    {
        return $scope !== '' && in_array($scope, $this->getScopes(), true);
    }

    public function hasPermission(string $permission): bool
    {
        return $permission !== '' && in_array($permission, $this->getPermissions(), true);
    }

    public function hasAllPermissions(string ...$permissions): bool
    {
        return array_diff($permissions, $this->getPermissions()) === [];
    }

    public function hasAnyPermission(string ...$permissions): bool
    {
        return array_intersect($permissions, $this->getPermissions()) !== [];
    }

    public function hasRole(string $role): bool
    {
        return $role !== '' && in_array($role, $this->getRoles(), true);
    }

    /**
     * @param mixed $value
     */
    private static function scalar($value): ?string
    {
        return is_string($value) && $value !== '' ? $value : (is_int($value) ? (string) $value : null);
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private static function stringList($value): array
    {
        $values = array_filter((array) $value, static function ($item): bool {
            return is_string($item) && $item !== '';
        });
        return array_values(array_unique($values));
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private static function spaceDelimited($value): array
    {
        $parts = [];
        foreach (self::stringList($value) as $item) {
            foreach (preg_split('/\s+/', $item, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                $parts[] = $part;
            }
        }
        return array_values(array_unique($parts));
    }
}
