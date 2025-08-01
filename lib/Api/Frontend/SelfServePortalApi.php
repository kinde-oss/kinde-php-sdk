<?php
/**
 * SelfServePortalApi
 * PHP version 8.1
 *
 * @category Class
 * @package  Kinde\KindeSDK
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Kinde Account API
 *
 * Provides endpoints to operate on an authenticated user.  ## Intro  ## How to use  1. Get a user access token - this can be obtained when a user signs in via the methods you've setup in Kinde (e.g. Google, passwordless, etc).  2. Call one of the endpoints below using the user access token in the Authorization header as a Bearer token. Typically, you can use the `getToken` command in the relevant SDK.
 *
 * The version of the OpenAPI document: 1
 * Contact: support@kinde.com
 * Generated by: https://openapi-generator.tech
 * Generator version: 7.13.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Kinde\KindeSDK\Api\Frontend;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Kinde\KindeSDK\ApiException;
use Kinde\KindeSDK\Configuration;
use Kinde\KindeSDK\FormDataProcessor;
use Kinde\KindeSDK\HeaderSelector;
use Kinde\KindeSDK\ObjectSerializer;

/**
 * SelfServePortalApi Class Doc Comment
 *
 * @category Class
 * @package  Kinde\KindeSDK
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */
class SelfServePortalApi
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    /**
     * @var int Host index
     */
    protected $hostIndex;

    /** @var string[] $contentTypes **/
    public const contentTypes = [
        'getPortalLink' => [
            'application/json',
        ],
    ];

    /**
     * @param ClientInterface $client
     * @param Configuration   $config
     * @param HeaderSelector  $selector
     * @param int             $hostIndex (Optional) host index to select the list of hosts if defined in the OpenAPI spec
     */
    public function __construct(
        ?ClientInterface $client = null,
        ?Configuration $config = null,
        ?HeaderSelector $selector = null,
        int $hostIndex = 0
    ) {
        $this->client = $client ?: new Client();
        $this->config = $config ?: Configuration::getDefaultConfiguration();
        $this->headerSelector = $selector ?: new HeaderSelector();
        $this->hostIndex = $hostIndex;
    }

    /**
     * Set the host index
     *
     * @param int $hostIndex Host index (required)
     */
    public function setHostIndex($hostIndex): void
    {
        $this->hostIndex = $hostIndex;
    }

    /**
     * Get the host index
     *
     * @return int Host index
     */
    public function getHostIndex()
    {
        return $this->hostIndex;
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Operation getPortalLink
     *
     * Get self-serve portal link
     *
     * @param  string|null $subnav The area of the portal you want the user to land on (optional)
     * @param  string|null $return_url The URL to redirect the user to after they have completed their actions in the portal. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPortalLink'] to see the possible values for this operation
     *
     * @throws \Kinde\KindeSDK\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return \Kinde\KindeSDK\Model\Frontend\PortalLink
     */
    public function getPortalLink($subnav = null, $return_url = null, string $contentType = self::contentTypes['getPortalLink'][0])
    {
        list($response) = $this->getPortalLinkWithHttpInfo($subnav, $return_url, $contentType);
        return $response;
    }

    /**
     * Operation getPortalLinkWithHttpInfo
     *
     * Get self-serve portal link
     *
     * @param  string|null $subnav The area of the portal you want the user to land on (optional)
     * @param  string|null $return_url The URL to redirect the user to after they have completed their actions in the portal. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPortalLink'] to see the possible values for this operation
     *
     * @throws \Kinde\KindeSDK\ApiException on non-2xx response or if the response body is not in the expected format
     * @throws \InvalidArgumentException
     * @return array of \Kinde\KindeSDK\Model\Frontend\PortalLink, HTTP status code, HTTP response headers (array of strings)
     */
    public function getPortalLinkWithHttpInfo($subnav = null, $return_url = null, string $contentType = self::contentTypes['getPortalLink'][0])
    {
        $request = $this->getPortalLinkRequest($subnav, $return_url, $contentType);

        try {
            $options = $this->createHttpClientOption();
            try {
                $response = $this->client->send($request, $options);
            } catch (RequestException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    $e->getResponse() ? $e->getResponse()->getHeaders() : null,
                    $e->getResponse() ? (string) $e->getResponse()->getBody() : null
                );
            } catch (ConnectException $e) {
                throw new ApiException(
                    "[{$e->getCode()}] {$e->getMessage()}",
                    (int) $e->getCode(),
                    null,
                    null
                );
            }

            $statusCode = $response->getStatusCode();


            switch($statusCode) {
                case 200:
                    return $this->handleResponseWithDataType(
                        '\Kinde\KindeSDK\Model\Frontend\PortalLink',
                        $request,
                        $response,
                    );
            }

            

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(
                    sprintf(
                        '[%d] Error connecting to the API (%s)',
                        $statusCode,
                        (string) $request->getUri()
                    ),
                    $statusCode,
                    $response->getHeaders(),
                    (string) $response->getBody()
                );
            }

            return $this->handleResponseWithDataType(
                '\Kinde\KindeSDK\Model\Frontend\PortalLink',
                $request,
                $response,
            );
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 200:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        '\Kinde\KindeSDK\Model\Frontend\PortalLink',
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    throw $e;
            }
        

            throw $e;
        }
    }

    /**
     * Operation getPortalLinkAsync
     *
     * Get self-serve portal link
     *
     * @param  string|null $subnav The area of the portal you want the user to land on (optional)
     * @param  string|null $return_url The URL to redirect the user to after they have completed their actions in the portal. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPortalLink'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPortalLinkAsync($subnav = null, $return_url = null, string $contentType = self::contentTypes['getPortalLink'][0])
    {
        return $this->getPortalLinkAsyncWithHttpInfo($subnav, $return_url, $contentType)
            ->then(
                function ($response) {
                    return $response[0];
                }
            );
    }

    /**
     * Operation getPortalLinkAsyncWithHttpInfo
     *
     * Get self-serve portal link
     *
     * @param  string|null $subnav The area of the portal you want the user to land on (optional)
     * @param  string|null $return_url The URL to redirect the user to after they have completed their actions in the portal. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPortalLink'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function getPortalLinkAsyncWithHttpInfo($subnav = null, $return_url = null, string $contentType = self::contentTypes['getPortalLink'][0])
    {
        $returnType = '\Kinde\KindeSDK\Model\Frontend\PortalLink';
        $request = $this->getPortalLinkRequest($subnav, $return_url, $contentType);

        return $this->client
            ->sendAsync($request, $this->createHttpClientOption())
            ->then(
                function ($response) use ($returnType) {
                    if ($returnType === '\SplFileObject') {
                        $content = $response->getBody(); //stream goes to serializer
                    } else {
                        $content = (string) $response->getBody();
                        if ($returnType !== 'string') {
                            $content = json_decode($content);
                        }
                    }

                    return [
                        ObjectSerializer::deserialize($content, $returnType, []),
                        $response->getStatusCode(),
                        $response->getHeaders()
                    ];
                },
                function ($exception) {
                    $response = $exception->getResponse();
                    $statusCode = $response->getStatusCode();
                    throw new ApiException(
                        sprintf(
                            '[%d] Error connecting to the API (%s)',
                            $statusCode,
                            $exception->getRequest()->getUri()
                        ),
                        $statusCode,
                        $response->getHeaders(),
                        (string) $response->getBody()
                    );
                }
            );
    }

    /**
     * Create request for operation 'getPortalLink'
     *
     * @param  string|null $subnav The area of the portal you want the user to land on (optional)
     * @param  string|null $return_url The URL to redirect the user to after they have completed their actions in the portal. (optional)
     * @param  string $contentType The value for the Content-Type header. Check self::contentTypes['getPortalLink'] to see the possible values for this operation
     *
     * @throws \InvalidArgumentException
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getPortalLinkRequest($subnav = null, $return_url = null, string $contentType = self::contentTypes['getPortalLink'][0])
    {




        $resourcePath = '/account_api/v1/portal_link';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $subnav,
            'subnav', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);
        // query params
        $queryParams = array_merge($queryParams, ObjectSerializer::toQueryValue(
            $return_url,
            'return_url', // param base name
            'string', // openApiType
            'form', // style
            true, // explode
            false // required
        ) ?? []);




        $headers = $this->headerSelector->selectHeaders(
            ['application/json', ],
            $contentType,
            $multipart
        );

        // for model (json/xml)
        if (count($formParams) > 0) {
            if ($multipart) {
                $multipartContents = [];
                foreach ($formParams as $formParamName => $formParamValue) {
                    $formParamValueItems = is_array($formParamValue) ? $formParamValue : [$formParamValue];
                    foreach ($formParamValueItems as $formParamValueItem) {
                        $multipartContents[] = [
                            'name' => $formParamName,
                            'contents' => $formParamValueItem
                        ];
                    }
                }
                // for HTTP post (form)
                $httpBody = new MultipartStream($multipartContents);

            } elseif (stripos($headers['Content-Type'], 'application/json') !== false) {
                # if Content-Type contains "application/json", json_encode the form parameters
                $httpBody = \GuzzleHttp\Utils::jsonEncode($formParams);
            } else {
                // for HTTP post (form)
                $httpBody = ObjectSerializer::buildQuery($formParams);
            }
        }

        // this endpoint requires Bearer (JWT) authentication (access token)
        if (!empty($this->config->getAccessToken())) {
            $headers['Authorization'] = 'Bearer ' . $this->config->getAccessToken();
        }

        $defaultHeaders = [];
        if ($this->config->getUserAgent()) {
            $defaultHeaders['User-Agent'] = $this->config->getUserAgent();
        }

        $headers = array_merge(
            $defaultHeaders,
            $headerParams,
            $headers
        );

        $operationHost = $this->config->getHost();
        $query = ObjectSerializer::buildQuery($queryParams);
        return new Request(
            'GET',
            $operationHost . $resourcePath . ($query ? "?{$query}" : ''),
            $headers,
            $httpBody
        );
    }

    /**
     * Create http client option
     *
     * @throws \RuntimeException on file opening failure
     * @return array of http client options
     */
    protected function createHttpClientOption()
    {
        $options = [];
        if ($this->config->getDebug()) {
            $options[RequestOptions::DEBUG] = fopen($this->config->getDebugFile(), 'a');
            if (!$options[RequestOptions::DEBUG]) {
                throw new \RuntimeException('Failed to open the debug file: ' . $this->config->getDebugFile());
            }
        }

        return $options;
    }

    private function handleResponseWithDataType(
        string $dataType,
        RequestInterface $request,
        ResponseInterface $response
    ): array {
        if ($dataType === '\SplFileObject') {
            $content = $response->getBody(); //stream goes to serializer
        } else {
            $content = (string) $response->getBody();
            if ($dataType !== 'string') {
                try {
                    $content = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    throw new ApiException(
                        sprintf(
                            'Error JSON decoding server response (%s)',
                            $request->getUri()
                        ),
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $content
                    );
                }
            }
        }

        return [
            ObjectSerializer::deserialize($content, $dataType, []),
            $response->getStatusCode(),
            $response->getHeaders()
        ];
    }

    private function responseWithinRangeCode(
        string $rangeCode,
        int $statusCode
    ): bool {
        $left = (int) ($rangeCode[0].'00');
        $right = (int) ($rangeCode[0].'99');

        return $statusCode >= $left && $statusCode <= $right;
    }
}
