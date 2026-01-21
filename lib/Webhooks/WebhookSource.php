<?php

namespace Kinde\KindeSDK\Webhooks;

/**
 * Origin of the webhook event.
 */
final class WebhookSource
{
    public const ADMIN = 'admin';
    public const API = 'api';
    public const USER = 'user';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::ADMIN,
            self::API,
            self::USER,
        ];
    }
}
