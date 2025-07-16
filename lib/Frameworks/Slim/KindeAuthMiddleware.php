<?php

namespace Kinde\KindeSDK\Frameworks\Slim;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Kinde\KindeSDK\KindeClientSDK;
use Kinde\KindeSDK\OAuthException;

class KindeAuthMiddleware
{
    protected KindeClientSDK $kindeClient;

    public function __construct(KindeClientSDK $kindeClient)
    {
        $this->kindeClient = $kindeClient;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if user is authenticated
        if (!$this->kindeClient->isAuthenticated) {
            $response = new \Slim\Psr7\Response();
            return $response
                ->withStatus(302)
                ->withHeader('Location', '/auth/login');
        }

        return $handler->handle($request);
    }

    /**
     * Create middleware with permission check
     */
    public static function withPermission(string $permission): callable
    {
        return function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($permission) {
            $kindeClient = $request->getAttribute('kinde_client');
            
            if (!$kindeClient->isAuthenticated) {
                $response = new \Slim\Psr7\Response();
                return $response
                    ->withStatus(302)
                    ->withHeader('Location', '/auth/login');
            }

            $permissionCheck = $kindeClient->getPermission($permission);
            
            if (!$permissionCheck['isGranted']) {
                $response = new \Slim\Psr7\Response();
                return $response
                    ->withStatus(403)
                    ->withHeader('Content-Type', 'application/json');
            }

            return $handler->handle($request);
        };
    }
} 