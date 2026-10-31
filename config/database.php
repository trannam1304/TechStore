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

    /**
     * Khởi tạo kết nối PDO với cấu hình từ .env hoặc mặc định
     */
    private static function connect(): void {
        self::loadEnv(__DIR__ . '/../.env');

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'techstore';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Tự động thử cổng MySQL thay thế (3306 <-> 3307) nếu cổng cấu hình bị từ chối kết nối
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
            error_log("[Database Connection Error] " . $e->getMessage());
            throw new PDOException("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage(), (int)$e->getCode());
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
                if (!getenv($key)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
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
