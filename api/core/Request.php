<?php
/**
 * TechStore - HTTP Request Abstraction
 * Encapsulates HTTP input, JSON body, query params, headers, and route parameters
 */

namespace Api\Core;

class Request {
    private string $method;
    private string $uri;
    private string $path;
    private array $queryParams;
    private array $body;
    private array $headers;
    private array $routeParams = [];
    private ?array $user = null;

    public function __construct() {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        // Support X-HTTP-Method-Override
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }

        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $rawPath = parse_url($this->uri, PHP_URL_PATH);

        // Strip /api/v1 or /api prefix
        $cleaned = preg_replace('#^/api(/v1)?#', '', $rawPath);
        $this->path = '/' . ltrim($cleaned, '/');
        if ($this->path === '') {
            $this->path = '/';
        }

        $this->queryParams = $_GET ?? [];

        // Parse JSON body or form data
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $rawInput = file_get_contents('php://input');
            $this->body = json_decode($rawInput, true) ?? [];
        } else {
            $this->body = $_POST ?? [];
            if (empty($this->body)) {
                $rawInput = file_get_contents('php://input');
                if ($rawInput) {
                    $json = json_decode($rawInput, true);
                    $this->body = is_array($json) ? $json : [];
                }
            }
        }

        $this->headers = $this->collectHeaders();
    }

    private function collectHeaders(): array {
        if (function_exists('getallheaders')) {
            $h = getallheaders();
            $res = [];
            foreach ($h as $k => $v) {
                $res[strtolower($k)] = $v;
            }
            return $res;
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$headerName] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerName = strtolower(str_replace('_', '-', $key));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function query(?string $key = null, $default = null) {
        if ($key === null) return $this->queryParams;
        return $this->queryParams[$key] ?? $default;
    }

    public function body(?string $key = null, $default = null) {
        if ($key === null) return $this->body;
        return $this->body[$key] ?? $default;
    }

    public function all(): array {
        return array_merge($this->queryParams, $this->body, $this->routeParams);
    }

    public function header(string $name, ?string $default = null): ?string {
        $normalized = strtolower($name);
        return $this->headers[$normalized] ?? $default;
    }

    public function getBearerToken(): ?string {
        $auth = $this->header('authorization');
        if (!$auth) return null;

        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    public function setRouteParams(array $params): void {
        $this->routeParams = $params;
    }

    public function param(string $key, $default = null) {
        return $this->routeParams[$key] ?? $default;
    }

    public function setUser(?array $user): void {
        $this->user = $user;
    }

    public function getUser(): ?array {
        return $this->user;
    }
}
