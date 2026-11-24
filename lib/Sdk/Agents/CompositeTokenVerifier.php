<?php

namespace Kinde\KindeSDK\Sdk\Agents;

/**
 * Checks the token locally first so forged or expired tokens never reach Kinde, then asks
 * introspection, which is authoritative and catches revocation.
 */
final class CompositeTokenVerifier implements TokenVerifier
{
    private TokenVerifier $local;
    private TokenVerifier $authoritative;

    public function __construct(TokenVerifier $local, TokenVerifier $authoritative)
    {
        $this->local = $local;
        $this->authoritative = $authoritative;
    }

    public function verify(string $accessToken): KindeAuthContext
    {
        $context = $this->local->verify($accessToken);
        return $context->isAuthenticated() ? $this->authoritative->verify($accessToken) : $context;
    }
}
