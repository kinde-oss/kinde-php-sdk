<?php

namespace Kinde\KindeSDK\Sdk\Exceptions;

use Kinde\KindeSDK\OAuthException;

/**
 * An error returned to the callback, after the state was validated.
 */
abstract class CallbackErrorException extends OAuthException
{
    private string $errorDescription;
    private ?string $state;

    public function __construct(string $errorDescription = '', ?string $state = null)
    {
        parent::__construct($errorDescription !== '' ? $errorDescription : static::ERROR_CODE);
        $this->errorDescription = $errorDescription;
        $this->state = $state;
    }

    public function getErrorDescription(): string
    {
        return $this->errorDescription;
    }

    public function getState(): ?string
    {
        return $this->state;
    }
}
