<?php

namespace Kinde\KindeSDK\Frameworks\Symfony;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Kinde\KindeSDK\KindeClientSDK;

class KindeSecurityVoter extends Voter
{
    protected KindeClientSDK $kindeClient;

    public function __construct(KindeClientSDK $kindeClient)
    {
        $this->kindeClient = $kindeClient;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Support any permission check
        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Check if user is authenticated
        if (!$this->kindeClient->isAuthenticated) {
            return false;
        }

        // Check specific permission
        $permissionCheck = $this->kindeClient->getPermission($attribute);
        
        return $permissionCheck['isGranted'];
    }
} 