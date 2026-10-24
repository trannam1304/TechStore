<?php
/**
 * TechStore - Review & Wishlist Controller
 * Quản lý đánh giá sản phẩm (Reviews) và danh sách yêu thích (Wishlist) thuần MySQL
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ReviewModel.php';
require_once __DIR__ . '/../models/WishlistModel.php';

use Api\Helpers\Response;
use Api\Models\ReviewModel;
use Api\Models\WishlistModel;
use Exception;

class ReviewWishlistController extends BaseController {
    /**
     * GET /api/v1/products/{id}/reviews
     * Lấy danh sách đánh giá của sản phẩm
     */
    public static function getProductReviews(int $productId): void {
        try {
            $reviews = ReviewModel::getByProductId($productId);
            Response::success($reviews, 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi lấy đánh giá: ' . $e->getMessage(), 'REVIEW_ERROR', 500);
        }
    }

    /**
     * POST /api/v1/products/{id}/reviews
     * Gửi đánh giá cho sản phẩm
     */
    public static function createReview(int $productId, array $body): void {
        $comment = trim($body['comment'] ?? '');
        if (!$comment) {
            Response::error('Vui lòng nhập nội dung đánh giá', 'VALIDATION_ERROR', 422);
            return;
        }

        $user = self::getCurrentAuthUser();
        $reviewData = [
            'rating'    => max(1, min(5, (int)($body['rating'] ?? 5))),
            'comment'   => $comment,
            'user_name' => $body['user_name'] ?? ($user['name'] ?? 'Khách hàng'),
            'user_id'   => $user ? (int)$user['id'] : null
        ];

        try {
            $newReview = ReviewModel::createReview($productId, $reviewData);
            Response::success($newReview, 201, 'Gửi đánh giá thành công');
        } catch (Exception $e) {
            Response::error('Lỗi khi lưu đánh giá: ' . $e->getMessage(), 'REVIEW_SAVE_ERROR', 500);
        }
    }

    /**
     * GET /api/v1/wishlist
     * Lấy danh sách các sản phẩm yêu thích của người dùng
     */
    public static function getWishlist(): void {
        $user = self::getCurrentAuthUser();
        if (!$user) {
            Response::error('Vui lòng đăng nhập để xem danh sách yêu thích', 'UNAUTHORIZED', 401);
            return;
        }

        try {
            $products = WishlistModel::getWishlist((int)$user['id']);
            Response::success($products, 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi lấy danh sách yêu thích: ' . $e->getMessage(), 'WISHLIST_ERROR', 500);
        }
    }

    /**
     * POST /api/v1/wishlist
     * Thêm sản phẩm vào danh sách yêu thích
     */
    public static function addToWishlist(array $body): void {
        $user = self::getCurrentAuthUser();
        if (!$user) {
            Response::error('Vui lòng đăng nhập để thêm vào yêu thích', 'UNAUTHORIZED', 401);
            return;
        }

        $prodId = (int)($body['product_id'] ?? 0);
        if ($prodId <= 0) {
            Response::error('ID sản phẩm không hợp lệ', 'VALIDATION_ERROR', 422);
            return;
        }

        try {
            WishlistModel::add((int)$user['id'], $prodId);
            Response::success(['product_id' => $prodId], 201, 'Đã thêm vào danh sách yêu thích');
        } catch (Exception $e) {
            Response::error('Lỗi khi thêm vào yêu thích: ' . $e->getMessage(), 'WISHLIST_ERROR', 500);
        }
    }

    /**
     * DELETE /api/v1/wishlist/{productId}
     * Xóa sản phẩm khỏi danh sách yêu thích
     */
    public static function removeFromWishlist(int $productId): void {
        $user = self::getCurrentAuthUser();
        if (!$user) {
            Response::error('Vui lòng đăng nhập để thao tác', 'UNAUTHORIZED', 401);
            return;
        }

        try {
            WishlistModel::remove((int)$user['id'], $productId);
            Response::success(['product_id' => $productId], 200, 'Đã xóa khỏi danh sách yêu thích');
        } catch (Exception $e) {
            Response::error('Lỗi khi xóa khỏi yêu thích: ' . $e->getMessage(), 'WISHLIST_ERROR', 500);
        }
    }
}
