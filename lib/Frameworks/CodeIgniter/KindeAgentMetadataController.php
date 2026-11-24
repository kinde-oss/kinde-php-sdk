<?php

namespace Kinde\KindeSDK\Frameworks\CodeIgniter;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Kinde\KindeSDK\Sdk\Agents\Client\ClientMetadataDocument;
use Kinde\KindeSDK\Sdk\Agents\KindeAgentAuth;

/**
 * Serves the discovery documents agents read to find Kinde. In app/Config/Routes.php, for a
 * resource URL of https://api.example.com/mcp:
 *
 *     $routes->match(['GET', 'OPTIONS'], '.well-known/oauth-protected-resource/mcp', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAgentMetadataController::protectedResource');
 *     $routes->match(['GET', 'OPTIONS'], '.well-known/oauth-protected-resource', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAgentMetadataController::protectedResource');
 *     $routes->match(['GET', 'OPTIONS'], '.well-known/oauth-authorization-server', '\Kinde\KindeSDK\Frameworks\CodeIgniter\KindeAgentMetadataController::authorizationServer');
 *
 * An agent using CIMD routes the path of KINDE_AGENTS_CLIENT_METADATA_URL to clientMetadata.
 */
class KindeAgentMetadataController extends Controller
{
    /**
     * Protected resource metadata (RFC 9728).
     */
    public function protectedResource(): ResponseInterface
    {
        return $this->respond(KindeAgentAuthFilter::auth()->metadata(), KindeAgentAuth::metadataHeaders());
    }

    /**
     * Mirror of the application's authorization server metadata (RFC 8414).
     */
    public function authorizationServer(): ResponseInterface
    {
        return $this->respond(KindeAgentAuthFilter::auth()->authorizationServerMetadata(), KindeAgentAuth::metadataHeaders());
    }

    /**
     * This agent's Client ID Metadata Document, from KINDE_AGENTS_CLIENT_METADATA_URL,
     * KINDE_AGENTS_CLIENT_NAME and KINDE_AGENTS_REDIRECT_URIS (space or comma separated).
     */
    public function clientMetadata(): ResponseInterface
    {
        $document = ClientMetadataDocument::build(
            (string) getenv('KINDE_AGENTS_CLIENT_METADATA_URL'),
            (string) getenv('KINDE_AGENTS_CLIENT_NAME'),
            preg_split('/[\s,]+/', (string) getenv('KINDE_AGENTS_REDIRECT_URIS'), -1, PREG_SPLIT_NO_EMPTY) ?: []
        );
        return $this->respond($document, ClientMetadataDocument::RESPONSE_HEADERS);
    }

    /**
     * @param array<string, string> $headers
     */
    private function respond(array $document, array $headers): ResponseInterface
    {
        if (strtoupper($this->request->getMethod()) === 'OPTIONS') {
            $this->response->setStatusCode(204);
            $headers = KindeAgentAuth::preflightHeaders();
        } else {
            $this->response->setBody((string) json_encode($document, JSON_UNESCAPED_SLASHES));
        }
        foreach ($headers as $name => $value) {
            $this->response->setHeader($name, $value);
        }
        return $this->response;
    }
}
