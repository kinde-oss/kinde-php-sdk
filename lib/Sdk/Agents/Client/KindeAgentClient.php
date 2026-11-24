<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

use GuzzleHttp\ClientInterface;

/**
 * An agent's session with a Kinde "AI agents and MCP" application: works out the client identity
 * (CIMD, then a pre-registered client, then dynamic registration), runs the authorization code
 * flow with PKCE, and keeps the tokens fresh in an AgentStore.
 *
 *     $url = $client->createAuthorizationRequest()->url;   // send the user here
 *     $client->handleCallback($_GET);                      // on the redirect_uri
 *     $token = $client->getAccessToken();                  // call the resource with it
 */
final class KindeAgentClient
{
    // Re-register this long before a DCR secret expires, so a sign-in never starts with a dead client.
    private const REGISTRATION_RENEWAL_WINDOW = 86400;

    private KindeAgentClientOptions $options;
    private AgentStore $store;
    private AgentOAuth $oauth;
    /** @var array<string, mixed>|null */
    private ?array $discovery = null;

    public function __construct(KindeAgentClientOptions $options, AgentStore $store, ?ClientInterface $http = null)
    {
        $this->options = $options;
        $this->store = $store;
        $this->oauth = new AgentOAuth($http, $options->allowPrivateNetworks);
    }

    /**
     * The application's authorization server metadata, loaded once per instance.
     *
     * @return array<string, mixed>
     */
    public function discover(): array
    {
        return $this->discovery ??= $this->oauth->discoverKinde($this->options->domain, $this->options->agentApplicationId);
    }

    /**
     * Starts sign-in. Redirect the user to the returned URL; the request is kept in the store
     * until handleCallback consumes it.
     */
    public function createAuthorizationRequest(): AuthorizationRequest
    {
        $metadata = $this->discover();
        $identity = $this->resolveClientIdentity($metadata);
        $request = $this->oauth->createAuthorizationRequest(
            $metadata,
            $identity['client_id'],
            $identity['client_secret'],
            $this->options->redirectUri,
            $this->options->scopes,
            $this->options->resourceUrl
        );
        $this->store->set($this->key('pending'), (string) json_encode($request->toArray()));
        return $request;
    }

    /**
     * Completes sign-in from the redirect_uri's query parameters and stores the tokens.
     *
     * @param array<string, mixed> $params
     */
    public function handleCallback(array $params): TokenSet
    {
        $stored = json_decode((string) $this->store->get($this->key('pending')), true);
        if (!is_array($stored)) {
            throw new AgentAuthException('invalid_state', 'There is no authorization in progress for this session.');
        }
        $request = AuthorizationRequest::fromArray($stored);
        // Single use: a replayed callback must not reach the token endpoint again.
        $this->store->remove($this->key('pending'));

        try {
            $tokens = $this->oauth->exchangeCode($request, AgentOAuth::parseCallback($request, $params));
        } catch (AgentAuthException $e) {
            $this->forgetRejectedRegistration($e);
            throw $e;
        }
        $this->saveTokens($tokens);
        return $tokens;
    }

    /**
     * Returns a valid access token, refreshing it when needed, or null when the user has to
     * authorize (again). A transient failure while refreshing is thrown and the tokens are kept.
     *
     * @param bool $forceRefresh Set after the resource answered 401 invalid_token, to refresh
     *                           once before giving up and authorizing again
     */
    public function getAccessToken(bool $forceRefresh = false): ?string
    {
        $tokens = $this->getTokens();
        if ($tokens === null) {
            return null;
        }
        if (!$forceRefresh && !$tokens->isExpired(60)) {
            return $tokens->accessToken;
        }
        if ($tokens->refreshToken === null) {
            $this->store->remove($this->key('tokens'));
            return null;
        }
        try {
            $tokens = $this->oauth->refresh(
                (string) $this->discover()['token_endpoint'],
                $tokens,
                $this->options->resourceUrl
            );
        } catch (AgentAuthException $e) {
            if (in_array($e->getErrorCode(), ['invalid_grant', 'invalid_client', 'unauthorized_client'], true)) {
                $this->store->remove($this->key('tokens'));
                $this->forgetRejectedRegistration($e);
                return null;
            }
            throw $e;
        }
        $this->saveTokens($tokens);
        return $tokens->accessToken;
    }

    public function getTokens(): ?TokenSet
    {
        $stored = json_decode((string) $this->store->get($this->key('tokens')), true);
        if (!is_array($stored)) {
            return null;
        }
        try {
            return TokenSet::fromArray($stored);
        } catch (AgentAuthException $e) {
            $this->store->remove($this->key('tokens'));
            return null;
        }
    }

    /**
     * Revokes this session's tokens where the server supports it (on Kinde, revoking the refresh
     * token also kills the access token) and forgets them. The tokens are forgotten even if
     * revocation fails, since RFC 7009 gives the client nothing to act on.
     *
     * @param bool $forget Also drop the dynamic client registration, so the next sign-in registers anew
     */
    public function disconnect(bool $forget = false): void
    {
        $tokens = $this->getTokens();
        $this->store->remove($this->key('tokens'));
        if ($forget) {
            $this->store->remove($this->registrationKey());
        }
        if ($tokens === null) {
            return;
        }
        $metadata = $this->discover();
        if (!empty($metadata['revocation_endpoint'])) {
            try {
                $this->oauth->revoke($metadata, $tokens);
            } catch (AgentAuthException $e) {
                // Local state is already cleared; nothing else to do.
            }
        }
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array{method: string, client_id: string, client_secret: string|null}
     */
    private function resolveClientIdentity(array $metadata): array
    {
        $preRegistered = $this->options->clientId !== null
            ? ['client_id' => $this->options->clientId, 'client_secret' => $this->options->clientSecret]
            : $this->storedRegistration();

        $identity = $this->oauth->resolveClientIdentity(
            $metadata,
            $this->options->cimdClientId,
            $preRegistered,
            ['client_name' => $this->options->clientName, 'redirect_uris' => [$this->options->redirectUri]]
        );
        if ($identity['method'] === 'dcr') {
            $this->store->set($this->registrationKey(), (string) json_encode($identity));
        }
        return $identity;
    }

    /**
     * @return array{client_id: string, client_secret: string|null}|null
     */
    private function storedRegistration(): ?array
    {
        $stored = json_decode((string) $this->store->get($this->registrationKey()), true);
        if (!is_array($stored) || empty($stored['client_id'])) {
            return null;
        }
        $expiresAt = (int) ($stored['client_secret_expires_at'] ?? 0);
        // 0 means the secret never expires.
        if ($expiresAt !== 0 && $expiresAt <= time() + self::REGISTRATION_RENEWAL_WINDOW) {
            return null;
        }
        return ['client_id' => (string) $stored['client_id'], 'client_secret' => $stored['client_secret'] ?? null];
    }

    private function saveTokens(TokenSet $tokens): void
    {
        $this->store->set($this->key('tokens'), (string) json_encode($tokens->toArray()));
    }

    // invalid_client means the stored registration expired or was removed; dropping it makes the
    // next authorization request register again.
    private function forgetRejectedRegistration(AgentAuthException $e): void
    {
        if ($e->getErrorCode() === 'invalid_client') {
            $this->store->remove($this->registrationKey());
        }
    }

    private function issuer(): string
    {
        return $this->options->domain . '/oauth2/agents/' . $this->options->agentApplicationId;
    }

    // A registration belongs to the agent and its redirect URI, and is shared by every user.
    private function registrationKey(): string
    {
        return 'kinde-agent:registration:' . sha1($this->issuer() . "\n" . $this->options->redirectUri);
    }

    // Pending requests and tokens belong to one user's session with one resource.
    private function key(string $name): string
    {
        return 'kinde-agent:' . $name . ':' . sha1(
            $this->issuer() . "\n" . ($this->options->resourceUrl ?? '') . "\n" . $this->options->sessionKey
        );
    }
}
