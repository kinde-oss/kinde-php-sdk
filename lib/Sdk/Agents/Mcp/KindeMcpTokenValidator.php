<?php

namespace Kinde\KindeSDK\Sdk\Agents\Mcp;

use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;
use Kinde\KindeSDK\Sdk\Agents\KindeAuthContext;
use Mcp\Server\Authorization\AccessToken;
use Mcp\Server\Transport\Http\OAuth\AuthorizationResult;
use Mcp\Server\Transport\Http\OAuth\AuthorizationTokenValidatorInterface;
use Mcp\Server\Transport\Http\OAuth\ProtectedResourceMetadata;

/**
 * Adapter for the official MCP PHP SDK (mcp/sdk, PHP 8.2+). Plug it into the SDK's HTTP
 * transport so agent tokens are verified by Kinde:
 *
 *     $auth = new KindeAgentAuth(KindeAgentAuthOptions::fromEnvironment());
 *     $middleware = [
 *         new ProtectedResourceMetadataMiddleware(KindeMcpTokenValidator::metadata($auth)),
 *         new AuthorizationMiddleware(new KindeMcpTokenValidator($auth), KindeMcpTokenValidator::metadata($auth)),
 *     ];
 *
 * The verified claims (including exp and aud) are on the request's AccessToken. Check Kinde
 * permissions inside each tool and answer with a tool error, never an authorization failure:
 *
 *     $kinde = KindeAuthContext::fromClaims($context->getAccessToken()->getClaims());
 *     if (!$kinde->hasPermission('read:docs')) {
 *         return new CallToolResult([new TextContent("You don't have permission to read docs.")], true);
 *     }
 */
final class KindeMcpTokenValidator implements AuthorizationTokenValidatorInterface
{
    private KindeAgentAuth $auth;
    /** @var string[] */
    private array $requiredScopes;

    /**
     * @param string[] $requiredScopes Scopes every request to the MCP server must carry
     */
    public function __construct(KindeAgentAuth $auth, array $requiredScopes = [])
    {
        $this->auth = $auth;
        $this->requiredScopes = $requiredScopes;
    }

    public function validate(string $accessToken): AuthorizationResult
    {
        $context = KindeAgentAuth::requireScopes($this->auth->verifyToken($accessToken), $this->requiredScopes);

        switch ($context->getFailureKind()) {
            case null:
                return AuthorizationResult::allow(new AccessToken($context->getScopes(), $context->getClaims()));
            case KindeAuthContext::INSUFFICIENT_SCOPE:
                return AuthorizationResult::forbidden('insufficient_scope', null, $context->getRequiredScopes());
            case KindeAuthContext::UNAVAILABLE:
                return AuthorizationResult::unavailable();
            case KindeAuthContext::MISSING_TOKEN:
                return AuthorizationResult::unauthorized();
            default:
                return AuthorizationResult::unauthorized('invalid_token', $context->getFailureReason());
        }
    }

    /**
     * The protected resource metadata for the MCP SDK's middleware, matching KindeAgentAuth::metadata().
     */
    public static function metadata(KindeAgentAuth $auth): ProtectedResourceMetadata
    {
        $options = $auth->getOptions();
        return new ProtectedResourceMetadata(
            $options->getResourceUrl(),
            [$options->getIssuer()],
            $options->getScopesSupported(),
            $options->getResourceName()
        );
    }
}
