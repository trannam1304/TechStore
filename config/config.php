<?php
/**
 * TechStore - System Configuration
 * Loads settings from .env file or environment variables
 */

namespace Config;

class Config {
    private static array $settings = [];
    private static bool $loaded = false;

    public static function load(): void {
        if (self::$loaded) {
            return;
        }

        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }
                if (str_contains($line, '=')) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    // Remove quotes if present
                    $value = trim($value, "\"'");
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }

        self::$settings = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'TechStore',
                'env' => $_ENV['APP_ENV'] ?? 'development',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
            ],
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['DB_PORT'] ?? '3306',
                'name' => $_ENV['DB_NAME'] ?? 'techstore',
                'user' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
                'charset' => 'utf8mb4'
            ],
            'jwt' => [
                'secret' => $_ENV['JWT_SECRET'] ?? 'techstore_jwt_secret_key_2026_super_secure',
                'access_expiry' => (int)($_ENV['JWT_ACCESS_EXPIRY'] ?? 3600),
                'refresh_expiry' => (int)($_ENV['JWT_REFRESH_EXPIRY'] ?? 604800),
            ],
            'vietqr' => [
                'bank_id' => $_ENV['BANK_ID'] ?? 'MB',
                'account_no' => $_ENV['ACCOUNT_NO'] ?? '0123456789',
                'account_name' => $_ENV['ACCOUNT_NAME'] ?? 'NGUYEN HUU NAM',
                'template' => $_ENV['VIETQR_TEMPLATE'] ?? 'compact2',
            ],
            'cors' => [
                'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:8000,http://127.0.0.1:8000,*')
            ]
        ];

        self::$loaded = true;
    }

    public static function get(string $key, $default = null) {
        self::load();
        $parts = explode('.', $key);
        $data = self::$settings;

        foreach ($parts as $part) {
            if (!is_array($data) || !array_key_exists($part, $data)) {
                return $default;
            }
            $data = $data[$part];
        }

        return $data;
    }

    public static function all(): array {
        self::load();
        return self::$settings;
    }
}
