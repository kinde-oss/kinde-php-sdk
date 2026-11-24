<?php

namespace Kinde\KindeSDK\Frameworks\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;

/**
 * Protects routes called by AI agents with Kinde-issued access tokens:
 *
 *     Route::get('/api/orders', ...)->middleware('kinde.agent');
 *     Route::post('/api/orders', ...)->middleware('kinde.agent:write:orders');   // required Kinde permissions
 *
 * A missing permission is a plain 403. The verified KindeAuthContext is on the request as the
 * "kinde_agent" attribute.
 */
class KindeAgentAuthMiddleware
{
    protected KindeAgentAuth $auth;

    public function __construct(KindeAgentAuth $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $context = KindeAgentAuth::requirePermissions(
            $this->auth->verifyAuthorizationHeader($request->header('Authorization')),
            ...$permissions
        );
        if (!$context->isAuthenticated()) {
            return response()->json(
                KindeAgentAuth::errorBody($context),
                KindeAgentAuth::statusCode($context),
                $this->auth->challengeHeaders($context)
            );
        }

        $request->attributes->set('kinde_agent', $context);
        return $next($request);
    }
}
