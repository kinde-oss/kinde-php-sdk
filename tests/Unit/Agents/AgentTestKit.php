<?php

namespace Kinde\KindeSDK\Tests\Unit\Agents;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

/**
 * Shared fixtures for the agent tests: an RS256 key pair with its JWKS, and a Guzzle client
 * that answers from a queue and records every request it sent.
 */
trait AgentTestKit
{
    private static ?string $privateKey = null;
    private static array $jwks = [];

    /** @var array<int, array{request: \Psr\Http\Message\RequestInterface}> */
    private array $history = [];

    private static function keys(): void
    {
        if (self::$privateKey !== null) {
            return;
        }
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $pem);
        $rsa = openssl_pkey_get_details($key)['rsa'];
        self::$privateKey = $pem;
        self::$jwks = ['keys' => [[
            'kty' => 'RSA',
            'kid' => 'kid-1',
            'use' => 'sig',
            'alg' => 'RS256',
            'n' => JWT::urlsafeB64Encode($rsa['n']),
            'e' => JWT::urlsafeB64Encode($rsa['e']),
        ]]];
    }

    private static function token(array $claims = [], string $kid = 'kid-1'): string
    {
        self::keys();
        return JWT::encode($claims + [
            'iss' => 'https://acme.kinde.com',
            'aud' => ['https://api.example.com/mcp'],
            'sub' => 'kp_user',
            'client_id' => 'agent-client',
            'exp' => time() + 3600,
            'iat' => time(),
        ], self::$privateKey, 'RS256', $kid);
    }

    /**
     * @param array<int, Response|\Throwable> $responses
     */
    private function http(array $responses): Client
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));
        return new Client(['handler' => $stack]);
    }

    private static function json(array $body, int $status = 200, array $headers = []): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'] + $headers, (string) json_encode($body));
    }

    /**
     * @return array<string, string>
     */
    private function sentForm(int $index): array
    {
        parse_str((string) $this->history[$index]['request']->getBody(), $form);
        return $form;
    }

    private function sentUrl(int $index): string
    {
        return (string) $this->history[$index]['request']->getUri();
    }
}
