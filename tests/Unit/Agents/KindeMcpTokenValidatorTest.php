<?php

namespace Kinde\KindeSDK\Tests\Unit\Agents;

use GuzzleHttp\Psr7\Response;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuthOptions;
use Kinde\KindeSDK\Sdk\Agents\KindeAuthContext;
use Kinde\KindeSDK\Sdk\Agents\Mcp\KindeMcpTokenValidator;
use Mcp\Server\Transport\Http\OAuth\AuthorizationTokenValidatorInterface;
use PHPUnit\Framework\TestCase;

class KindeMcpTokenValidatorTest extends TestCase
{
    use AgentTestKit;

    protected function setUp(): void
    {
        if (!interface_exists(AuthorizationTokenValidatorInterface::class)) {
            $this->markTestSkipped('mcp/sdk is not installed.');
        }
    }

    private function validator(array $responses): KindeMcpTokenValidator
    {
        return new KindeMcpTokenValidator(new KindeAgentAuth(
            new KindeAgentAuthOptions('https://acme.kinde.com', 'agent-app', 'https://api.example.com/mcp', 'secret'),
            $this->http($responses)
        ));
    }

    public function testInvalidTokenIsUnauthorized(): void
    {
        $result = $this->validator([self::json(['active' => false])])->validate(self::token());

        $this->assertSame(401, $result->getStatusCode());
        $this->assertSame('invalid_token', $result->getError());
    }

    public function testOutageIsUnavailable(): void
    {
        $this->assertSame(503, $this->validator([new Response(500)])->validate(self::token())->getStatusCode());
    }

    public function testValidTokenCarriesKindeClaimsIntoTools(): void
    {
        $token = self::token(['permissions' => ['read:docs']]);
        $result = $this->validator([self::json(['active' => true, 'aud' => ['https://api.example.com/mcp'], 'scope' => 'offline'])])->validate($token);

        $this->assertTrue($result->isAllowed());
        $this->assertSame(['offline'], $result->getAccessToken()->getScopes());
        $this->assertTrue(KindeAuthContext::fromClaims($result->getAccessToken()->getClaims())->hasPermission('read:docs'));
    }

    public function testMetadataMatchesTheCore(): void
    {
        $auth = new KindeAgentAuth(new KindeAgentAuthOptions('https://acme.kinde.com', 'agent-app', 'https://api.example.com/mcp', 'secret'));
        $metadata = json_decode((string) json_encode(KindeMcpTokenValidator::metadata($auth)), true);

        $this->assertSame($auth->metadata()['resource'], $metadata['resource']);
        $this->assertSame($auth->metadata()['authorization_servers'], $metadata['authorization_servers']);
    }
}
