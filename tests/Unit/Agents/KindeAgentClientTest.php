<?php

namespace Kinde\KindeSDK\Tests\Unit\Agents;

use GuzzleHttp\Psr7\Response;
use Kinde\KindeSDK\Sdk\Agents\Client\AgentAuthException;
use Kinde\KindeSDK\Sdk\Agents\Client\AgentOAuth;
use Kinde\KindeSDK\Sdk\Agents\Client\AgentStore;
use Kinde\KindeSDK\Sdk\Agents\Client\ClientMetadataDocument;
use Kinde\KindeSDK\Sdk\Agents\Client\KindeAgentClient;
use Kinde\KindeSDK\Sdk\Agents\Client\KindeAgentClientOptions;
use PHPUnit\Framework\TestCase;

class KindeAgentClientTest extends TestCase
{
    use AgentTestKit;

    private const ISSUER = 'https://acme.kinde.com/oauth2/agents/agent-app';
    private const CIMD = 'https://agent.example.com/oauth/client.json';

    private AgentStore $store;

    protected function setUp(): void
    {
        $this->store = new class () implements AgentStore {
            public array $data = [];

            public function get(string $key): ?string
            {
                return $this->data[$key] ?? null;
            }

            public function set(string $key, string $value): void
            {
                $this->data[$key] = $value;
            }

            public function remove(string $key): void
            {
                unset($this->data[$key]);
            }
        };
    }

    private static function serverMetadata(array $overrides = []): Response
    {
        return self::json($overrides + [
            'issuer' => self::ISSUER,
            'authorization_endpoint' => self::ISSUER . '/authorize',
            'token_endpoint' => self::ISSUER . '/token',
            'registration_endpoint' => self::ISSUER . '/register',
            'revocation_endpoint' => self::ISSUER . '/revoke',
            'code_challenge_methods_supported' => ['S256'],
            'client_id_metadata_document_supported' => true,
        ]);
    }

    private static function tokens(array $overrides = []): Response
    {
        return self::json($overrides + ['access_token' => 'at-1', 'refresh_token' => 'rt-1', 'token_type' => 'Bearer', 'expires_in' => 3600]);
    }

    private function client(array $responses, array $config = []): KindeAgentClient
    {
        return new KindeAgentClient(new KindeAgentClientOptions($config + [
            'domain' => 'https://acme.kinde.com',
            'agent_application_id' => 'agent-app',
            'redirect_uri' => 'http://127.0.0.1:8765/callback',
            'resource_url' => 'https://api.example.com/mcp',
        ]), $this->store, $this->http($responses));
    }

    private function signIn(KindeAgentClient $client): void
    {
        $request = $client->createAuthorizationRequest();
        $client->handleCallback(['state' => $request->state, 'code' => 'code-1']);
    }

    public function testParsesBearerChallengeAmongOthersWithQuotedCommas(): void
    {
        $params = AgentOAuth::parseWwwAuthenticate(
            'DPoP algs="ES256 PS256", Bearer error="invalid_token", error_description="expired, \"really\"", resource_metadata="https://api.example.com/.well-known/oauth-protected-resource/mcp"'
        );

        $this->assertSame([
            'error' => 'invalid_token',
            'error_description' => 'expired, "really"',
            'resource_metadata' => 'https://api.example.com/.well-known/oauth-protected-resource/mcp',
        ], $params);
        $this->assertNull(AgentOAuth::parseWwwAuthenticate('Basic realm="x"'));
    }

    public function testDiscoveryFallsBackToRootMetadataAndAcceptsParentResource(): void
    {
        $oauth = new AgentOAuth($this->http([
            new Response(404),
            self::json(['resource' => 'https://api.example.com', 'authorization_servers' => [self::ISSUER], 'scopes_supported' => ['offline', 'openid']]),
            self::serverMetadata(),
        ]));

        $discovery = $oauth->discoverFromResource('https://api.example.com/mcp');

        $this->assertSame('https://api.example.com/.well-known/oauth-protected-resource/mcp', $this->sentUrl(0));
        $this->assertSame('https://api.example.com/.well-known/oauth-protected-resource', $discovery['resource_metadata_url']);
        $this->assertSame('https://acme.kinde.com/.well-known/oauth-authorization-server/oauth2/agents/agent-app', $this->sentUrl(2));
        $this->assertSame('offline', $discovery['scope']);
    }

    public function testDiscoveryRejectsMetadataForAnotherResource(): void
    {
        $oauth = new AgentOAuth($this->http([self::json(['resource' => 'https://api.example.com/other', 'authorization_servers' => [self::ISSUER]])]));

        try {
            $oauth->discoverFromResource('https://api.example.com/mcp', 'Bearer resource_metadata="https://api.example.com/.well-known/oauth-protected-resource/mcp"');
            $this->fail('Accepted metadata for another resource');
        } catch (AgentAuthException $e) {
            $this->assertSame('resource_mismatch', $e->getErrorCode());
        }
    }

    public function testServerMetadataMustMatchIssuerAndSupportS256(): void
    {
        $cases = [
            'issuer_mismatch' => self::serverMetadata(['issuer' => 'https://evil.example.com']),
            'invalid_response' => self::serverMetadata(['code_challenge_methods_supported' => ['plain']]),
        ];
        foreach ($cases as $code => $response) {
            try {
                (new AgentOAuth($this->http([$response])))->discoverKinde('https://acme.kinde.com', 'agent-app');
                $this->fail("Expected {$code}");
            } catch (AgentAuthException $e) {
                $this->assertSame($code, $e->getErrorCode());
            }
        }
    }

    public function testCimdSignInNeverRegistersOrSendsASecret(): void
    {
        $client = $this->client([self::serverMetadata(), self::tokens()], ['cimd_client_id' => self::CIMD, 'scopes' => 'profile']);

        $request = $client->createAuthorizationRequest();
        parse_str((string) parse_url($request->url, PHP_URL_QUERY), $query);
        $this->assertSame(self::CIMD, $query['client_id']);
        $this->assertSame('offline profile', $query['scope']);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertSame('https://api.example.com/mcp', $query['resource']);

        $client->handleCallback(['state' => $request->state, 'code' => 'code-1']);
        $form = $this->sentForm(1);
        $this->assertCount(2, $this->history);
        $this->assertSame(self::ISSUER . '/token', $this->sentUrl(1));
        $this->assertSame(self::CIMD, $form['client_id']);
        $this->assertArrayNotHasKey('client_secret', $form);
        $this->assertSame('https://api.example.com/mcp', $form['resource']);
        $this->assertSame('at-1', $client->getAccessToken());
    }

    public function testFallsBackToDcrAndUsesTheIssuedSecret(): void
    {
        $client = $this->client([
            self::serverMetadata(['client_id_metadata_document_supported' => false]),
            self::json(['client_id' => 'dcr-client', 'client_secret' => 'dcr-secret', 'token_endpoint_auth_method' => 'client_secret_post', 'client_secret_expires_at' => time() + 30 * 86400], 201),
            self::tokens(),
        ], ['cimd_client_id' => self::CIMD]);

        $this->signIn($client);

        $registration = json_decode((string) $this->history[1]['request']->getBody(), true);
        $this->assertSame(self::ISSUER . '/register', $this->sentUrl(1));
        $this->assertSame('none', $registration['token_endpoint_auth_method']);
        $this->assertSame(['http://127.0.0.1:8765/callback'], $registration['redirect_uris']);
        $this->assertSame(['dcr-client', 'dcr-secret'], [$this->sentForm(2)['client_id'], $this->sentForm(2)['client_secret']]);
    }

    public function testRegistrationExpiringWithinADayIsRenewed(): void
    {
        $client = $this->client([
            self::serverMetadata(['client_id_metadata_document_supported' => false]),
            self::json(['client_id' => 'dcr-1', 'client_secret' => 's', 'client_secret_expires_at' => time() + 3600], 201),
            self::json(['client_id' => 'dcr-2', 'client_secret' => 's', 'client_secret_expires_at' => time() + 30 * 86400], 201),
        ]);

        $this->assertSame('dcr-1', $client->createAuthorizationRequest()->clientId);
        $this->assertSame('dcr-2', $client->createAuthorizationRequest()->clientId);
        $this->assertSame('dcr-2', $client->createAuthorizationRequest()->clientId);
        $this->assertCount(3, $this->history);
    }

    public function testInvalidClientOnRefreshForgetsTheRegistration(): void
    {
        $client = $this->client([
            self::serverMetadata(['client_id_metadata_document_supported' => false]),
            self::json(['client_id' => 'dcr-1', 'client_secret' => 's', 'client_secret_expires_at' => 0], 201),
            self::tokens(),
            self::json(['error' => 'invalid_client'], 401),
            self::json(['client_id' => 'dcr-2', 'client_secret' => 's', 'client_secret_expires_at' => 0], 201),
        ]);
        $this->signIn($client);

        $this->assertNull($client->getAccessToken(true));
        $this->assertNull($client->getTokens());
        $this->assertSame('dcr-2', $client->createAuthorizationRequest()->clientId);
    }

    public function testOpenIdIsRefused(): void
    {
        try {
            $this->client([], ['scopes' => 'openid offline']);
            $this->fail('openid accepted in options');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('openid', $e->getMessage());
        }

        $this->expectException(AgentAuthException::class);
        (new AgentOAuth($this->http([])))->createAuthorizationRequest(
            json_decode((string) self::serverMetadata()->getBody(), true),
            self::CIMD,
            null,
            'http://127.0.0.1/callback',
            ['openid']
        );
    }

    public function testCallbackStateMismatchIsRejectedAndNotReplayable(): void
    {
        $client = $this->client([self::serverMetadata()], ['cimd_client_id' => self::CIMD]);
        $request = $client->createAuthorizationRequest();

        foreach ([['state' => 'forged', 'code' => 'c'], ['state' => $request->state, 'code' => 'c']] as $params) {
            try {
                $client->handleCallback($params);
                $this->fail('callback accepted');
            } catch (AgentAuthException $e) {
                $this->assertSame('invalid_state', $e->getErrorCode());
            }
        }
        $this->assertCount(1, $this->history);
    }

    public function testRefreshKeepsTheOldRefreshTokenUnlessANewOneIsIssued(): void
    {
        $client = $this->client([
            self::serverMetadata(),
            self::tokens(),
            self::json(['access_token' => 'at-2', 'expires_in' => 3600]),
            self::tokens(['access_token' => 'at-3', 'refresh_token' => 'rt-3']),
        ], ['cimd_client_id' => self::CIMD]);
        $this->signIn($client);

        $this->assertSame('at-2', $client->getAccessToken(true));
        $this->assertSame('rt-1', $this->sentForm(2)['refresh_token']);
        $this->assertSame('rt-1', $client->getTokens()->refreshToken);

        $this->assertSame('at-3', $client->getAccessToken(true));
        $this->assertSame('rt-3', $client->getTokens()->refreshToken);
    }

    public function testDisconnectRevokesTheRefreshTokenAndClearsTokens(): void
    {
        $client = $this->client([self::serverMetadata(), self::tokens(), new Response(503)], ['cimd_client_id' => self::CIMD]);
        $this->signIn($client);

        $client->disconnect();

        $this->assertSame(self::ISSUER . '/revoke', $this->sentUrl(2));
        $this->assertSame(['token' => 'rt-1', 'token_type_hint' => 'refresh_token', 'client_id' => self::CIMD], $this->sentForm(2));
        $this->assertNull($client->getTokens());
    }

    public function testClientMetadataDocumentRules(): void
    {
        $document = ClientMetadataDocument::build(self::CIMD, 'Agent', ['https://agent.example.com/callback', 'http://127.0.0.1:8765/callback']);
        $this->assertSame('none', $document['token_endpoint_auth_method']);

        $invalid = [
            'other host' => [self::CIMD, ['https://evil.example.com/callback']],
            'custom scheme' => [self::CIMD, ['myagent://callback']],
            'no path' => ['https://agent.example.com', ['https://agent.example.com/callback']],
            'query' => [self::CIMD . '?v=1', ['https://agent.example.com/callback']],
        ];
        foreach ($invalid as $name => [$clientId, $redirects]) {
            try {
                ClientMetadataDocument::build($clientId, 'Agent', $redirects);
                $this->fail("Accepted {$name}");
            } catch (AgentAuthException $e) {
                $this->assertSame('invalid_client_metadata', $e->getErrorCode(), $name);
            }
        }
    }
}
