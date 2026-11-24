<?php

namespace Kinde\KindeSDK\Sdk\Agents;

use InvalidArgumentException;

/**
 * Configuration for a resource server (API or MCP server) that accepts access tokens issued to
 * AI agents by a Kinde "AI agents and MCP" application.
 *
 * Only the domain, the agent application's client ID and the resource URL are supplied; every
 * endpoint is derived from them.
 */
final class KindeAgentAuthOptions
{
    public const VERIFY_INTROSPECTION = 'introspection';
    public const VERIFY_JWT = 'jwt';
    public const VERIFY_JWT_THEN_INTROSPECTION = 'jwt+introspection';

    public const OFFLINE_SCOPE = 'offline';

    public const WELL_KNOWN_PROTECTED_RESOURCE = '/.well-known/oauth-protected-resource';

    private string $domain;
    private string $clientId;
    private ?string $clientSecret;
    private string $resourceUrl;
    private string $verification;
    /** @var string[] */
    private array $scopesSupported;
    private ?string $resourceName;
    private int $clockTolerance;
    private float $timeout;

    /**
     * @param string      $domain          Kinde domain, e.g. https://example.kinde.com
     * @param string      $clientId        Client ID of the "AI agents and MCP" application
     * @param string      $resourceUrl     Must equal the Resource URL configured on that application exactly
     * @param string|null $clientSecret    Required for introspection verification
     * @param string      $verification    One of the VERIFY_* constants
     * @param string[]    $scopesSupported Scopes advertised in the protected resource metadata; "offline" is always added
     * @param string|null $resourceName    Human-readable name advertised in the metadata
     * @param int         $clockTolerance  Seconds of clock skew tolerated when checking exp/nbf locally
     * @param float       $timeout         Seconds allowed for each call to Kinde
     */
    public function __construct(
        string $domain,
        string $clientId,
        string $resourceUrl,
        ?string $clientSecret = null,
        string $verification = self::VERIFY_INTROSPECTION,
        array $scopesSupported = [],
        ?string $resourceName = null,
        int $clockTolerance = 30,
        float $timeout = 5.0
    ) {
        $this->domain = rtrim(trim($domain), '/');
        $this->clientId = trim($clientId);
        $this->resourceUrl = trim($resourceUrl);
        $this->clientSecret = $clientSecret === null || trim($clientSecret) === '' ? null : $clientSecret;
        $this->verification = $verification;
        $this->scopesSupported = $scopesSupported;
        $this->resourceName = $resourceName === null || trim($resourceName) === '' ? null : $resourceName;
        $this->clockTolerance = $clockTolerance;
        $this->timeout = $timeout;

        $this->validate();
    }

    /**
     * Builds options from snake_case keys: domain, client_id, client_secret, resource_url,
     * verification, scopes_supported (array or space-delimited string), resource_name,
     * clock_tolerance, timeout.
     */
    public static function fromArray(array $config): self
    {
        $scopes = $config['scopes_supported'] ?? [];
        if (is_string($scopes)) {
            $scopes = preg_split('/\s+/', $scopes, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return new self(
            (string) ($config['domain'] ?? ''),
            (string) ($config['client_id'] ?? ''),
            (string) ($config['resource_url'] ?? ''),
            isset($config['client_secret']) ? (string) $config['client_secret'] : null,
            (string) (($config['verification'] ?? '') ?: self::VERIFY_INTROSPECTION),
            (array) $scopes,
            isset($config['resource_name']) ? (string) $config['resource_name'] : null,
            (int) ($config['clock_tolerance'] ?? 30),
            (float) ($config['timeout'] ?? 5.0)
        );
    }

    /**
     * Builds options from KINDE_DOMAIN, KINDE_AGENTS_CLIENT_ID, KINDE_AGENTS_CLIENT_SECRET,
     * KINDE_AGENTS_RESOURCE_URL, KINDE_AGENTS_VERIFICATION, KINDE_AGENTS_SCOPES and
     * KINDE_AGENTS_RESOURCE_NAME.
     */
    public static function fromEnvironment(): self
    {
        $env = static function (string $name): ?string {
            $value = getenv($name);
            return $value === false || $value === '' ? null : $value;
        };

        return self::fromArray([
            'domain' => $env('KINDE_DOMAIN'),
            'client_id' => $env('KINDE_AGENTS_CLIENT_ID'),
            'client_secret' => $env('KINDE_AGENTS_CLIENT_SECRET'),
            'resource_url' => $env('KINDE_AGENTS_RESOURCE_URL'),
            'verification' => $env('KINDE_AGENTS_VERIFICATION'),
            'scopes_supported' => $env('KINDE_AGENTS_SCOPES') ?? [],
            'resource_name' => $env('KINDE_AGENTS_RESOURCE_NAME'),
        ]);
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function getResourceUrl(): string
    {
        return $this->resourceUrl;
    }

    public function getVerification(): string
    {
        return $this->verification;
    }

    public function getResourceName(): ?string
    {
        return $this->resourceName;
    }

    public function getClockTolerance(): int
    {
        return $this->clockTolerance;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * The application's scoped authorization server, advertised in the protected resource metadata.
     */
    public function getIssuer(): string
    {
        return $this->domain . '/oauth2/agents/' . $this->clientId;
    }

    /**
     * The iss claim Kinde puts in agent access tokens: the bare domain, not the scoped issuer.
     */
    public function getExpectedTokenIssuer(): string
    {
        return $this->domain;
    }

    public function getJwksUrl(): string
    {
        return $this->domain . '/.well-known/jwks';
    }

    public function getAuthorizationServerMetadataUrl(): string
    {
        return $this->domain . '/.well-known/oauth-authorization-server/oauth2/agents/' . $this->clientId;
    }

    public function getIntrospectionUrl(): string
    {
        return $this->getIssuer() . '/introspect';
    }

    public function getRevocationUrl(): string
    {
        return $this->getIssuer() . '/revoke';
    }

    /**
     * RFC 9728 path: the well-known prefix followed by the resource URL's path, without a
     * trailing slash.
     */
    public function getProtectedResourceMetadataPath(): string
    {
        $path = rtrim((string) parse_url($this->resourceUrl, PHP_URL_PATH), '/');
        return self::WELL_KNOWN_PROTECTED_RESOURCE . $path;
    }

    public function getProtectedResourceMetadataUrl(): string
    {
        $parts = parse_url($this->resourceUrl);
        $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        return $origin . $this->getProtectedResourceMetadataPath();
    }

    /**
     * Paths the protected resource metadata is served at: the path-suffixed form MCP clients try
     * first and the root form older clients fall back to (identical when the resource path is "/").
     *
     * @return string[]
     */
    public function getProtectedResourceMetadataPaths(): array
    {
        return array_values(array_unique([$this->getProtectedResourceMetadataPath(), self::WELL_KNOWN_PROTECTED_RESOURCE]));
    }

    /**
     * @return string[] Advertised scopes, always including "offline" so agents receive a refresh token.
     */
    public function getScopesSupported(): array
    {
        $scopes = [self::OFFLINE_SCOPE];
        foreach ($this->scopesSupported as $scope) {
            $scope = trim((string) $scope);
            if ($scope !== '' && !in_array($scope, $scopes, true)) {
                $scopes[] = $scope;
            }
        }
        return $scopes;
    }

    private function validate(): void
    {
        $problems = [];

        $domain = parse_url($this->domain);
        if ($this->domain === '') {
            $problems[] = 'domain is required (for example https://example.kinde.com).';
        } elseif ($domain === false || ($domain['scheme'] ?? '') !== 'https' || empty($domain['host'])) {
            $problems[] = "domain must be an absolute https URL, but was '{$this->domain}'.";
        }

        if ($this->clientId === '') {
            $problems[] = "client_id is required. Use the AI agents and MCP application's client ID, "
                . 'not the one your web app signs users in with.';
        } elseif (!preg_match('/^[A-Za-z0-9._~-]+$/', $this->clientId)) {
            $problems[] = 'client_id contains characters that cannot appear in a URL path segment.';
        }

        $resource = parse_url($this->resourceUrl);
        if ($this->resourceUrl === '') {
            $problems[] = 'resource_url is required and must equal the Resource URL configured on the Kinde application exactly.';
        } elseif ($resource === false || empty($resource['scheme']) || empty($resource['host'])) {
            $problems[] = "resource_url must be an absolute URL, but was '{$this->resourceUrl}'.";
        } elseif (strpos($this->resourceUrl, '#') !== false) {
            $problems[] = "resource_url must not contain a fragment, but was '{$this->resourceUrl}'.";
        }

        $modes = [self::VERIFY_INTROSPECTION, self::VERIFY_JWT, self::VERIFY_JWT_THEN_INTROSPECTION];
        if (!in_array($this->verification, $modes, true)) {
            $problems[] = "verification must be one of '" . implode("', '", $modes) . "'.";
        }

        if ($this->clockTolerance < 0) {
            $problems[] = 'clock_tolerance cannot be negative.';
        }

        if ($this->timeout <= 0) {
            $problems[] = 'timeout must be greater than zero.';
        }

        if ($problems !== []) {
            throw new InvalidArgumentException(
                "Kinde agent authentication is not configured correctly:\n - " . implode("\n - ", $problems)
            );
        }
    }
}
