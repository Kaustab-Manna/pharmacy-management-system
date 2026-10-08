<?php
namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $json;
    private string $method;
    private string $uri;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Parse path info
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        // Strip script folder if running in subfolder
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptDir !== '/' && $scriptDir !== '\\' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        $this->uri = '/' . ltrim($uri, '/');

        // Parse JSON payload if sent
        $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
        $rawInput = file_get_contents('php://input');
        $isJson = (stripos($contentType, 'application/json') !== false) ||
                  (!empty($rawInput) && in_array(substr(trim($rawInput), 0, 1), ['{', '[']));
        $this->json = $isJson ? (json_decode($rawInput, true) ?? []) : [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));
    }

    public function get(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->get;
        return $this->get[$key] ?? $default;
    }

    public function post(string $key = null, mixed $default = null): mixed
    {
        $data = !empty($this->post) ? $this->post : $this->json;
        if ($key === null) return $data;
        return $data[$key] ?? $default;
    }

    public function json(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) return $this->json;
        return $this->json[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->json);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->json[$key] ?? ($this->post[$key] ?? ($this->get[$key] ?? $default));
    }

    public function getFile(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function getIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
}
