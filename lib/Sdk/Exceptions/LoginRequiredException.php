<?php

namespace Kinde\KindeSDK\Sdk\Exceptions;

/**
 * Thrown when prompt=none cannot complete without an interactive login.
 */
class LoginRequiredException extends CallbackErrorException
{
    public const ERROR_CODE = 'login_required';
}
