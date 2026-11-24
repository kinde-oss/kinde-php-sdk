<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

/**
 * Client ID Metadata Document (CIMD) support. An agent publishes the document built here at an
 * https URL on its own domain, and that URL is its client_id: Kinde fetches it instead of the
 * agent registering. Serve it directly (no redirects) with RESPONSE_HEADERS, from a public host
 * listed in the Kinde application's Trusted agent hosts.
 */
final class ClientMetadataDocument
{
    /** Kinde clamps the cache lifetime to between 5 minutes and 24 hours. */
    public const RESPONSE_HEADERS = ['Content-Type' => 'application/json', 'Cache-Control' => 'public, max-age=3600'];

    private const FORBIDDEN_REDIRECT_SCHEMES = ['javascript', 'data', 'vbscript', 'file', 'blob'];
    private const LOOPBACK_HOSTS = ['localhost', '127.0.0.1', '[::1]'];
    private const OPTIONAL_FIELDS = [
        'client_uri', 'logo_uri', 'tos_uri', 'policy_uri', 'contacts', 'software_id', 'software_version',
    ];

    /**
     * Builds the document to publish at $clientId.
     *
     * @param string[]             $redirectUris
     * @param string[]             $scopes   Optional default scopes; openid is not allowed
     * @param array<string, mixed> $optional Any of client_uri, logo_uri, tos_uri, policy_uri,
     *                                       contacts, software_id, software_version
     * @return array<string, mixed>
     */
    public static function build(string $clientId, string $clientName, array $redirectUris, array $scopes = [], array $optional = []): array
    {
        self::assertValidClientId($clientId);
        if (trim($clientName) === '') {
            throw self::invalid('client_name is required.');
        }
        if ($redirectUris === []) {
            throw self::invalid('At least one redirect_uri is required.');
        }
        $host = strtolower((string) parse_url($clientId, PHP_URL_HOST));
        foreach ($redirectUris as $redirectUri) {
            self::assertValidRedirectUri($redirectUri);
            $parts = parse_url($redirectUri);
            $scheme = strtolower($parts['scheme']);
            $loopback = $scheme === 'http';
            if (!$loopback && ($scheme !== 'https' || strtolower($parts['host'] ?? '') !== $host)) {
                throw self::invalid(
                    "Kinde ignores {$redirectUri}: a redirect_uri must be https on {$host}, or http loopback."
                );
            }
        }
        $scopes = AgentOAuth::normalizeScopes($scopes);
        AgentOAuth::assertNoOpenId($scopes);

        $unknown = array_diff(array_keys($optional), self::OPTIONAL_FIELDS);
        if ($unknown !== []) {
            throw self::invalid('Unsupported client metadata: ' . implode(', ', $unknown) . '.');
        }

        $document = [
            'client_id' => $clientId,
            'client_name' => $clientName,
            'redirect_uris' => array_values(array_unique($redirectUris)),
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
            // A document anyone can read cannot hold a shared secret.
            'token_endpoint_auth_method' => 'none',
        ];
        if ($scopes !== []) {
            $document['scope'] = implode(' ', $scopes);
        }
        foreach ($optional as $field => $value) {
            if ($value !== null && $value !== '' && $value !== []) {
                $document[$field] = $value;
            }
        }
        return $document;
    }

    /**
     * A CIMD client_id must be an https URL with a path, and no fragment, credentials, dot
     * segments, whitespace or control characters.
     */
    public static function assertValidClientId(string $clientId): void
    {
        if (preg_match('/[\x00-\x20\x7F\\\\]/', $clientId)) {
            throw self::invalid('client_id contains invalid characters.');
        }
        $parts = parse_url($clientId);
        if ($parts === false || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            throw self::invalid('client_id must be an absolute https URL.');
        }
        $path = $parts['path'] ?? '';
        if ($path === '' || $path === '/') {
            throw self::invalid('client_id must contain a path.');
        }
        if (strpos($clientId, '#') !== false || isset($parts['query'])) {
            throw self::invalid('client_id must not contain a query or fragment.');
        }
        if (isset($parts['user']) || isset($parts['pass']) || strpos(explode('/', substr($clientId, 8))[0], '@') !== false) {
            throw self::invalid('client_id must not contain credentials.');
        }
        foreach (explode('/', $path) as $segment) {
            if (preg_match('/^(\.|%2e){1,2}$/i', $segment)) {
                throw self::invalid('client_id must not contain dot path segments.');
            }
        }
    }

    public static function assertValidRedirectUri(string $redirectUri): void
    {
        $parts = parse_url($redirectUri);
        if ($parts === false || empty($parts['scheme'])) {
            throw self::invalid("redirect_uri is not a valid URL: {$redirectUri}");
        }
        if (strpos($redirectUri, '#') !== false) {
            throw self::invalid('redirect_uri must not contain a fragment.');
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw self::invalid('redirect_uri must not contain credentials.');
        }
        $scheme = strtolower($parts['scheme']);
        if (in_array($scheme, self::FORBIDDEN_REDIRECT_SCHEMES, true)) {
            throw self::invalid("redirect_uri scheme not allowed: {$scheme}");
        }
        if ($scheme === 'http' && !in_array(strtolower($parts['host'] ?? ''), self::LOOPBACK_HOSTS, true)) {
            throw self::invalid('http redirect_uri is only allowed for loopback hosts.');
        }
    }

    private static function invalid(string $message): AgentAuthException
    {
        return new AgentAuthException('invalid_client_metadata', $message);
    }
}
