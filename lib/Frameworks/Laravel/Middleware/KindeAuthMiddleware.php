<?php

namespace Kinde\KindeSDK\Frameworks\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Kinde\KindeSDK\KindeClientSDK;

class KindeAuthMiddleware
{
    protected KindeClientSDK $kindeClient;

    public function __construct(KindeClientSDK $kindeClient)
    {
        $this->kindeClient = $kindeClient;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission = null): mixed
    {
        // Check if user is authenticated
        if (!$this->kindeClient->isAuthenticated) {
            return redirect()->route('kinde.login');
        }

        // Check specific permission if provided
        if ($permission) {
            $permissionCheck = $this->kindeClient->getPermission($permission);
            
            if (!$permissionCheck['isGranted']) {
                abort(403, 'Insufficient permissions');
            }
        }

        return $next($request);
    }
} 