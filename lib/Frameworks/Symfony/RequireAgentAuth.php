<?php

namespace Kinde\KindeSDK\Frameworks\Symfony;

/**
 * Marks a controller (class or method) as callable only by AI agents holding a Kinde-issued
 * access token, optionally with the given Kinde permissions. Enforced by KindeAgentAuthSubscriber.
 *
 *     #[RequireAgentAuth(permissions: ['write:orders'])]
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class RequireAgentAuth
{
    /** @var string[] */
    public array $permissions;

    /**
     * @param string[] $permissions
     */
    public function __construct(array $permissions = [])
    {
        $this->permissions = $permissions;
    }
}
