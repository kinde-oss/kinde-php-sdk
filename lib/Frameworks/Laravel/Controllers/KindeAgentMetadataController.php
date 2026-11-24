<?php

namespace Kinde\KindeSDK\Frameworks\Laravel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Kinde\KindeSDK\Sdk\Agents\Client\ClientMetadataDocument;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;

/**
 * Serves the discovery documents agents read to find Kinde. Routes are registered by
 * KindeServiceProvider from the "agents" section of config/kinde.php.
 */
class KindeAgentMetadataController
{
    /**
     * Protected resource metadata (RFC 9728).
     */
    public function protectedResource(KindeAgentAuth $auth): JsonResponse
    {
        return response()->json($auth->metadata(), 200, KindeAgentAuth::metadataHeaders(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Mirror of the application's authorization server metadata (RFC 8414), for agents that look
     * for it on the resource's origin.
     */
    public function authorizationServer(KindeAgentAuth $auth): JsonResponse
    {
        return response()->json($auth->authorizationServerMetadata(), 200, KindeAgentAuth::metadataHeaders(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * CORS preflight for the metadata documents.
     */
    public function preflight(): Response
    {
        return response('', 204, KindeAgentAuth::preflightHeaders());
    }

    /**
     * This agent's Client ID Metadata Document, served at its client_id URL.
     */
    public function clientMetadata(): JsonResponse
    {
        $config = config('kinde.agents.client_metadata', []);
        $redirectUris = $config['redirect_uris'] ?? [];
        if (is_string($redirectUris)) {
            $redirectUris = preg_split('/[\s,]+/', $redirectUris, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return response()->json(
            ClientMetadataDocument::build((string) $config['client_id'], (string) ($config['client_name'] ?? ''), $redirectUris),
            200,
            ClientMetadataDocument::RESPONSE_HEADERS,
            JSON_UNESCAPED_SLASHES
        );
    }
}
