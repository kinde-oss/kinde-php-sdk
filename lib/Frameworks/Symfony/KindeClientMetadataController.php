<?php

namespace Kinde\KindeSDK\Frameworks\Symfony;

use Kinde\KindeSDK\Sdk\Agents\Client\ClientMetadataDocument;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Serves this agent's Client ID Metadata Document. Route it at the path of the document's
 * client_id, and pass the document from ClientMetadataDocument::build().
 */
final class KindeClientMetadataController
{
    private array $document;

    public function __construct(array $document)
    {
        $this->document = $document;
    }

    public function __invoke(): JsonResponse
    {
        $response = new JsonResponse($this->document, 200, ClientMetadataDocument::RESPONSE_HEADERS);
        $response->setEncodingOptions(JSON_UNESCAPED_SLASHES);
        return $response;
    }
}
