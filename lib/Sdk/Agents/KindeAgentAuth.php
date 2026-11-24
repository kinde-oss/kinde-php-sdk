<?php

namespace Kinde\KindeSDK\Sdk\Agents;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

/**
 * Framework-agnostic entry point for a resource server (API or MCP server) that accepts access
 * tokens issued to AI agents by Kinde. Framework integrations call this and translate the result
 * into their own request and response types.
 */
final class KindeAgentAuth
{
    private KindeAgentAuthOptions $options;
    private ClientInterface $http;
    private TokenVerifier $verifier;

    /**
     * @param TokenVerifier|null  $verifier Overrides the verifier chosen by the verification option
     * @param CacheInterface|null $cache    Shares the JWKS between requests in jwt modes
     */
    public function __construct(
        KindeAgentAuthOptions $options,
        ?ClientInterface $http = null,
        ?TokenVerifier $verifier = null,
        ?CacheInterface $cache = null
    ) {
        $this->options = $options;
        $this->http = $http ?? new Client();
        $this->verifier = $verifier ?? self::createVerifier($options, $this->http, $cache);
    }

    public function getOptions(): KindeAgentAuthOptions
    {
        return $this->options;
    }

    public function verifyToken(string $accessToken): KindeAuthContext
    {
        return $this->verifier->verify($accessToken);
    }

    /**
     * Verifies the token in an Authorization header and, when it is valid, checks it carries
     * every required scope.
     *
     * @param string[] $requiredScopes
     */
    public function verifyAuthorizationHeader(?string $authorizationHeader, array $requiredScopes = []): KindeAuthContext
    {
        $token = self::bearerToken($authorizationHeader);
        if ($token === null) {
            return KindeAuthContext::missingToken();
        }
        return self::requireScopes($this->verifier->verify($token), $requiredScopes);
    }

    /**
     * Extracts the token from "Authorization: Bearer <token>". Tokens in the query string or
     * body are not accepted.
     */
    public static function bearerToken(?string $authorizationHeader): ?string
    {
        if ($authorizationHeader === null || !preg_match('/^Bearer[ ]+(\S+)\s*$/i', trim($authorizationHeader), $match)) {
            return null;
        }
        return $match[1];
    }

    /**
     * @param string[] $requiredScopes
     */
    public static function requireScopes(KindeAuthContext $context, array $requiredScopes): KindeAuthContext
    {
        if (!$context->isAuthenticated()) {
            return $context;
        }
        $missing = array_values(array_diff($requiredScopes, $context->getScopes()));
        return $missing === []
            ? $context
            : KindeAuthContext::insufficientScope($requiredScopes, 'Missing scope: ' . implode(' ', $missing));
    }

    /**
     * Requires every listed Kinde permission. For REST routes only: inside an MCP tool, check
     * hasPermission() and return a tool error instead, since every tool shares one endpoint.
     */
    public static function requirePermissions(KindeAuthContext $context, string ...$permissions): KindeAuthContext
    {
        if (!$context->isAuthenticated()) {
            return $context;
        }
        $missing = array_values(array_diff($permissions, $context->getPermissions()));
        return $missing === []
            ? $context
            : KindeAuthContext::forbidden('Missing permission: ' . implode(' ', $missing));
    }

    /**
     * Revokes an access or refresh token issued for this resource, for example to cut off an
     * agent. Kinde revokes both tokens of the pair, and answers 200 even for unknown tokens.
     *
     * @throws RuntimeException When the request fails or Kinde does not answer 200
     */
    public function revokeToken(string $token, ?string $tokenTypeHint = null): void
    {
        if ($this->options->getClientSecret() === null) {
            throw new RuntimeException('client_secret is required to revoke tokens.');
        }
        $form = ['token' => $token];
        if ($tokenTypeHint !== null) {
            $form['token_type_hint'] = $tokenTypeHint;
        }
        try {
            $status = $this->http->request('POST', $this->options->getRevocationUrl(), [
                'form_params' => $form,
                'headers' => ['Authorization' => IntrospectionTokenVerifier::basicCredentials($this->options)],
                'timeout' => $this->options->getTimeout(),
                'connect_timeout' => $this->options->getTimeout(),
                'allow_redirects' => false,
                'http_errors' => false,
            ])->getStatusCode();
        } catch (GuzzleException $e) {
            throw new RuntimeException('Token revocation request failed: ' . $e->getMessage(), 0, $e);
        }
        if ($status !== 200) {
            throw new RuntimeException("Token revocation returned HTTP {$status}.");
        }
    }

    /**
     * RFC 9728 protected resource metadata, served at every path in
     * KindeAgentAuthOptions::getProtectedResourceMetadataPaths().
     *
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        $metadata = [
            'resource' => $this->options->getResourceUrl(),
            'authorization_servers' => [$this->options->getIssuer()],
            'bearer_methods_supported' => ['header'],
            'scopes_supported' => $this->options->getScopesSupported(),
        ];
        if ($this->options->getResourceName() !== null) {
            $metadata['resource_name'] = $this->options->getResourceName();
        }
        return $metadata;
    }

    /**
     * RFC 8414 metadata for the application's scoped issuer, for clients that look for it at
     * /.well-known/oauth-authorization-server on the resource origin instead of following
     * authorization_servers. Advertises CIMD support so those clients do not fall back to DCR.
     *
     * @return array<string, mixed>
     */
    public function authorizationServerMetadata(): array
    {
        $issuer = $this->options->getIssuer();
        return [
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer . '/authorize',
            'token_endpoint' => $issuer . '/token',
            'registration_endpoint' => $issuer . '/register',
            'introspection_endpoint' => $this->options->getIntrospectionUrl(),
            'revocation_endpoint' => $this->options->getRevocationUrl(),
            'jwks_uri' => $this->options->getJwksUrl(),
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'none'],
            'code_challenge_methods_supported' => ['S256'],
            'scopes_supported' => ['offline', 'profile', 'email'],
            'subject_types_supported' => ['public'],
            'resource_indicators_supported' => true,
            'client_id_metadata_document_supported' => true,
        ];
    }

    /**
     * Headers for both metadata documents. They are public, and browser-based MCP clients
     * fetch them cross-origin.
     *
     * @return array<string, string>
     */
    public static function metadataHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
        ];
    }

    /**
     * Headers answering a CORS preflight (OPTIONS) for the metadata documents.
     *
     * @return array<string, string>
     */
    public static function preflightHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, MCP-Protocol-Version',
            'Access-Control-Max-Age' => '86400',
        ];
    }

    public static function statusCode(KindeAuthContext $context): int
    {
        switch ($context->getFailureKind()) {
            case null:
                return 200;
            case KindeAuthContext::INSUFFICIENT_SCOPE:
            case KindeAuthContext::FORBIDDEN:
                return 403;
            case KindeAuthContext::UNAVAILABLE:
                return 503;
            default:
                return 401;
        }
    }

    /**
     * Headers for a failed request. An outage and a missing permission get no challenge:
     * neither is fixed by signing in again, and challenging would send the agent into a login loop.
     *
     * @return array<string, string>
     */
    public function challengeHeaders(KindeAuthContext $context): array
    {
        $headers = ['Cache-Control' => 'no-store'];
        $kind = $context->getFailureKind();
        if ($kind === null || $kind === KindeAuthContext::UNAVAILABLE || $kind === KindeAuthContext::FORBIDDEN) {
            return $headers;
        }

        $parameters = [];
        if ($kind === KindeAuthContext::INVALID_TOKEN) {
            $parameters[] = self::parameter('error', 'invalid_token');
            $parameters[] = self::parameter('error_description', (string) $context->getFailureReason());
        } elseif ($kind === KindeAuthContext::INSUFFICIENT_SCOPE) {
            $parameters[] = self::parameter('error', 'insufficient_scope');
            if ($context->getRequiredScopes() !== []) {
                $parameters[] = self::parameter('scope', implode(' ', $context->getRequiredScopes()));
            }
        }
        $parameters[] = self::parameter('resource_metadata', $this->options->getProtectedResourceMetadataUrl());

        $headers['WWW-Authenticate'] = 'Bearer ' . implode(', ', $parameters);
        $headers['Access-Control-Expose-Headers'] = 'WWW-Authenticate';
        return $headers;
    }

    /**
     * JSON body for a failed request. Outage detail stays in the logs (getFailureReason()).
     *
     * @return array<string, string>
     */
    public static function errorBody(KindeAuthContext $context): array
    {
        switch ($context->getFailureKind()) {
            case KindeAuthContext::MISSING_TOKEN:
                return ['error' => 'unauthorized', 'error_description' => 'A bearer token is required.'];
            case KindeAuthContext::UNAVAILABLE:
                return ['error' => 'temporarily_unavailable', 'error_description' => 'The token could not be verified right now.'];
            default:
                return ['error' => (string) $context->getFailureKind(), 'error_description' => (string) $context->getFailureReason()];
        }
    }

    private static function createVerifier(KindeAgentAuthOptions $options, ClientInterface $http, ?CacheInterface $cache): TokenVerifier
    {
        switch ($options->getVerification()) {
            case KindeAgentAuthOptions::VERIFY_JWT:
                return new JwtTokenVerifier($options, $http, $cache);
            case KindeAgentAuthOptions::VERIFY_JWT_THEN_INTROSPECTION:
                return new CompositeTokenVerifier(
                    new JwtTokenVerifier($options, $http, $cache),
                    new IntrospectionTokenVerifier($options, $http)
                );
            default:
                return new IntrospectionTokenVerifier($options, $http);
        }
    }

    // Values are quoted and escaped, with control and non-ASCII characters removed, so a stray
    // quote or line break cannot truncate the header and drop the parameters after it.
    private static function parameter(string $name, string $value): string
    {
        $value = trim((string) preg_replace('/[^\x20-\x7E]/', '', (string) preg_replace('/[\r\n\t]+/', ' ', $value)));
        return $name . '="' . addcslashes($value, '"\\') . '"';
    }
}
