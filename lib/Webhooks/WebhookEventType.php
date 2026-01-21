<?php

namespace Kinde\KindeSDK\Webhooks;

/**
 * Webhook event type constants mirroring webhook-main.
 */
final class WebhookEventType
{
    public const ORGANIZATION_CREATED = 'organization.created';
    public const ORGANIZATION_UPDATED = 'organization.updated';
    public const USER_CREATED = 'user.created';
    public const USER_UPDATED = 'user.updated';
    public const USER_DELETED = 'user.deleted';
    public const USER_AUTHENTICATION_FAILED = 'user.authentication_failed';
    public const USER_AUTHENTICATED = 'user.authenticated';
    public const ORGANIZATION_DELETED = 'organization.deleted';
    public const ROLE_CREATED = 'role.created';
    public const ROLE_UPDATED = 'role.updated';
    public const ROLE_DELETED = 'role.deleted';
    public const PERMISSION_CREATED = 'permission.created';
    public const PERMISSION_UPDATED = 'permission.updated';
    public const PERMISSION_DELETED = 'permission.deleted';
    public const SUBSCRIBER_CREATED = 'subscriber.created';
    public const ACCESS_REQUEST_CREATED = 'access_request.created';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::ORGANIZATION_CREATED,
            self::ORGANIZATION_UPDATED,
            self::USER_CREATED,
            self::USER_UPDATED,
            self::USER_DELETED,
            self::USER_AUTHENTICATION_FAILED,
            self::USER_AUTHENTICATED,
            self::ORGANIZATION_DELETED,
            self::ROLE_CREATED,
            self::ROLE_UPDATED,
            self::ROLE_DELETED,
            self::PERMISSION_CREATED,
            self::PERMISSION_UPDATED,
            self::PERMISSION_DELETED,
            self::SUBSCRIBER_CREATED,
            self::ACCESS_REQUEST_CREATED,
        ];
    }
}
