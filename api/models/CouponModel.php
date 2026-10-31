<?php
/**
 * TechStore - Coupon Model
 * Quản lý mã giảm giá trực tiếp từ CSDL MySQL
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;

class CouponModel extends BaseModel {
    /**
     * Lấy tất cả mã giảm giá
     */
    public static function getAll(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM coupons ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm coupon theo mã
     */
    public static function findByCode(string $code): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE UPPER(code) = UPPER(?) LIMIT 1");
        $stmt->execute([trim($code)]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        return $coupon ?: null;
    }

    /**
     * Tăng số lần sử dụng của coupon
     */
    public static function incrementUsage(string $code): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE UPPER(code) = UPPER(?)");
        return $stmt->execute([trim($code)]);
    }
}
