<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuthOptions;

/**
 * Stateless OAuth primitives an agent (an MCP client) uses to get a token for a resource
 * protected by Kinde: discovery, client identity (CIMD, pre-registered or dynamic registration),
 * PKCE authorization, code exchange, refresh and revocation. KindeAgentClient composes these
 * with storage; use this class directly to manage state yourself.
 */
final class AgentOAuth
{
    private AgentHttp $http;
    private bool $allowPrivateNetworks;

    /**
     * @param bool $allowPrivateNetworks Allow resource and authorization server metadata on private
     *                                   or local hosts. Off by default: a malicious resource could
     *                                   otherwise point the agent at internal services.
     */
    public function __construct(?ClientInterface $http = null, bool $allowPrivateNetworks = false, float $timeout = 10.0)
    {
        $this->http = new AgentHttp($http ?? new Client(), $timeout);
        $this->allowPrivateNetworks = $allowPrivateNetworks;
    }

    /**
     * Loads and validates the metadata of a Kinde agent application's scoped authorization server.
     *
     * @return array<string, mixed>
     */
    public function discoverKinde(string $domain, string $agentApplicationId): array
    {
        $domain = rtrim(trim($domain), '/');
        $issuer = $domain . '/oauth2/agents/' . rawurlencode($agentApplicationId);
        $url = $domain . '/.well-known/oauth-authorization-server/oauth2/agents/' . rawurlencode($agentApplicationId);
        [$status, $metadata] = $this->http->getJson($url, 'Kinde authorization server metadata');
        if ($status !== 200 || $metadata === null) {
            throw new AgentAuthException(
                'metadata_unavailable',
                "Could not load {$url} (HTTP {$status}). Check the domain and the agent application's client ID.",
                $status
            );
        }
        return self::validateAuthorizationServer($metadata, $issuer);
    }

    /**
     * Discovers how to get a token for a resource from its 401 challenge (RFC 9728): fetches the
     * protected resource metadata, checks it describes $resourceUrl, then loads the
     * authorization server it names.
     *
     * @param string|null $wwwAuthenticate The WWW-Authenticate header of the resource's 401, if any
     * @param string|null $preferredIssuer Used when the resource lists it; otherwise the first server
     * @return array{resource: string, resource_metadata_url: string, resource_metadata: array, authorization_server: array, scope: string|null}
     */
    public function discoverFromResource(string $resourceUrl, ?string $wwwAuthenticate = null, ?string $preferredIssuer = null): array
    {
        AgentHttp::assertAllowedUrl($resourceUrl, 'resourceUrl', true);
        $challenge = self::parseWwwAuthenticate($wwwAuthenticate);
        $parts = parse_url($resourceUrl);
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $path = ($parts['path'] ?? '/') === '/' ? '' : $parts['path'];

        $candidates = isset($challenge['resource_metadata'])
            ? [$challenge['resource_metadata']]
            : array_values(array_unique([
                $origin . KindeAgentAuthOptions::WELL_KNOWN_PROTECTED_RESOURCE . $path,
                $origin . KindeAgentAuthOptions::WELL_KNOWN_PROTECTED_RESOURCE,
            ]));

        $resourceMetadata = null;
        $metadataUrl = $candidates[0];
        $failure = null;
        foreach ($candidates as $candidate) {
            try {
                [$status, $body] = $this->http->getJson($candidate, 'resource_metadata', $this->allowPrivateNetworks);
                if ($status === 200 && isset($body['resource']) && is_string($body['resource'])) {
                    $resourceMetadata = $body;
                    $metadataUrl = $candidate;
                    break;
                }
            } catch (AgentAuthException $e) {
                if ($e->getErrorCode() === 'private_network_blocked' || $e->getErrorCode() === 'insecure_url') {
                    throw $e;
                }
                $failure = $e;
            }
        }
        if ($resourceMetadata === null) {
            throw new AgentAuthException('metadata_unavailable', "Unable to load protected resource metadata for {$resourceUrl}.", null, $failure);
        }

        if (!self::resourceCovers($resourceMetadata['resource'], $resourceUrl)) {
            throw new AgentAuthException(
                'resource_mismatch',
                "Resource metadata describes {$resourceMetadata['resource']}, not {$resourceUrl}."
            );
        }
        $servers = self::stringList($resourceMetadata['authorization_servers'] ?? null);
        if ($servers === []) {
            throw new AgentAuthException('no_authorization_server', 'Resource metadata lists no authorization servers.');
        }
        $issuer = $preferredIssuer !== null && in_array($preferredIssuer, $servers, true) ? $preferredIssuer : $servers[0];

        $scopes = self::withoutOpenId(self::normalizeScopes($challenge['scope'] ?? ''));
        if ($scopes === []) {
            $scopes = self::withoutOpenId(self::normalizeScopes(self::stringList($resourceMetadata['scopes_supported'] ?? null)));
        }

        return [
            'resource' => $resourceMetadata['resource'],
            'resource_metadata_url' => $metadataUrl,
            'resource_metadata' => $resourceMetadata,
            'authorization_server' => $this->fetchAuthorizationServerMetadata($issuer),
            'scope' => $scopes === [] ? null : implode(' ', $scopes),
        ];
    }

    /**
     * Loads RFC 8414 metadata for any issuer, trying the oauth-authorization-server and
     * openid-configuration locations in turn.
     *
     * @return array<string, mixed>
     */
    public function fetchAuthorizationServerMetadata(string $issuer): array
    {
        AgentHttp::assertAllowedUrl($issuer, 'authorization server issuer', $this->allowPrivateNetworks);
        $parts = parse_url($issuer);
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $path = rtrim($parts['path'] ?? '', '/');
        $candidates = array_values(array_unique([
            $origin . '/.well-known/oauth-authorization-server' . $path,
            $origin . '/.well-known/openid-configuration' . $path,
            rtrim($issuer, '/') . '/.well-known/openid-configuration',
        ]));

        $failure = null;
        foreach ($candidates as $candidate) {
            try {
                [$status, $metadata] = $this->http->getJson($candidate, 'Authorization server metadata', $this->allowPrivateNetworks);
                if ($status === 200 && $metadata !== null) {
                    return self::validateAuthorizationServer($metadata, $issuer);
                }
            } catch (AgentAuthException $e) {
                if ($e->getErrorCode() === 'issuer_mismatch') {
                    throw $e;
                }
                $failure = $e;
            }
        }
        throw new AgentAuthException('metadata_unavailable', "Unable to load authorization server metadata for {$issuer}.", null, $failure);
    }

    /**
     * Picks how the agent identifies itself, in order of preference:
     *  1. CIMD, when the server supports it and $cimdClientId is set;
     *  2. a pre-registered client;
     *  3. dynamic client registration (RFC 7591), when $registration is given.
     *
     * @param array<string, mixed>                                       $authorizationServer
     * @param array{client_id: string, client_secret?: string|null}|null $preRegistered
     * @param array{client_name: string, redirect_uris: string[]}|null   $registration
     * @return array{method: string, client_id: string, client_secret: string|null, client_secret_expires_at?: int}
     */
    public function resolveClientIdentity(
        array $authorizationServer,
        ?string $cimdClientId = null,
        ?array $preRegistered = null,
        ?array $registration = null
    ): array {
        if ($cimdClientId !== null && $cimdClientId !== '' && ($authorizationServer['client_id_metadata_document_supported'] ?? false) === true) {
            ClientMetadataDocument::assertValidClientId($cimdClientId);
            return ['method' => 'cimd', 'client_id' => $cimdClientId, 'client_secret' => null];
        }
        if (!empty($preRegistered['client_id'])) {
            return [
                'method' => 'preregistered',
                'client_id' => (string) $preRegistered['client_id'],
                'client_secret' => isset($preRegistered['client_secret']) ? (string) $preRegistered['client_secret'] : null,
            ];
        }
        if ($registration !== null && !empty($authorizationServer['registration_endpoint'])) {
            return $this->register(
                (string) $authorizationServer['registration_endpoint'],
                $registration['client_name'],
                $registration['redirect_uris']
            );
        }
        throw new AgentAuthException(
            'no_client_identity',
            'No way to identify this agent: the server does not support Client ID Metadata Documents '
            . 'for the given client ID, no pre-registered client was configured, and dynamic client '
            . 'registration is not available.'
        );
    }

    /**
     * Dynamic client registration (RFC 7591). MCP has deprecated it in favour of CIMD; use it only
     * for agents that cannot host a metadata document. Store the result and reuse it.
     *
     * @param string[] $redirectUris
     * @return array{method: string, client_id: string, client_secret: string|null, client_secret_expires_at: int}
     */
    public function register(string $registrationEndpoint, string $clientName, array $redirectUris): array
    {
        if ($redirectUris === []) {
            throw new AgentAuthException('invalid_client_metadata', 'At least one redirect_uri is required.');
        }
        foreach ($redirectUris as $redirectUri) {
            ClientMetadataDocument::assertValidRedirectUri($redirectUri);
        }

        [$status, $body] = $this->http->postJson($registrationEndpoint, [
            'client_name' => $clientName,
            'redirect_uris' => array_values($redirectUris),
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
            // Spec-correct for a public client. Kinde still issues a secret for client_secret_post,
            // and whatever comes back is used.
            'token_endpoint_auth_method' => 'none',
        ], 'Dynamic client registration');

        if ($status < 200 || $status >= 300) {
            throw self::oauthError($status, $body, 'Dynamic client registration');
        }
        if (!isset($body['client_id']) || !is_string($body['client_id']) || $body['client_id'] === '') {
            throw new AgentAuthException('invalid_response', 'Registration response is missing client_id.');
        }
        return [
            'method' => 'dcr',
            'client_id' => $body['client_id'],
            'client_secret' => isset($body['client_secret']) ? (string) $body['client_secret'] : null,
            'client_secret_expires_at' => (int) ($body['client_secret_expires_at'] ?? 0),
        ];
    }

    /**
     * Builds the authorization URL with PKCE (S256) and a random state. "offline" is always
     * requested so the agent gets a refresh token; openid is refused because agent tokens are
     * not ID tokens.
     *
     * @param array<string, mixed> $authorizationServer
     * @param string[]|string      $scopes
     */
    public function createAuthorizationRequest(
        array $authorizationServer,
        string $clientId,
        ?string $clientSecret,
        string $redirectUri,
        $scopes = [],
        ?string $resource = null
    ): AuthorizationRequest {
        ClientMetadataDocument::assertValidRedirectUri($redirectUri);
        $scopes = self::normalizeScopes($scopes);
        self::assertNoOpenId($scopes);
        if (!in_array(KindeAgentAuthOptions::OFFLINE_SCOPE, $scopes, true)) {
            array_unshift($scopes, KindeAgentAuthOptions::OFFLINE_SCOPE);
        }

        $verifier = self::randomToken();
        $state = self::randomToken();
        $query = [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ];
        if ($resource !== null && $resource !== '') {
            $query['resource'] = $resource;
        }

        $endpoint = (string) $authorizationServer['authorization_endpoint'];
        $url = $endpoint . (strpos($endpoint, '?') === false ? '?' : '&') . http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return new AuthorizationRequest(
            $url,
            $state,
            $verifier,
            $redirectUri,
            $clientId,
            $clientSecret,
            (string) $authorizationServer['issuer'],
            (string) $authorizationServer['token_endpoint'],
            $resource,
            ($authorizationServer['authorization_response_iss_parameter_supported'] ?? false) === true
        );
    }

    /**
     * Validates the redirect back from Kinde and returns the authorization code.
     *
     * @param array<string, mixed> $params The callback's query parameters
     */
    public static function parseCallback(AuthorizationRequest $request, array $params): string
    {
        $state = $params['state'] ?? null;
        if (!is_string($state) || !hash_equals($request->state, $state)) {
            throw new AgentAuthException('invalid_state', 'The callback state does not match the authorization request.');
        }
        // RFC 9207: a callback naming a different issuer is a mix-up attack.
        $iss = $params['iss'] ?? null;
        if ($iss !== null && $iss !== $request->issuer) {
            throw new AgentAuthException('issuer_mismatch', 'The callback came from a different issuer.');
        }
        if ($iss === null && $request->issParameterRequired) {
            throw new AgentAuthException('issuer_mismatch', 'The callback is missing the iss parameter the server promised.');
        }
        if (isset($params['error'])) {
            $description = isset($params['error_description']) ? (string) $params['error_description'] : (string) $params['error'];
            throw new AgentAuthException((string) $params['error'], $description);
        }
        $code = $params['code'] ?? null;
        if (!is_string($code) || $code === '') {
            throw new AgentAuthException('invalid_request', 'The callback has no authorization code.');
        }
        return $code;
    }

    public function exchangeCode(AuthorizationRequest $request, string $code): TokenSet
    {
        $form = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'code_verifier' => $request->codeVerifier,
            'redirect_uri' => $request->redirectUri,
        ];
        if ($request->resource !== null) {
            $form['resource'] = $request->resource;
        }
        return $this->tokenRequest($request->tokenEndpoint, $form, $request->clientId, $request->clientSecret, null);
    }

    /**
     * @param string[]|string $scopes Optional narrower scopes
     */
    public function refresh(string $tokenEndpoint, TokenSet $tokens, ?string $resource = null, $scopes = []): TokenSet
    {
        if ($tokens->refreshToken === null) {
            throw new AgentAuthException('invalid_grant', 'There is no refresh token. Ask the user to authorize again.');
        }
        $form = ['grant_type' => 'refresh_token', 'refresh_token' => $tokens->refreshToken];
        if ($resource !== null && $resource !== '') {
            $form['resource'] = $resource;
        }
        $scopes = self::normalizeScopes($scopes);
        if ($scopes !== []) {
            self::assertNoOpenId($scopes);
            $form['scope'] = implode(' ', $scopes);
        }
        return $this->tokenRequest($tokenEndpoint, $form, $tokens->clientId, $tokens->clientSecret, $tokens->refreshToken);
    }

    /**
     * Revokes a token (RFC 7009) with the client it was issued to.
     *
     * @param array<string, mixed> $authorizationServer
     */
    public function revoke(array $authorizationServer, TokenSet $tokens, bool $refreshToken = true): void
    {
        $endpoint = $authorizationServer['revocation_endpoint'] ?? null;
        if (!is_string($endpoint) || $endpoint === '') {
            throw new AgentAuthException('unsupported_operation', 'The authorization server does not advertise a revocation_endpoint.');
        }
        $useRefresh = $refreshToken && $tokens->refreshToken !== null;
        $form = [
            'token' => $useRefresh ? (string) $tokens->refreshToken : $tokens->accessToken,
            'token_type_hint' => $useRefresh ? 'refresh_token' : 'access_token',
            'client_id' => $tokens->clientId,
        ];
        if ($tokens->clientSecret !== null) {
            $form['client_secret'] = $tokens->clientSecret;
        }
        [$status, $body] = $this->http->postForm($endpoint, $form, 'Token revocation');
        if ($status < 200 || $status >= 300) {
            throw self::oauthError($status, $body, 'Token revocation');
        }
    }

    /**
     * Parses the Bearer challenge in a WWW-Authenticate header (RFC 6750 / RFC 9728).
     *
     * @return array<string, string>|null Lower-cased parameter names to values, or null without a Bearer challenge
     */
    public static function parseWwwAuthenticate(?string $header): ?array
    {
        if ($header === null || trim($header) === '') {
            return null;
        }
        $challenges = [];
        $length = strlen($header);
        $i = 0;
        while ($i < $length) {
            while ($i < $length && strpos(" \t\r\n,", $header[$i]) !== false) {
                $i++;
            }
            if ($i >= $length) {
                break;
            }
            $start = $i;
            while ($i < $length && strpos(" \t\r\n,=", $header[$i]) === false) {
                $i++;
            }
            $token = substr($header, $start, $i - $start);
            while ($i < $length && strpos(" \t\r\n", $header[$i]) !== false) {
                $i++;
            }
            if ($i >= $length || $header[$i] !== '=') {
                $challenges[] = ['scheme' => strtolower($token), 'params' => []];
                continue;
            }
            $i++;
            while ($i < $length && strpos(" \t\r\n", $header[$i]) !== false) {
                $i++;
            }
            $value = '';
            if ($i < $length && $header[$i] === '"') {
                $i++;
                while ($i < $length && $header[$i] !== '"') {
                    if ($header[$i] === '\\' && $i + 1 < $length) {
                        $i++;
                    }
                    $value .= $header[$i];
                    $i++;
                }
                $i++;
            } else {
                $start = $i;
                while ($i < $length && strpos(" \t\r\n,", $header[$i]) === false) {
                    $i++;
                }
                $value = substr($header, $start, $i - $start);
            }
            $current = count($challenges) - 1;
            $name = strtolower($token);
            if ($current >= 0 && $name !== '' && !array_key_exists($name, $challenges[$current]['params'])) {
                $challenges[$current]['params'][$name] = $value;
            }
        }
        foreach ($challenges as $challenge) {
            if ($challenge['scheme'] === 'bearer') {
                return $challenge['params'];
            }
        }
        return null;
    }

    /**
     * @param string[]|string|null $scopes
     * @return string[]
     */
    public static function normalizeScopes($scopes): array
    {
        $parts = [];
        foreach ((array) $scopes as $scope) {
            foreach (preg_split('/\s+/', (string) $scope, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                $parts[] = $part;
            }
        }
        return array_values(array_unique($parts));
    }

    /**
     * @param string[] $scopes
     */
    public static function assertNoOpenId(array $scopes): void
    {
        if (in_array('openid', $scopes, true)) {
            throw new AgentAuthException(
                'invalid_scope',
                'Agent tokens are access tokens only; the openid scope is not supported.'
            );
        }
    }

    /**
     * @param array<string, string> $form
     */
    private function tokenRequest(string $tokenEndpoint, array $form, string $clientId, ?string $clientSecret, ?string $previousRefreshToken): TokenSet
    {
        $form['client_id'] = $clientId;
        if ($clientSecret !== null) {
            $form['client_secret'] = $clientSecret;
        }

        // expires_in counts from when Kinde issued the token, so stamp the time before the call.
        $requestedAt = time();
        [$status, $body] = $this->http->postForm($tokenEndpoint, $form, 'Token request');
        if ($status < 200 || $status >= 300) {
            throw self::oauthError($status, $body, 'Token request');
        }
        if (!isset($body['access_token']) || !is_string($body['access_token']) || $body['access_token'] === '') {
            throw new AgentAuthException('invalid_response', 'Token response is missing access_token.');
        }
        $tokenType = isset($body['token_type']) ? (string) $body['token_type'] : 'Bearer';
        if (strcasecmp($tokenType, 'Bearer') !== 0) {
            throw new AgentAuthException('invalid_response', "Unsupported token_type {$tokenType}.");
        }
        $expiresIn = isset($body['expires_in']) && is_numeric($body['expires_in']) ? (int) $body['expires_in'] : 3600;

        return new TokenSet(
            $body['access_token'],
            isset($body['refresh_token']) && is_string($body['refresh_token']) ? $body['refresh_token'] : $previousRefreshToken,
            $requestedAt + $expiresIn,
            'Bearer',
            isset($body['scope']) ? (string) $body['scope'] : null,
            $clientId,
            $clientSecret
        );
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private static function validateAuthorizationServer(array $metadata, string $issuer): array
    {
        if (($metadata['issuer'] ?? null) !== $issuer) {
            throw new AgentAuthException(
                'issuer_mismatch',
                'Metadata issuer ' . json_encode($metadata['issuer'] ?? null) . " does not match {$issuer}."
            );
        }
        foreach (['authorization_endpoint' => true, 'token_endpoint' => true, 'registration_endpoint' => false, 'revocation_endpoint' => false] as $field => $required) {
            $value = $metadata[$field] ?? null;
            if ($value === null && !$required) {
                continue;
            }
            if (!is_string($value) || strtolower((string) parse_url($value, PHP_URL_SCHEME)) !== 'https') {
                throw new AgentAuthException('invalid_response', "Authorization server metadata {$field} must be an https URL.");
            }
        }
        if (!in_array('S256', self::stringList($metadata['code_challenge_methods_supported'] ?? null), true)) {
            throw new AgentAuthException('invalid_response', 'The authorization server does not support PKCE S256.');
        }
        return $metadata;
    }

    /**
     * @param mixed $body
     */
    private static function oauthError(int $status, $body, string $label): AgentAuthException
    {
        $error = is_array($body) && isset($body['error']) && is_string($body['error']) ? $body['error'] : 'http_error';
        $description = is_array($body) && isset($body['error_description']) ? (string) $body['error_description'] : null;
        return new AgentAuthException($error, "{$label} failed (HTTP {$status}): " . ($description ?? $error), $status);
    }

    /**
     * Whether the advertised resource covers the requested one: same origin, and the requested
     * path equals the advertised path or sits under it. Some servers advertise a parent path.
     */
    private static function resourceCovers(string $advertised, string $requested): bool
    {
        $split = static function (string $url): ?array {
            $parts = parse_url($url);
            if (!isset($parts['scheme'], $parts['host'])) {
                return null;
            }
            $origin = strtolower($parts['scheme'] . '://' . $parts['host']) . (isset($parts['port']) ? ':' . $parts['port'] : '');
            return [$origin, (string) preg_replace('#/$#', '', $parts['path'] ?? '')];
        };
        $a = $split($advertised);
        $r = $split($requested);
        if ($a === null || $r === null || $a[0] !== $r[0]) {
            return false;
        }
        return $a[1] === '' || $r[1] === $a[1] || strpos($r[1], $a[1] . '/') === 0;
    }

    /**
     * @param string[] $scopes
     * @return string[]
     */
    private static function withoutOpenId(array $scopes): array
    {
        return array_values(array_diff($scopes, ['openid']));
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private static function stringList($value): array
    {
        return array_values(array_filter((array) $value, static function ($item): bool {
            return is_string($item) && $item !== '';
        }));
    }

    private static function randomToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
