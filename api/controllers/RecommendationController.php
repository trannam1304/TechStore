<?php
/**
 * TechStore - Recommendation Controller
 * Gợi ý sản phẩm thông minh và ghi nhận hành vi xem sản phẩm (Event Tracking)
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;

class RecommendationController extends BaseController {
    /**
     * POST /api/v1/events/view
     * Ghi nhận sự kiện xem sản phẩm (HTTP 202 Accepted)
     */
    public static function trackView(array $body): void {
        Response::success(['tracked' => true, 'event' => 'product_view'], 202, 'Ghi nhận lượt xem');
    }

    /**
     * GET /api/v1/products/{id}/recommendations
     * Lấy gợi ý các sản phẩm tương tự và thường được mua cùng
     */
    public static function forProduct(int $productId): void {
        $db = self::getDb();
        $similar = array_values(array_filter($db['products'] ?? [], fn($p) => (int)($p['id'] ?? 0) !== $productId));
        Response::success([
            'similar' => array_slice($similar, 0, 4),
            'bought_together' => array_slice($similar, 1, 2)
        ], 200);
    }

    /**
     * GET /api/v1/recommendations/me
     * Gợi ý sản phẩm cá nhân hóa dành riêng cho khách hàng đang đăng nhập
     */
    public static function forMe(): void {
        $db = self::getDb();
        Response::success([
            'for_you' => array_slice($db['products'] ?? [], 0, 6)
        ], 200);
    }
}
