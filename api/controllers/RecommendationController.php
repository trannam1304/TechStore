<?php
/**
 * TechStore - Recommendation Controller
 * Gợi ý sản phẩm thông minh và ghi nhận hành vi xem sản phẩm (Event Tracking) qua CSDL MySQL
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ProductModel.php';

use Api\Helpers\Response;
use Api\Models\ProductModel;
use Config\Database;
use PDO;
use Exception;

class RecommendationController extends BaseController {
    /**
     * POST /api/v1/events/view
     * Ghi nhận sự kiện xem sản phẩm vào MySQL (HTTP 202 Accepted)
     */
    public static function trackView(array $body): void {
        $prodId = (int)($body['product_id'] ?? 0);
        if ($prodId > 0) {
            try {
                $pdo = Database::getConnection();
                // Tăng view_count trong bảng products
                $stmt = $pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?");
                $stmt->execute([$prodId]);

                // Lưu nhật ký xem vào product_views
                $user = self::getCurrentAuthUser();
                $userId = $user ? (int)$user['id'] : null;
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $stmtView = $pdo->prepare("INSERT INTO product_views (product_id, user_id, ip_address, viewed_at) VALUES (?, ?, ?, NOW())");
                $stmtView->execute([$prodId, $userId, $ip]);
            } catch (Exception $e) {
                // Bỏ qua lỗi log view để không gián đoạn trải nghiệm người dùng
            }
        }
        Response::success(['tracked' => true, 'event' => 'product_view'], 202, 'Ghi nhận lượt xem');
    }

    /**
     * GET /api/v1/products/{id}/recommendations
     * Lấy gợi ý các sản phẩm tương tự và thường được mua cùng
     */
    public static function forProduct(int $productId): void {
        try {
            $pdo = Database::getConnection();

            // 1. Lấy sản phẩm tương tự từ bảng product_similarity
            $sqlSim = "SELECT p.*, ps.score 
                       FROM product_similarity ps
                       JOIN products p ON ps.similar_product_id = p.id
                       WHERE ps.product_id = ?
                       ORDER BY ps.score DESC LIMIT 4";
            $stmtSim = $pdo->prepare($sqlSim);
            $stmtSim->execute([$productId]);
            $similar = $stmtSim->fetchAll(PDO::FETCH_ASSOC);

            // Nếu chưa có trong bảng similarity, gợi ý theo cùng danh mục
            if (empty($similar)) {
                $prod = ProductModel::findById($productId);
                $catId = $prod ? (int)$prod['category_id'] : 1;
                $stmtCat = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY rating DESC LIMIT 4");
                $stmtCat->execute([$catId, $productId]);
                $similar = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
            }

            // 2. Gợi ý sản phẩm mua cùng
            $boughtTogetherStmt = $pdo->prepare("SELECT * FROM products WHERE id != ? ORDER BY view_count DESC LIMIT 2");
            $boughtTogetherStmt->execute([$productId]);
            $boughtTogether = $boughtTogetherStmt->fetchAll(PDO::FETCH_ASSOC);

            Response::success([
                'similar'         => $similar,
                'bought_together' => $boughtTogether
            ], 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi tải gợi ý: ' . $e->getMessage(), 'RECOMMENDATION_ERROR', 500);
        }
    }

    /**
     * GET /api/v1/recommendations/me
     * Gợi ý sản phẩm cá nhân hóa dành riêng cho khách hàng đang đăng nhập
     */
    public static function forMe(): void {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT * FROM products WHERE featured = 1 ORDER BY rating DESC LIMIT 6");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success(['for_you' => $items], 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi tải gợi ý: ' . $e->getMessage(), 'RECOMMENDATION_ERROR', 500);
        }
    }
}
