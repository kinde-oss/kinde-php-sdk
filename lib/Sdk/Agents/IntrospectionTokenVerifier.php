<?php

namespace Kinde\KindeSDK\Sdk\Agents;

use Firebase\JWT\JWT;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Verifies tokens with Kinde's introspection endpoint (RFC 7662). Authoritative: a revoked token
 * is rejected immediately. Requires the agent application's client secret.
 */
final class IntrospectionTokenVerifier implements TokenVerifier
{
    private const CLIENT_AUTH_ERRORS = ['invalid_client', 'unauthorized_client', 'request_unauthorized'];

    private KindeAgentAuthOptions $options;
    private ClientInterface $http;

    public function __construct(KindeAgentAuthOptions $options, ClientInterface $http)
    {
        if ($options->getClientSecret() === null) {
            throw new \InvalidArgumentException(
                'client_secret is required for introspection verification. Set it, or use jwt verification.'
            );
        }
        $this->options = $options;
        $this->http = $http;
    }

    public function verify(string $accessToken): KindeAuthContext
    {
        $accessToken = trim($accessToken);
        if ($accessToken === '') {
            return KindeAuthContext::missingToken();
        }

        // Kinde reports active:true for refresh tokens too, and the response carries no token
        // type, so the JWT shape is the only way to keep a refresh token from passing as a bearer.
        if (!self::looksLikeJwt($accessToken)) {
            return KindeAuthContext::invalid('Only a JWT access token is accepted.');
        }

        try {
            $response = $this->http->request('POST', $this->options->getIntrospectionUrl(), [
                'form_params' => ['token' => $accessToken, 'token_type_hint' => 'access_token'],
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => self::basicCredentials($this->options),
                ],
                'timeout' => $this->options->getTimeout(),
                'connect_timeout' => $this->options->getTimeout(),
                'allow_redirects' => false,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            return KindeAuthContext::unavailable('Introspection request failed: ' . $e->getMessage());
        }

        $status = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true);

        // Kinde answers 401/403 both for an inactive token and for bad client credentials; only
        // the error code tells them apart.
        if ($status === 401 || $status === 403) {
            $error = is_array($body) ? ($body['error'] ?? null) : null;
            return in_array($error, self::CLIENT_AUTH_ERRORS, true)
                ? KindeAuthContext::unavailable(
                    "Kinde rejected this resource server's credentials ({$error}). Check the agent client_id and client_secret."
                )
                : KindeAuthContext::invalid('The token is not active (expired or revoked).');
        }

        if ($status < 200 || $status >= 300) {
            return KindeAuthContext::unavailable("Introspection returned HTTP {$status}.");
        }

        if (!is_array($body)) {
            return KindeAuthContext::unavailable('Introspection returned a response that is not a JSON object.');
        }

        if (($body['active'] ?? false) !== true) {
            return KindeAuthContext::invalid('The token is not active (expired or revoked).');
        }

        $exp = $body['exp'] ?? null;
        if (is_numeric($exp) && (int) $exp <= time() - $this->options->getClockTolerance()) {
            return KindeAuthContext::invalid('The token has expired.');
        }

        // Introspection omits Kinde's permissions, roles and org_code. Kinde has just confirmed the
        // token, so its payload can be read without a signature check; the introspection fields
        // win where both are present.
        $context = KindeAuthContext::fromClaims(array_merge(self::payload($accessToken), $body), $accessToken);
        if (!in_array($this->options->getResourceUrl(), $context->getAudiences(), true)) {
            return KindeAuthContext::invalid('The token was not issued for this resource.');
        }

        return $context;
    }

    /**
     * @return array<string, mixed>
     */
    private static function payload(string $token): array
    {
        $payload = json_decode((string) JWT::urlsafeB64Decode(explode('.', $token)[1]), true);
        return is_array($payload) ? $payload : [];
    }

    /**
     * @internal The application's client_secret_basic header, shared with admin revocation.
     */
    public static function basicCredentials(KindeAgentAuthOptions $options): string
    {
        // Each part is URL-encoded first so a ':' in the secret cannot split the pair.
        return 'Basic ' . base64_encode(rawurlencode($options->getClientId()) . ':' . rawurlencode((string) $options->getClientSecret()));
    }

    private static function looksLikeJwt(string $token): bool
    {
        return (bool) preg_match('/^[^.\s]+\.[^.\s]+\.[^.\s]+$/', $token);
    }
}
