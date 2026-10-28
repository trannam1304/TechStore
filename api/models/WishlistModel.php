<?php
/**
 * TechStore - Wishlist Model
 * Quản lý danh sách yêu thích trực tiếp từ CSDL MySQL
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;

class WishlistModel extends BaseModel {
    private static ?string $tableName = null;

    /**
     * Tự động nhận diện tên bảng yêu thích (ưu tiên chuẩn số nhiều `wishlists`, fallback số ít `wishlist`)
     */
    public static function getTableName(): string {
        if (self::$tableName === null) {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->query("SHOW TABLES LIKE 'wishlists'");
                self::$tableName = ($stmt && $stmt->fetch()) ? 'wishlists' : 'wishlist';
            } catch (\Throwable $e) {
                self::$tableName = 'wishlists';
            }
        }
        return self::$tableName;
    }

    /**
     * Lấy danh sách sản phẩm yêu thích của user kèm thông tin sản phẩm
     */
    public static function getWishlist(int $userId): array {
        $pdo = Database::getConnection();
        $table = self::getTableName();
        $sql = "SELECT p.*, w.created_at as favorited_at
                FROM {$table} w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sản phẩm vào danh sách yêu thích
     */
    public static function add(int $userId, int $productId): bool {
        $pdo = Database::getConnection();
        $table = self::getTableName();
        // Kiểm tra đã tồn tại chưa
        $check = $pdo->prepare("SELECT id FROM {$table} WHERE user_id = ? AND product_id = ? LIMIT 1");
        $check->execute([$userId, $productId]);
        if ($check->fetch()) {
            return true;
        }

        $stmt = $pdo->prepare("INSERT INTO {$table} (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $productId]);
    }

    /**
     * Xóa sản phẩm khỏi danh sách yêu thích
     */
    public static function remove(int $userId, int $productId): bool {
        $pdo = Database::getConnection();
        $table = self::getTableName();
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }
}
