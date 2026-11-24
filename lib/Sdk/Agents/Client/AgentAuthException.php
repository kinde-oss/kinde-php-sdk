<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

/**
 * Raised by the agent client. The error code is the OAuth error returned by Kinde (for example
 * invalid_grant) or an SDK code such as invalid_state or discovery_failed.
 */
final class AgentAuthException extends \RuntimeException
{
    private string $errorCode;
    private ?int $statusCode;

    public function __construct(string $errorCode, string $message, ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
