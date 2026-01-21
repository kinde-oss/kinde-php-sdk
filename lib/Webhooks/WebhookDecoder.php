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

        // Basic domain validation: require HTTPS scheme and host, strip path/query.
        $normalizedDomain = rtrim($domain, '/');
        $parts = parse_url($normalizedDomain);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host']) || strtolower($parts['scheme']) !== 'https') {
            return null;
        }
        $normalizedDomain = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
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
