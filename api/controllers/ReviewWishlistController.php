<?php
/**
 * TechStore - Review & Wishlist Controller
 * Quản lý đánh giá sản phẩm (Reviews) và danh sách yêu thích (Wishlist)
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;

class ReviewWishlistController extends BaseController {
    /**
     * GET /api/v1/products/{id}/reviews
     * Lấy danh sách đánh giá của sản phẩm
     */
    public static function getProductReviews(int $productId): void {
        $db = self::getDb();
        $reviews = array_values(array_filter($db['reviews'] ?? [], fn($r) => (int)($r['product_id'] ?? 0) === $productId));
        Response::success($reviews, 200);
    }

    /**
     * POST /api/v1/products/{id}/reviews
     * Gửi đánh giá cho sản phẩm
     */
    public static function createReview(int $productId, array $body): void {
        $db = self::getDb();
        $rating = max(1, min(5, (int)($body['rating'] ?? 5)));
        $comment = trim($body['comment'] ?? '');
        if (!$comment) {
            Response::error('Vui lòng nhập nội dung đánh giá', 'VALIDATION_ERROR', 422);
        }

        $newReview = [
            'id' => count($db['reviews'] ?? []) + 1,
            'product_id' => $productId,
            'user_name' => $body['user_name'] ?? 'Bạn',
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d')
        ];
        $db['reviews'][] = $newReview;
        self::saveDb($db);

        Response::success($newReview, 201, 'Gửi đánh giá thành công');
    }

    /**
     * GET /api/v1/wishlist
     * Lấy danh sách các sản phẩm yêu thích của người dùng
     */
    public static function getWishlist(): void {
        $db = self::getDb();
        $wishlistProductIds = array_column($db['wishlist'] ?? [], 'product_id');
        $wishlistProducts = array_values(array_filter($db['products'] ?? [], fn($p) => in_array($p['id'], $wishlistProductIds)));
        Response::success($wishlistProducts, 200);
    }

    /**
     * POST /api/v1/wishlist
     * Thêm sản phẩm vào danh sách yêu thích
     */
    public static function addToWishlist(array $body): void {
        $db = self::getDb();
        $prodId = (int)($body['product_id'] ?? 0);
        $exists = false;
        foreach ($db['wishlist'] ?? [] as $w) {
            if ((int)($w['product_id'] ?? 0) === $prodId) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $db['wishlist'][] = [
                'id' => count($db['wishlist'] ?? []) + 1,
                'user_id' => 2,
                'product_id' => $prodId
            ];
            self::saveDb($db);
        }
        Response::success(['product_id' => $prodId, 'active' => true], 201, 'Đã thêm vào yêu thích');
    }

    /**
     * DELETE /api/v1/wishlist/{id}
     * Xóa sản phẩm khỏi danh sách yêu thích
     */
    public static function removeFromWishlist(int $productId): void {
        $db = self::getDb();
        $db['wishlist'] = array_values(array_filter($db['wishlist'] ?? [], fn($w) => (int)($w['product_id'] ?? 0) !== $productId));
        self::saveDb($db);
        Response::success(null, 204);
    }
}
