<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

/**
 * A pending authorization: the URL to send the user to, plus the state, PKCE verifier and client
 * the callback needs. Keep it (for example in the session, via toArray) until the callback arrives.
 */
final class AuthorizationRequest
{
    public string $url;
    public string $state;
    public string $codeVerifier;
    public string $redirectUri;
    public string $clientId;
    public ?string $clientSecret;
    public string $issuer;
    public string $tokenEndpoint;
    public ?string $resource;
    /** Whether the server promised an iss parameter on the callback (RFC 9207). */
    public bool $issParameterRequired;

    public function __construct(
        string $url,
        string $state,
        string $codeVerifier,
        string $redirectUri,
        string $clientId,
        ?string $clientSecret,
        string $issuer,
        string $tokenEndpoint,
        ?string $resource,
        bool $issParameterRequired
    ) {
        $this->url = $url;
        $this->state = $state;
        $this->codeVerifier = $codeVerifier;
        $this->redirectUri = $redirectUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->issuer = $issuer;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->resource = $resource;
        $this->issParameterRequired = $issParameterRequired;
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
        foreach (['url', 'state', 'codeVerifier', 'redirectUri', 'clientId', 'issuer', 'tokenEndpoint'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                throw new AgentAuthException('invalid_state', "Stored authorization request is missing {$field}.");
            }
        }
        return new self(
            $data['url'],
            $data['state'],
            $data['codeVerifier'],
            $data['redirectUri'],
            $data['clientId'],
            $data['clientSecret'] ?? null,
            $data['issuer'],
            $data['tokenEndpoint'],
            $data['resource'] ?? null,
            (bool) ($data['issParameterRequired'] ?? false)
        );
    }
}
