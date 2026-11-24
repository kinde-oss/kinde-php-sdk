<?php

namespace Kinde\KindeSDK\Tests\Unit\Agents;

use Firebase\JWT\JWT;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kinde\KindeSDK\Sdk\Agents\JwtTokenVerifier;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuthOptions;
use Kinde\KindeSDK\Sdk\Agents\KindeAuthContext;
use PHPUnit\Framework\TestCase;

class KindeAgentAuthTest extends TestCase
{
    use AgentTestKit;

    private function auth(array $responses, string $verification = KindeAgentAuthOptions::VERIFY_INTROSPECTION, array $scopes = []): KindeAgentAuth
    {
        return new KindeAgentAuth(
            new KindeAgentAuthOptions('https://acme.kinde.com/', 'agent-app', 'https://api.example.com/mcp', 'se:cret', $verification, $scopes, 'Orders API'),
            $this->http($responses)
        );
    }

    public function testMissingHeaderIsChallengedWithoutAnErrorCode(): void
    {
        $auth = $this->auth([]);
        $context = $auth->verifyAuthorizationHeader(null);

        $this->assertSame(401, KindeAgentAuth::statusCode($context));
        $this->assertSame(
            'Bearer resource_metadata="https://api.example.com/.well-known/oauth-protected-resource/mcp"',
            $auth->challengeHeaders($context)['WWW-Authenticate']
        );
        $this->assertSame([], $this->history);
    }

    public function testNonBearerSchemeIsTreatedAsMissing(): void
    {
        $auth = $this->auth([]);
        $context = $auth->verifyAuthorizationHeader('Basic dXNlcjpwYXNz');

        $this->assertSame(KindeAuthContext::MISSING_TOKEN, $context->getFailureKind());
        $this->assertSame([], $this->history);
    }

    public function testIntrospectionSendsBasicCredentialsAndMergesTokenClaims(): void
    {
        $token = self::token(['permissions' => ['read:orders'], 'org_code' => 'org_1', 'roles' => [['key' => 'admin']]]);
        $auth = $this->auth([self::json(['active' => true, 'aud' => ['https://api.example.com/mcp'], 'scope' => 'offline', 'sub' => 'kp_user', 'exp' => time() + 60])]);

        $context = $auth->verifyAuthorizationHeader('Bearer ' . $token);

        $this->assertTrue($context->isAuthenticated());
        $this->assertSame('https://acme.kinde.com/oauth2/agents/agent-app/introspect', $this->sentUrl(0));
        $this->assertSame('Basic ' . base64_encode('agent-app:se%3Acret'), $this->history[0]['request']->getHeaderLine('Authorization'));
        $this->assertSame(['token' => $token, 'token_type_hint' => 'access_token'], $this->sentForm(0));
        $this->assertTrue($context->hasPermission('read:orders'));
        $this->assertSame('org_1', $context->getOrganizationCode());
        $this->assertTrue($context->hasRole('admin'));
        $this->assertSame($token, $context->getToken());
    }

    public function testInactiveTokenIsInvalid(): void
    {
        $auth = $this->auth([self::json(['active' => false])]);
        $context = $auth->verifyToken(self::token());

        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $context->getFailureKind());
        $this->assertStringContainsString('error="invalid_token"', $auth->challengeHeaders($context)['WWW-Authenticate']);
    }

    public function testIntrospectedTokenForAnotherResourceIsInvalid(): void
    {
        $auth = $this->auth([self::json(['active' => true, 'aud' => ['https://other.example.com/mcp']])]);

        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken(self::token(['aud' => ['https://other.example.com/mcp']]))->getFailureKind());
    }

    public function testRefreshTokenIsRejectedBeforeIntrospection(): void
    {
        $auth = $this->auth([]);

        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken('opaque-refresh-token')->getFailureKind());
        $this->assertSame([], $this->history);
    }

    public function testIntrospectionOutageIsUnavailableWithoutChallenge(): void
    {
        foreach ([new Response(502), self::json(['error' => 'invalid_client'], 401), new ConnectException('down', new Request('POST', '/'))] as $failure) {
            $auth = $this->auth([$failure]);
            $context = $auth->verifyToken(self::token());

            $this->assertSame(503, KindeAgentAuth::statusCode($context));
            $this->assertArrayNotHasKey('WWW-Authenticate', $auth->challengeHeaders($context));
            $this->assertSame('temporarily_unavailable', KindeAgentAuth::errorBody($context)['error']);
        }
    }

    public function testIntrospection401WithoutClientErrorIsInvalidToken(): void
    {
        $auth = $this->auth([self::json(['error' => 'invalid_token'], 401)]);

        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken(self::token())->getFailureKind());
    }

    public function testIntrospectionRequiresClientSecret(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new KindeAgentAuth(new KindeAgentAuthOptions('https://acme.kinde.com', 'agent-app', 'https://api.example.com/mcp'));
    }

    public function testJwtModeAcceptsDomainIssuerAndAudienceArray(): void
    {
        self::keys();
        $auth = $this->auth([self::json(self::$jwks)], KindeAgentAuthOptions::VERIFY_JWT);

        $context = $auth->verifyToken(self::token(['iss' => 'https://acme.kinde.com/', 'aud' => ['https://x.example.com', 'https://api.example.com/mcp']]));

        $this->assertTrue($context->isAuthenticated(), (string) $context->getFailureReason());
        $this->assertSame('https://acme.kinde.com/.well-known/jwks', $this->sentUrl(0));
    }

    public function testJwtModeRejectsBadTokens(): void
    {
        self::keys();
        $cases = [
            'scoped issuer' => self::token(['iss' => 'https://acme.kinde.com/oauth2/agents/agent-app']),
            'wrong audience' => self::token(['aud' => 'https://api.example.com']),
            'expired' => self::token(['exp' => time() - 120]),
            'hs256' => JWT::encode(['iss' => 'https://acme.kinde.com'], 'secret-secret-secret-secret-secret', 'HS256', 'kid-1'),
            'alg none' => JWT::urlsafeB64Encode('{"alg":"none","kid":"kid-1"}') . '.' . JWT::urlsafeB64Encode('{"iss":"https://acme.kinde.com"}') . '.',
        ];
        foreach ($cases as $name => $token) {
            $auth = $this->auth([self::json(self::$jwks)], KindeAgentAuthOptions::VERIFY_JWT);
            $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken($token)->getFailureKind(), $name);
        }
    }

    public function testUnknownKidsCannotHammerTheKeySet(): void
    {
        self::keys();
        $auth = $this->auth([self::json(self::$jwks)], KindeAgentAuthOptions::VERIFY_JWT);

        $this->assertTrue($auth->verifyToken(self::token())->isAuthenticated());
        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken(self::token([], 'kid-2'))->getFailureKind());
        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken(self::token([], 'kid-3'))->getFailureKind());
        $this->assertCount(1, $this->history);
    }

    public function testRotatedKeyIsPickedUpByOneRefetch(): void
    {
        self::keys();
        $options = new KindeAgentAuthOptions('https://acme.kinde.com', 'agent-app', 'https://api.example.com/mcp', null, KindeAgentAuthOptions::VERIFY_JWT);
        $verifier = new JwtTokenVerifier($options, $this->http([self::json(['keys' => []]), self::json(self::$jwks)]));
        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $verifier->verify(self::token())->getFailureKind());

        $lastFetch = new \ReflectionProperty(JwtTokenVerifier::class, 'lastFetch');
        if (PHP_VERSION_ID < 80100) {
            $lastFetch->setAccessible(true);
        }
        $lastFetch->setValue($verifier, time() - 120);

        $this->assertTrue($verifier->verify(self::token())->isAuthenticated());
        $this->assertCount(2, $this->history);
    }

    public function testJwtThenIntrospectionSkipsKindeForForgedTokens(): void
    {
        self::keys();
        $auth = $this->auth([self::json(self::$jwks), self::json(['active' => false])], KindeAgentAuthOptions::VERIFY_JWT_THEN_INTROSPECTION);

        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken(self::token(['aud' => 'https://evil.example.com']))->getFailureKind());
        $this->assertCount(1, $this->history);
        $this->assertSame(KindeAuthContext::INVALID_TOKEN, $auth->verifyToken(self::token())->getFailureKind(), 'revoked token');
        $this->assertCount(2, $this->history);
    }

    public function testMissingPermissionIsForbiddenWithoutChallenge(): void
    {
        $auth = $this->auth([]);
        $context = KindeAgentAuth::requirePermissions(KindeAuthContext::fromClaims(['permissions' => ['read:orders']]), 'read:orders', 'write:orders');

        $this->assertSame(403, KindeAgentAuth::statusCode($context));
        $this->assertArrayNotHasKey('WWW-Authenticate', $auth->challengeHeaders($context));
        $this->assertSame(['error' => 'forbidden', 'error_description' => 'Missing permission: write:orders'], KindeAgentAuth::errorBody($context));
        $this->assertTrue(KindeAgentAuth::requirePermissions(KindeAuthContext::fromClaims(['permissions' => ['read:orders']]), 'read:orders')->isAuthenticated());
    }

    public function testChallengeValuesCannotBreakTheHeader(): void
    {
        $auth = $this->auth([]);
        $header = $auth->challengeHeaders(KindeAuthContext::invalid("bad \"token\"\r\nX-Injected: 1"))['WWW-Authenticate'];

        $this->assertSame(
            'Bearer error="invalid_token", error_description="bad \"token\" X-Injected: 1", resource_metadata="https://api.example.com/.well-known/oauth-protected-resource/mcp"',
            $header
        );
    }

    public function testProtectedResourceMetadataAlwaysAdvertisesOffline(): void
    {
        $auth = $this->auth([], KindeAgentAuthOptions::VERIFY_INTROSPECTION, ['profile', 'offline']);

        $this->assertSame([
            'resource' => 'https://api.example.com/mcp',
            'authorization_servers' => ['https://acme.kinde.com/oauth2/agents/agent-app'],
            'bearer_methods_supported' => ['header'],
            'scopes_supported' => ['offline', 'profile'],
            'resource_name' => 'Orders API',
        ], $auth->metadata());
        $this->assertSame(
            ['/.well-known/oauth-protected-resource/mcp', '/.well-known/oauth-protected-resource'],
            $auth->getOptions()->getProtectedResourceMetadataPaths()
        );
        $this->assertSame('*', KindeAgentAuth::metadataHeaders()['Access-Control-Allow-Origin']);
    }

    public function testAuthorizationServerMirrorAdvertisesCimd(): void
    {
        $metadata = $this->auth([])->authorizationServerMetadata();

        $this->assertSame('https://acme.kinde.com/oauth2/agents/agent-app', $metadata['issuer']);
        $this->assertTrue($metadata['client_id_metadata_document_supported']);
        $this->assertSame(['S256'], $metadata['code_challenge_methods_supported']);
    }

    public function testRevokeTokenPostsBasicCredentials(): void
    {
        $auth = $this->auth([new Response(200), new Response(400)]);

        $auth->revokeToken('tok', 'refresh_token');
        $this->assertSame('https://acme.kinde.com/oauth2/agents/agent-app/revoke', $this->sentUrl(0));
        $this->assertSame(['token' => 'tok', 'token_type_hint' => 'refresh_token'], $this->sentForm(0));
        $this->assertStringStartsWith('Basic ', $this->history[0]['request']->getHeaderLine('Authorization'));

        $this->expectException(\RuntimeException::class);
        $auth->revokeToken('tok');
    }
}
