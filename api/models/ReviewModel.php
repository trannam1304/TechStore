<?php
/**
 * TechStore - Review Model
 * Quản lý đánh giá sản phẩm trực tiếp từ CSDL MySQL và cập nhật rating trung bình
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;

class ReviewModel extends BaseModel {
    /**
     * Lấy danh sách đánh giá của sản phẩm
     */
    public static function getByProductId(int $productId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY id DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm đánh giá mới và tự động cập nhật rating sản phẩm
     */
    public static function createReview(int $productId, array $data): array {
        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');
        $rating = max(1, min(5, (int)($data['rating'] ?? 5)));
        $userName = $data['user_name'] ?? 'Khách hàng';
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : null;
        $comment = trim($data['comment'] ?? '');

        $sql = "INSERT INTO reviews (product_id, user_id, user_name, rating, comment, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'approved', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId, $userId, $userName, $rating, $comment, $now]);
        $reviewId = (int)$pdo->lastInsertId();

        // Cập nhật rating và review_count trong bảng products
        $statStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as cnt FROM reviews WHERE product_id = ? AND status = 'approved'");
        $statStmt->execute([$productId]);
        $stats = $statStmt->fetch(PDO::FETCH_ASSOC);

        if ($stats) {
            $avgRating = round((float)$stats['avg_rating'], 1);
            $cnt = (int)$stats['cnt'];
            $updateProd = $pdo->prepare("UPDATE products SET rating = ?, review_count = ? WHERE id = ?");
            $updateProd->execute([$avgRating, $cnt, $productId]);
        }

        return [
            'id' => $reviewId,
            'product_id' => $productId,
            'user_id' => $userId,
            'user_name' => $userName,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => $now
        ];
    }

    /**
     * BR09: Kiểm tra người dùng đã mua sản phẩm và đơn hàng ở trạng thái COMPLETED
     */
    public static function hasCompletedPurchase(int $userId, int $productId): bool {
        $pdo = Database::getConnection();
        $sql = "SELECT 1 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.user_id = ? 
                  AND oi.product_id = ? 
                  AND LOWER(o.status) = 'completed'
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * BR09: Kiểm tra người dùng đã từng gửi đánh giá cho sản phẩm này hay chưa
     */
    public static function hasUserReviewed(int $userId, int $productId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
        $stmt->execute([$userId, $productId]);
        return (bool)$stmt->fetchColumn();
    }
}
