<?php

namespace Kinde\KindeSDK\Webhooks;

use Kinde\KindeSDK\Sdk\Storage\Storage;
use Kinde\KindeSDK\Sdk\Utils\Utils;

/**
 * Decode and validate webhook JWTs using the SDK's JWKS handling.
 */
final class WebhookDecoder
{
    /**
     * Decode a webhook JWT and return its payload as an array.
     *
     * Returns null when the token is missing, domain is missing, signature
     * validation fails, or the payload cannot be decoded.
     *
     * @param string|null $token   The webhook JWT.
     * @param string|null $domain  The Kinde domain (e.g. https://your-subdomain.kinde.com).
     *
     * @return array|null
     */
    public static function decodeWebhook(?string $token, ?string $domain): ?array
    {
        if (empty($token) || empty($domain)) {
            return null;
        }

        // Normalise and set JWKS URL so Utils::parseJWT can verify signature.
        $normalizedDomain = rtrim($domain, '/');
        $jwksUrl = $normalizedDomain . '/.well-known/jwks.json';

        try {
            Storage::getInstance()->setJwksUrl($jwksUrl);
            $payload = Utils::parseJWT($token);

            return is_array($payload) ? $payload : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
