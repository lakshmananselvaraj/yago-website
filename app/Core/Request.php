<?php

namespace App\Core;

final class Request
{
    private array $query;
    private array $body;
    private array $server;
    private array $json;

    public function __construct()
    {
        $this->query = $_GET;
        $this->server = $_SERVER;

        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input') ?: '';
            $decoded = json_decode($raw, true);
            $this->json = is_array($decoded) ? $decoded : [];
            $this->body = $this->json;
        } else {
            $this->json = [];
            $this->body = $_POST;
        }
    }

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST' && isset($this->body['_method'])) {
            return strtoupper((string) $this->body['_method']);
        }

        return $method;
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return rtrim($path, '/') ?: '/';
    }

    /**
     * Path + query string (e.g. "/booking/schedule?instructor_id=5"), used to
     * remember where an anonymous visitor was headed before being bounced to
     * /login so they can be sent back after authenticating.
     */
    public function fullPath(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . str_replace('-', '_', strtoupper($name));

        return $this->server[$key] ?? null;
    }

    public function isJsonRequest(): bool
    {
        return str_contains($this->header('Accept') ?? '', 'application/json')
            || str_contains($this->server['CONTENT_TYPE'] ?? '', 'application/json');
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return substr((string) ($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }
}
