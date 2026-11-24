<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

/**
 * Durable storage for an agent's client registration, pending authorization and tokens. Values
 * are JSON strings holding secrets: back it with something private to the agent.
 */
interface AgentStore
{
    public function get(string $key): ?string;

    public function set(string $key, string $value): void;

    public function remove(string $key): void;
}
