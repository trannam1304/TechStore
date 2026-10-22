<?php
/**
 * TechStore - Review & Wishlist Controller (4 APIs)
 * Author: Nguyen Thanh Trung (Back-end & Algorithm Specialist)
 * Endpoints: GET/POST /products/{id}/reviews, GET/POST/DELETE /wishlist
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class ReviewWishlistController extends BaseController {
    public function getReviews(Request $request): void {
        $productId = (int)$request->param('id');
        $reviews = $this->model->query(
            "SELECT * FROM reviews WHERE product_id = :pid AND status = 'approved' ORDER BY id DESC",
            ['pid' => $productId]
        );
        Response::json($reviews);
    }

    public function storeReview(Request $request): void {
        $user = $this->requireAuthUser($request);
        $productId = (int)$request->param('id');
        $body = $request->body();

        $rating = max(1, min(5, (int)($body['rating'] ?? 5)));
        $comment = trim($body['comment'] ?? '');

        if (!$comment) {
            Response::error('Vui lòng nhập nội dung đánh giá', 'MISSING_COMMENT', 400);
        }

        // Kiểm tra xem khách đã mua sản phẩm này chưa (theo yêu cầu đề tài)
        $hasBought = $this->model->queryOne(
            "SELECT oi.id FROM order_items oi
             JOIN orders o ON oi.order_id = o.id
             WHERE o.user_id = :uid AND oi.product_id = :pid",
            ['uid' => $user['sub'], 'pid' => $productId]
        );

        if (!$hasBought && ($user['role'] ?? '') !== 'admin') {
            Response::error('Chỉ những khách hàng đã mua sản phẩm này mới có quyền gửi đánh giá', 'NOT_PURCHASED', 403);
        }

        $this->model->execute(
            "INSERT INTO reviews (product_id, user_id, user_name, rating, comment, status, created_at)
             VALUES (:pid, :uid, :name, :rating, :cmt, 'approved', NOW())",
            [
                'pid' => $productId,
                'uid' => $user['sub'],
                'name' => $user['name'] ?? 'Khách hàng',
                'rating' => $rating,
                'cmt' => $comment
            ]
        );

        // Recalculate product rating
        $avg = $this->model->queryOne(
            "SELECT AVG(rating) as avg_rating, COUNT(*) as cnt FROM reviews WHERE product_id = :pid AND status = 'approved'",
            ['pid' => $productId]
        );
        if ($avg) {
            $this->model->execute(
                "UPDATE products SET rating = :rating, review_count = :cnt WHERE id = :pid",
                ['rating' => round((float)$avg['avg_rating'], 1), 'cnt' => (int)$avg['cnt'], 'pid' => $productId]
            );
        }

        Response::json(['message' => 'Gửi đánh giá sản phẩm thành công'], 201, true, 'Đánh giá thành công');
    }

    public function getWishlist(Request $request): void {
        $user = $this->requireAuthUser($request);
        $items = $this->model->query(
            "SELECT p.*, w.id as wishlist_id FROM wishlist w
             JOIN products p ON w.product_id = p.id
             WHERE w.user_id = :uid ORDER BY w.id DESC",
            ['uid' => $user['sub']]
        );
        Response::json($items);
    }

    public function toggleWishlist(Request $request): void {
        $user = $this->requireAuthUser($request);
        $body = $request->body();
        $productId = (int)($body['product_id'] ?? 0);

        if (!$productId) {
            Response::error('Thiếu mã sản phẩm', 'VALIDATION_ERROR', 422);
        }

        $existing = $this->model->queryOne(
            "SELECT id FROM wishlist WHERE user_id = :uid AND product_id = :pid",
            ['uid' => $user['sub'], 'pid' => $productId]
        );

        if ($existing) {
            $this->model->execute("DELETE FROM wishlist WHERE id = :id", ['id' => $existing['id']]);
            Response::json(['saved' => false, 'message' => 'Đã xóa khỏi danh sách yêu thích']);
        } else {
            $this->model->execute(
                "INSERT INTO wishlist (user_id, product_id, created_at) VALUES (:uid, :pid, NOW())",
                ['uid' => $user['sub'], 'pid' => $productId]
            );
            Response::json(['saved' => true, 'message' => 'Đã thêm vào danh sách yêu thích'], 201);
        }
    }

    public function deleteWishlist(Request $request): void {
        $user = $this->requireAuthUser($request);
        $body = $request->body();
        $productId = (int)($body['product_id'] ?? ($request->param('id') ?? 0));

        if ($productId) {
            $this->model->execute(
                "DELETE FROM wishlist WHERE user_id = :uid AND product_id = :pid",
                ['uid' => $user['sub'], 'pid' => $productId]
            );
        }
        Response::json(null, 204, true, 'Đã xóa khỏi yêu thích');
    }
}
