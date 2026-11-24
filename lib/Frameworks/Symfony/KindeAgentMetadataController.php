<?php

namespace Kinde\KindeSDK\Frameworks\Symfony;

use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Serves the discovery documents agents read to find Kinde: the protected resource metadata
 * (RFC 9728) and a mirror of the authorization server metadata (RFC 8414). Symfony 6.2+.
 */
final class KindeAgentMetadataController
{
    private KindeAgentAuth $auth;

    public function __construct(KindeAgentAuth $auth)
    {
        $this->auth = $auth;
    }

    #[Route('/.well-known/oauth-protected-resource{path}', name: 'kinde_agent_metadata', requirements: ['path' => '.*'], defaults: ['path' => ''], methods: ['GET', 'OPTIONS'])]
    public function protectedResource(Request $request): Response
    {
        // Only the path-suffixed form for the configured resource and the root form are answered.
        if (!in_array($request->getPathInfo(), $this->auth->getOptions()->getProtectedResourceMetadataPaths(), true)) {
            throw new NotFoundHttpException();
        }
        return $this->respond($request, $this->auth->metadata());
    }

    #[Route('/.well-known/oauth-authorization-server', name: 'kinde_agent_authorization_server', methods: ['GET', 'OPTIONS'])]
    public function authorizationServer(Request $request): Response
    {
        return $this->respond($request, $this->auth->authorizationServerMetadata());
    }

    private function respond(Request $request, array $document): Response
    {
        if ($request->isMethod('OPTIONS')) {
            return new Response('', 204, KindeAgentAuth::preflightHeaders());
        }
        $response = new JsonResponse($document, 200, KindeAgentAuth::metadataHeaders());
        $response->setEncodingOptions(JSON_UNESCAPED_SLASHES);
        return $response;
    }
}
