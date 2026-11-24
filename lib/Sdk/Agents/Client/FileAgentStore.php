<?php

namespace Kinde\KindeSDK\Sdk\Agents\Client;

/**
 * Stores each key in its own file, readable only by the current user. Suits CLI agents and
 * single-server deployments; use a shared store when several servers act as the same agent.
 */
final class FileAgentStore implements AgentStore
{
    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    public function get(string $key): ?string
    {
        $path = $this->path($key);
        if (!is_file($path)) {
            return null;
        }
        $value = file_get_contents($path);
        return $value === false ? null : $value;
    }

    public function set(string $key, string $value): void
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new \RuntimeException("Could not create the agent store directory {$this->directory}.");
        }
        $path = $this->path($key);
        // Written to a private temporary file and renamed, so a reader never sees half a record
        // and the secret is never briefly world-readable.
        $temporary = tempnam($this->directory, 'kinde');
        if ($temporary === false) {
            throw new \RuntimeException("Could not write to the agent store directory {$this->directory}.");
        }
        chmod($temporary, 0600);
        if (file_put_contents($temporary, $value) === false || !rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException("Could not write {$path}.");
        }
    }

    public function remove(string $key): void
    {
        $path = $this->path($key);
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function path(string $key): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    }
}
