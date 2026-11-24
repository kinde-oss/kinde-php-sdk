<?php

namespace Kinde\KindeSDK\Sdk\Agents;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\CacheInterface;

/**
 * Verifies RS256 access tokens locally against Kinde's JWKS. Fast, but cannot see revocation;
 * a revoked token stays valid until it expires.
 *
 * Checks signature, alg, iss (the bare Kinde domain), aud (must contain the resource URL), exp
 * and nbf. Unlike Utils::parseJWT, issuer and audience are always enforced.
 */
final class JwtTokenVerifier implements TokenVerifier
{
    private const ALGORITHM = 'RS256';
    private const REFRESH_INTERVAL = 60;
    private const CACHE_TTL = 3600;

    private KindeAgentAuthOptions $options;
    private ClientInterface $http;
    private ?CacheInterface $cache;
    /** @var array<string, mixed>|null */
    private ?array $jwks = null;
    private int $lastFetch = 0;

    public function __construct(KindeAgentAuthOptions $options, ClientInterface $http, ?CacheInterface $cache = null)
    {
        $this->options = $options;
        $this->http = $http;
        $this->cache = $cache;
    }

    public function verify(string $accessToken): KindeAuthContext
    {
        $accessToken = trim($accessToken);
        if ($accessToken === '') {
            return KindeAuthContext::missingToken();
        }

        $segments = explode('.', $accessToken);
        $header = count($segments) === 3 ? json_decode((string) JWT::urlsafeB64Decode($segments[0]), true) : null;
        if (!is_array($header)) {
            return KindeAuthContext::invalid('The token is not a well-formed JWT.');
        }
        if (($header['alg'] ?? null) !== self::ALGORITHM) {
            return KindeAuthContext::invalid('The token must be signed with RS256.');
        }
        $kid = $header['kid'] ?? null;
        if (!is_string($kid) || $kid === '') {
            return KindeAuthContext::invalid('The token header has no key ID.');
        }

        $jwks = $this->keySet(false);
        if (!self::hasKey($jwks, $kid)) {
            // Kinde may have rotated keys since the set was cached.
            $jwks = $this->keySet(true);
        }
        if ($jwks === null) {
            return KindeAuthContext::unavailable('Could not fetch the JSON Web Key Set from Kinde.');
        }
        if (!self::hasKey($jwks, $kid)) {
            return KindeAuthContext::invalid('The token was signed with an unknown key.');
        }

        $previousLeeway = JWT::$leeway;
        JWT::$leeway = $this->options->getClockTolerance();
        try {
            $claims = json_decode(json_encode(JWT::decode($accessToken, JWK::parseKeySet($jwks, self::ALGORITHM))), true);
        } catch (\UnexpectedValueException | \LogicException | \DomainException $e) {
            return KindeAuthContext::invalid('The token failed verification: ' . $e->getMessage());
        } finally {
            JWT::$leeway = $previousLeeway;
        }

        if (!is_array($claims) || !isset($claims['exp'])) {
            return KindeAuthContext::invalid('The token has no expiry.');
        }
        $issuer = $claims['iss'] ?? null;
        if (!is_string($issuer) || preg_replace('#/$#', '', $issuer) !== $this->options->getExpectedTokenIssuer()) {
            return KindeAuthContext::invalid('The token was not issued by the configured Kinde domain.');
        }

        $context = KindeAuthContext::fromClaims($claims, $accessToken);
        if (!in_array($this->options->getResourceUrl(), $context->getAudiences(), true)) {
            return KindeAuthContext::invalid('The token was not issued for this resource.');
        }

        return $context;
    }

    /**
     * Returns the cached key set, fetching it when absent or when $refresh is set. Refetches are
     * limited to one per REFRESH_INTERVAL so tokens with random kids cannot hammer Kinde; a
     * failed refetch keeps the previous keys.
     *
     * @return array<string, mixed>|null
     */
    private function keySet(bool $refresh): ?array
    {
        $cacheKey = 'kinde_agent_jwks_' . sha1($this->options->getJwksUrl());
        if ($this->jwks === null && $this->cache !== null) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached) && isset($cached['keys'], $cached['fetched_at'])) {
                $this->jwks = $cached['keys'];
                $this->lastFetch = (int) $cached['fetched_at'];
            }
        }

        if ($this->jwks !== null && (!$refresh || time() - $this->lastFetch < self::REFRESH_INTERVAL)) {
            return $this->jwks;
        }

        try {
            $response = $this->http->request('GET', $this->options->getJwksUrl(), [
                'headers' => ['Accept' => 'application/json'],
                'timeout' => $this->options->getTimeout(),
                'connect_timeout' => $this->options->getTimeout(),
                'http_errors' => false,
            ]);
            $body = $response->getStatusCode() === 200 ? json_decode((string) $response->getBody(), true) : null;
        } catch (GuzzleException $e) {
            $body = null;
        }

        $this->lastFetch = time();
        if (is_array($body) && isset($body['keys']) && is_array($body['keys'])) {
            $this->jwks = $body;
        }
        if ($this->cache !== null && $this->jwks !== null) {
            $this->cache->set($cacheKey, ['keys' => $this->jwks, 'fetched_at' => $this->lastFetch], self::CACHE_TTL);
        }

        return $this->jwks;
    }

    private static function hasKey(?array $jwks, string $kid): bool
    {
        foreach ($jwks['keys'] ?? [] as $key) {
            if (is_array($key) && ($key['kid'] ?? null) === $kid) {
                return true;
            }
        }
        return false;
    }
}
