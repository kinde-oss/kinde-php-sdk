<?php

namespace Kinde\KindeSDK\Sdk\Agents;

interface TokenVerifier
{
    /**
     * Verifies a bearer access token. Never throws for a bad token or an unreachable Kinde;
     * the outcome is reported through the returned context.
     */
    public function verify(string $accessToken): KindeAuthContext;
}
