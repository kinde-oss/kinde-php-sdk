<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

use InvalidArgumentException;

/**
 * Configuration for an agent that signs users in to a Kinde "AI agents and MCP" application.
 *
 * Keys: domain, agent_application_id and redirect_uri (required); cimd_client_id (the https URL
 * of the agent's Client ID Metadata Document, preferred); client_id and client_secret (a
 * pre-registered client); resource_url; scopes; client_name (used for dynamic registration);
 * session_key (separates the tokens of different users); allow_private_networks.
 */
final class KindeAgentClientOptions
{
    public string $domain;
    public string $agentApplicationId;
    public string $redirectUri;
    public ?string $cimdClientId;
    public ?string $clientId;
    public ?string $clientSecret;
    public ?string $resourceUrl;
    /** @var string[] */
    public array $scopes;
    public string $clientName;
    public string $sessionKey;
    public bool $allowPrivateNetworks;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $optional = static function (string $key) use ($config): ?string {
            $value = isset($config[$key]) ? trim((string) $config[$key]) : '';
            return $value === '' ? null : $value;
        };

        $this->domain = rtrim((string) $optional('domain'), '/');
        $this->agentApplicationId = (string) $optional('agent_application_id');
        $this->redirectUri = (string) $optional('redirect_uri');
        $this->cimdClientId = $optional('cimd_client_id');
        $this->clientId = $optional('client_id');
        $this->clientSecret = $optional('client_secret');
        $this->resourceUrl = $optional('resource_url');
        $this->scopes = AgentOAuth::normalizeScopes($config['scopes'] ?? []);
        $this->clientName = $optional('client_name') ?? 'Kinde PHP agent';
        $this->sessionKey = $optional('session_key') ?? 'default';
        $this->allowPrivateNetworks = (bool) ($config['allow_private_networks'] ?? false);

        $this->validate();
    }

    private function validate(): void
    {
        $problems = [];
        if ($this->domain === '') {
            $problems[] = 'domain is required (for example https://example.kinde.com).';
        } elseif (strtolower((string) parse_url($this->domain, PHP_URL_SCHEME)) !== 'https') {
            $problems[] = "domain must be an absolute https URL, but was '{$this->domain}'.";
        }
        if ($this->agentApplicationId === '') {
            $problems[] = 'agent_application_id is required. Use the client ID of the Kinde AI agents and MCP application.';
        }
        if ($this->redirectUri === '') {
            $problems[] = 'redirect_uri is required.';
        }
        if ($this->resourceUrl !== null && empty(parse_url($this->resourceUrl, PHP_URL_HOST))) {
            $problems[] = "resource_url must be an absolute URL, but was '{$this->resourceUrl}'.";
        }
        if (in_array('openid', $this->scopes, true)) {
            $problems[] = 'scopes cannot include openid; agent tokens are access tokens only.';
        }
        if ($problems !== []) {
            throw new InvalidArgumentException(
                "Kinde agent client is not configured correctly:\n - " . implode("\n - ", $problems)
            );
        }
        if ($this->redirectUri !== '') {
            ClientMetadataDocument::assertValidRedirectUri($this->redirectUri);
        }
        if ($this->cimdClientId !== null) {
            ClientMetadataDocument::assertValidClientId($this->cimdClientId);
        }
    }
}
