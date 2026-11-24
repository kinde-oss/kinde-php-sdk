<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

/**
 * Tokens issued to an agent, along with the client they were issued to. Refresh and revocation
 * must use that same client, so it travels with the tokens.
 */
final class TokenSet
{
    public string $accessToken;
    public ?string $refreshToken;
    public int $expiresAt;
    public string $tokenType;
    public ?string $scope;
    public string $clientId;
    public ?string $clientSecret;

    public function __construct(
        string $accessToken,
        ?string $refreshToken,
        int $expiresAt,
        string $tokenType,
        ?string $scope,
        string $clientId,
        ?string $clientSecret
    ) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = $expiresAt;
        $this->tokenType = $tokenType;
        $this->scope = $scope;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * True when the access token has expired or will within $leeway seconds.
     */
    public function isExpired(int $leeway = 30): bool
    {
        return time() + $leeway >= $this->expiresAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['accessToken'], $data['expiresAt'], $data['clientId'])) {
            throw new AgentAuthException('invalid_state', 'Stored token set is incomplete.');
        }
        return new self(
            (string) $data['accessToken'],
            isset($data['refreshToken']) ? (string) $data['refreshToken'] : null,
            (int) $data['expiresAt'],
            (string) ($data['tokenType'] ?? 'Bearer'),
            isset($data['scope']) ? (string) $data['scope'] : null,
            (string) $data['clientId'],
            isset($data['clientSecret']) ? (string) $data['clientSecret'] : null
        );
    }
}
