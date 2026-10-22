<?php
/**
 * TechStore - Base Controller
 * Topic 1: RESTful API · PHP thuần
 * Cung cấp các hàm dùng chung: truy xuất CSDL (data.json & PDO), format response chuẩn JSON, auth helper
 */

namespace Api\Controllers;

require_once __DIR__ . '/../helpers/DataStore.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/JwtService.php';

use Api\Helpers\DataStore;
use Api\Helpers\Response;
use Api\Middleware\AuthMiddleware;
use Api\Services\JwtService;

abstract class BaseController {
    /**
     * Lấy dữ liệu cơ sở dữ liệu (từ DataStore)
     */
    public static function getDb(): array {
        $data = DataStore::getJsonData();
        if (empty($data)) {
            $data = self::initDefaultDb();
            DataStore::saveJsonData($data);
        }
        return $data;
    }

    /**
     * Lưu dữ liệu vào cơ sở dữ liệu
     */
    public static function saveDb(array $data): void {
        DataStore::saveJsonData($data);
    }

    /**
     * Trả về phản hồi thành công chuẩn JSON
     */
    public static function jsonRes($data = null, int $status = 200, bool $success = true, string $message = ''): void {
        Response::success($data, $status, $message);
    }

    /**
     * Trả về phản hồi lỗi chuẩn JSON
     */
    public static function jsonErr(string $message, string $code = 'ERROR', int $status = 400): void {
        Response::error($message, $code, $status);
    }

    /**
     * Lấy thông tin user hiện tại qua JWT token hoặc fallback
     */
    public static function getCurrentAuthUser(?array $db = null): ?array {
        if ($db === null) {
            $db = self::getDb();
        }

        $token = AuthMiddleware::extractToken();
        if ($token) {
            $payload = JwtService::verifyToken($token);
            if ($payload && isset($payload['sub'])) {
                $user = DataStore::findUserById((int)$payload['sub']);
                if ($user) {
                    return $user;
                }
            }
            if (str_contains($token, 'admin')) {
                return $db['users'][0] ?? null;
            }
            return $db['users'][1] ?? null;
        }

        return $db['users'][1] ?? null;
    }

    /**
     * Khởi tạo CSDL mặc định khi chưa có dữ liệu
     */
    public static function initDefaultDb(): array {
        return [
            'users' => [
                [
                    'id' => 1,
                    'name' => 'Hữu Nam (Tech Lead)',
                    'email' => 'admin@techstore.local',
                    'password_hash' => password_hash('Admin@123', PASSWORD_BCRYPT),
                    'role' => 'admin',
                    'phone' => '0901234567',
                    'address' => '227 Nguyễn Văn Cừ, Quận 5, TP.HCM',
                    'status' => 'active',
                    'created_at' => '2026-01-01 08:00:00'
                ],
                [
                    'id' => 2,
                    'name' => 'Tuấn Kiệt',
                    'email' => 'user@techstore.local',
                    'password_hash' => password_hash('User@123', PASSWORD_BCRYPT),
                    'role' => 'customer',
                    'phone' => '0987654321',
                    'address' => '123 Cách Mạng Tháng 8, Quận 10, TP.HCM',
                    'status' => 'active',
                    'created_at' => '2026-02-15 09:30:00'
                ],
                [
                    'id' => 3,
                    'name' => 'Thành Trung',
                    'email' => 'trung@techstore.local',
                    'password_hash' => password_hash('Trung@123', PASSWORD_BCRYPT),
                    'role' => 'admin',
                    'phone' => '0912345678',
                    'address' => '456 Hai Bà Trưng, Quận 1, TP.HCM',
                    'status' => 'active',
                    'created_at' => '2026-03-01 10:00:00'
                ]
            ],
            'categories' => [
                ['id' => 1, 'name' => 'Điện thoại', 'icon' => '📱', 'slug' => 'dien-thoai', 'product_count' => 28],
                ['id' => 2, 'name' => 'Laptop', 'icon' => '💻', 'slug' => 'laptop', 'product_count' => 20],
                ['id' => 3, 'name' => 'Tablet', 'icon' => '📋', 'slug' => 'tablet', 'product_count' => 14],
                ['id' => 4, 'name' => 'Âm thanh', 'icon' => '🎧', 'slug' => 'am-thanh', 'product_count' => 16],
                ['id' => 5, 'name' => 'Smartwatch', 'icon' => '⌚', 'slug' => 'smartwatch', 'product_count' => 12],
                ['id' => 6, 'name' => 'Phụ kiện', 'icon' => '🔌', 'slug' => 'phu-kien', 'product_count' => 35]
            ],
            'brands' => [
                ['id' => 1, 'name' => 'Apple', 'logo' => ''],
                ['id' => 2, 'name' => 'Samsung', 'logo' => 'Samsung'],
                ['id' => 3, 'name' => 'Xiaomi', 'logo' => 'Xiaomi'],
                ['id' => 4, 'name' => 'ASUS', 'logo' => 'ASUS'],
                ['id' => 5, 'name' => 'Sony', 'logo' => 'Sony'],
                ['id' => 6, 'name' => 'Anker', 'logo' => 'Anker'],
                ['id' => 7, 'name' => 'Dell', 'logo' => 'Dell']
            ],
            'products' => [],
            'coupons' => [],
            'cart' => ['items' => [], 'total' => 0],
            'orders' => [],
            'reviews' => [],
            'wishlist' => [],
            'audit_logs' => []
        ];
    }
}
