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
    /**
     * Lấy danh sách sản phẩm yêu thích của user kèm thông tin sản phẩm
     */
    public static function getWishlist(int $userId): array {
        $pdo = Database::getConnection();
        $sql = "SELECT p.*, w.created_at as favorited_at
                FROM wishlists w
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
        // Kiểm tra đã tồn tại chưa
        $check = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1");
        $check->execute([$userId, $productId]);
        if ($check->fetch()) {
            return true;
        }

        $stmt = $pdo->prepare("INSERT INTO wishlists (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $productId]);
    }

    /**
     * Xóa sản phẩm khỏi danh sách yêu thích
     */
    public static function remove(int $userId, int $productId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }
}
