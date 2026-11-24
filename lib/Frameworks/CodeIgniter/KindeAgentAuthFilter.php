<?php

namespace Kinde\KindeSDK\Frameworks\CodeIgniter;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuthOptions;
use Kinde\KindeSDK\Sdk\Agents\KindeAuthContext;

/**
 * Protects routes called by AI agents with Kinde-issued access tokens. Configured from the
 * KINDE_DOMAIN and KINDE_AGENTS_* environment variables. In app/Config/Filters.php:
 *
 *     public array $aliases = ['kinde-agent' => KindeAgentAuthFilter::class];
 *
 * then on a route: ['filter' => 'kinde-agent'] or ['filter' => 'kinde-agent:write:orders'] to
 * require Kinde permissions (a missing permission is a plain 403). Controllers read the verified
 * identity from KindeAgentAuthFilter::context().
 */
class KindeAgentAuthFilter implements FilterInterface
{
    private static ?KindeAgentAuth $auth = null;
    private static ?KindeAuthContext $context = null;

    public static function auth(): KindeAgentAuth
    {
        return self::$auth ??= new KindeAgentAuth(KindeAgentAuthOptions::fromEnvironment());
    }

    /**
     * The verified agent identity for the current request, or null outside a filtered route.
     */
    public static function context(): ?KindeAuthContext
    {
        return self::$context;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // Workers can serve many requests per process, so never let one request see another's identity.
        self::$context = null;

        $auth = self::auth();
        $context = KindeAgentAuth::requirePermissions(
            $auth->verifyAuthorizationHeader($request->getHeaderLine('Authorization')),
            ...array_map('strval', (array) $arguments)
        );
        if (!$context->isAuthenticated()) {
            $response = service('response')
                ->setStatusCode(KindeAgentAuth::statusCode($context))
                ->setJSON(KindeAgentAuth::errorBody($context));
            foreach ($auth->challengeHeaders($context) as $name => $value) {
                $response->setHeader($name, $value);
            }
            return $response;
        }

        self::$context = $context;
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
