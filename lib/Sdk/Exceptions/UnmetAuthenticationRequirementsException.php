<?php

namespace Kinde\KindeSDK\Sdk\Exceptions;

/**
 * Thrown when the requested authentication context cannot be met. Retrying gives the same error.
 */
class UnmetAuthenticationRequirementsException extends CallbackErrorException
{
    public const ERROR_CODE = 'unmet_authentication_requirements';
}
