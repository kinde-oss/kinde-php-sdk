<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Transport for the agent client. Every URL must be https (plain http only on loopback),
 * redirects are followed by hand so each hop is re-checked, and response bodies are capped.
 *
 * @internal
 */
final class AgentHttp
{
    private const MAX_REDIRECTS = 5;
    private const MAX_RESPONSE_BYTES = 1048576;
    private const LOOPBACK_HOSTS = ['localhost', '127.0.0.1', '[::1]', '::1'];
    private const PRIVATE_SUFFIXES = ['.localhost', '.local', '.internal', '.localdomain', '.home.arpa'];

    private ClientInterface $http;
    private float $timeout;

    public function __construct(ClientInterface $http, float $timeout = 10.0)
    {
        $this->http = $http;
        $this->timeout = $timeout;
    }

    /**
     * @return array{0: int, 1: array<string, mixed>|null, 2: ResponseInterface}
     */
    public function getJson(string $url, string $label, bool $allowPrivateNetworks = true): array
    {
        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            self::assertAllowedUrl($url, $label, $allowPrivateNetworks);
            $response = $this->send('GET', $url, ['headers' => ['Accept' => 'application/json']], $label);
            $status = $response->getStatusCode();
            if (!in_array($status, [301, 302, 303, 307, 308], true)) {
                return [$status, $this->decode($response, $label), $response];
            }
            $location = $response->getHeaderLine('Location');
            if ($location === '') {
                throw new AgentAuthException('discovery_failed', "{$label} redirected without a Location header.", $status);
            }
            $url = (string) \GuzzleHttp\Psr7\UriResolver::resolve(new \GuzzleHttp\Psr7\Uri($url), new \GuzzleHttp\Psr7\Uri($location));
        }
        throw new AgentAuthException('discovery_failed', "{$label} redirected more than " . self::MAX_REDIRECTS . ' times.');
    }

    /**
     * @param array<string, string> $form
     * @return array{0: int, 1: array<string, mixed>|null}
     */
    public function postForm(string $url, array $form, string $label): array
    {
        self::assertAllowedUrl($url, $label, true);
        $response = $this->send('POST', $url, ['form_params' => $form, 'headers' => ['Accept' => 'application/json']], $label);
        return [$response->getStatusCode(), $this->decode($response, $label)];
    }

    /**
     * @param array<string, mixed> $body
     * @return array{0: int, 1: array<string, mixed>|null}
     */
    public function postJson(string $url, array $body, string $label): array
    {
        self::assertAllowedUrl($url, $label, true);
        $response = $this->send('POST', $url, ['json' => $body, 'headers' => ['Accept' => 'application/json']], $label);
        return [$response->getStatusCode(), $this->decode($response, $label)];
    }

    public static function assertAllowedUrl(string $url, string $label, bool $allowPrivateNetworks): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || !($scheme === 'https' || ($scheme === 'http' && in_array($host, self::LOOPBACK_HOSTS, true)))) {
            throw new AgentAuthException('insecure_url', "{$label} must be an absolute https URL, but was '{$url}'.");
        }
        if (!$allowPrivateNetworks && self::isPrivateHost($host)) {
            throw new AgentAuthException('private_network_blocked', "{$label} points to a private or local address.");
        }
    }

    public static function isPrivateHost(string $host): bool
    {
        $host = rtrim(strtolower(trim($host, '[]')), '.');
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }
        if (strpos($host, '.') === false) {
            return true;
        }
        foreach (self::PRIVATE_SUFFIXES as $suffix) {
            if (substr($host, -strlen($suffix)) === $suffix) {
                return true;
            }
        }
        return false;
    }

    private function send(string $method, string $url, array $options, string $label): ResponseInterface
    {
        try {
            return $this->http->request($method, $url, $options + [
                'timeout' => $this->timeout,
                'connect_timeout' => $this->timeout,
                'allow_redirects' => false,
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new AgentAuthException('network_error', "{$label} request failed: " . $e->getMessage(), null, $e);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(ResponseInterface $response, string $label): ?array
    {
        $body = $response->getBody();
        $text = '';
        while (!$body->eof() && strlen($text) <= self::MAX_RESPONSE_BYTES) {
            $chunk = $body->read(8192);
            if ($chunk === '') {
                break;
            }
            $text .= $chunk;
        }
        if (strlen($text) > self::MAX_RESPONSE_BYTES) {
            throw new AgentAuthException('response_too_large', "{$label} response exceeded " . self::MAX_RESPONSE_BYTES . ' bytes.');
        }
        $data = json_decode($text, true);
        return is_array($data) ? $data : null;
    }
}
