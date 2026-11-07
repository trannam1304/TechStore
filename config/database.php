<?php
/**
 * TechStore - Quản lý kết nối cơ sở dữ liệu (Database Connection)
 * Topic 1: RESTful API · PHP, MySQL, HTML/CSS/JS
 * Cung cấp kết nối PDO Singleton, tự động đọc biến môi trường từ file .env
 */

namespace Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    /**
     * Lấy kết nối PDO Singleton
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    private static function getEnvVal(string $key, ?string $default = null): ?string {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string)$_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string)$_SERVER[$key];
        }
        return $default;
    }

    /**
     * Khởi tạo kết nối PDO với cấu hình từ .env hoặc mặc định
     */
    private static function connect(): void {
        self::loadEnv(__DIR__ . '/../.env');

        $host = self::getEnvVal('DB_HOST', '127.0.0.1');
        $port = self::getEnvVal('DB_PORT', '3306');
        $dbname = self::getEnvVal('DB_NAME', 'test');
        $username = self::getEnvVal('DB_USER', 'root');
        $password = self::getEnvVal('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        // Hỗ trợ kết nối bảo mật SSL / TLS cho Cloud Database (TiDB Cloud, Aiven, v.v.)
        $caBundle = ini_get('openssl.cafile') ?: ini_get('curl.cainfo');
        if (!$caBundle || !file_exists($caBundle)) {
            $candidates = [
                'C:\\xampp\\apache\\bin\\curl-ca-bundle.crt',
                'C:\\xampp\\perl\\vendor\\lib\\Mozilla\\CA\\cacert.pem',
                '/etc/ssl/certs/ca-certificates.crt',
                '/etc/pki/tls/certs/ca-bundle.crt',
                '/etc/ssl/ca-bundle.pem',
                '/etc/ssl/cert.pem'
            ];
            foreach ($candidates as $cand) {
                if (file_exists($cand)) {
                    $caBundle = $cand;
                    break;
                }
            }
        }

        if (defined('PDO::MYSQL_ATTR_SSL_CA') && $caBundle && file_exists($caBundle)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caBundle;
        }
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Tự động thử cổng MySQL thay thế (3306 <-> 3307) nếu chạy localhost bị từ chối kết nối
            if ($host === '127.0.0.1' || $host === 'localhost') {
                $altPort = ($port === '3307') ? '3306' : (($port === '3306') ? '3307' : null);
                if ($altPort !== null) {
                    try {
                        $altDsn = "mysql:host={$host};port={$altPort};dbname={$dbname};charset=utf8mb4";
                        self::$instance = new PDO($altDsn, $username, $password, $options);
                        return;
                    } catch (\Throwable $altEx) {
                        // Tiếp tục ném lỗi gốc nếu cả 2 cổng đều thất bại
                    }
                }
            }
            error_log("[Database Connection Error] " . $e->getMessage());
            throw new PDOException("Lỗi kết nối cơ sở dữ liệu Cloud/Local: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Helper đọc file .env thủ công (không cần thư viện composer bên thứ 3)
     */
    private static function loadEnv(string $path): void {
        if (!file_exists($path)) {
            $examplePath = $path . '.example';
            if (file_exists($examplePath)) {
                $path = $examplePath;
            } else {
                return;
            }
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * Đóng kết nối
     */
    public static function close(): void {
        self::$instance = null;
    }
}
