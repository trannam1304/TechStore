<?php
/**
 * TechStore - Recommendation Controller
 * Gợi ý sản phẩm thông minh và ghi nhận hành vi xem sản phẩm (Event Tracking) qua CSDL MySQL
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../services/RecommendService.php';

use Api\Helpers\Response;
use Api\Models\ProductModel;
use Api\Services\RecommendService;
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

            // 2. Gợi ý sản phẩm mua cùng (Thuật toán đa tầng: Apriori + Hệ sinh thái + Bổ trợ danh mục)
            $boughtTogether = RecommendService::getBoughtTogether($productId, 2);

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
     * Gợi ý sản phẩm cá nhân hóa dành riêng cho khách hàng đang đăng nhập (FR09.03 & BR12)
     * Công thức lai: Score = 0.4 * ContentSim + 0.4 * CoOccurrence + 0.2 * Popularity
     * Tự động fallback sang sản phẩm nhiều lượt xem & đánh giá cao nếu là Cold-start
     */
    public static function forMe(): void {
        try {
            $pdo = Database::getConnection();
            $user = self::getCurrentAuthUser();
            $seedProductIds = [];

            if ($user && !empty($user['id'])) {
                $userId = (int)$user['id'];

                // 1. Lấy sản phẩm khách đã xem gần đây (tối đa 10)
                $stmtView = $pdo->prepare("SELECT product_id FROM product_views WHERE user_id = ? ORDER BY viewed_at DESC LIMIT 10");
                $stmtView->execute([$userId]);
                $viewedIds = $stmtView->fetchAll(PDO::FETCH_COLUMN);

                // 2. Lấy sản phẩm khách đã từng mua (tối đa 10)
                $stmtOrder = $pdo->prepare("
                    SELECT DISTINCT oi.product_id 
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.user_id = ? AND o.status != 'cancelled'
                    ORDER BY o.created_at DESC LIMIT 10
                ");
                $stmtOrder->execute([$userId]);
                $boughtIds = $stmtOrder->fetchAll(PDO::FETCH_COLUMN);

                $seedProductIds = array_values(array_unique(array_merge($viewedIds, $boughtIds)));
            }

            // Trường hợp có lịch sử tương tác: Tính điểm Hybrid Scoring
            if (!empty($seedProductIds)) {
                $placeholders = implode(',', array_fill(0, count($seedProductIds), '?'));

                // Lấy max view_count để chuẩn hóa Popularity
                $maxViews = (float)$pdo->query("SELECT MAX(view_count) FROM products WHERE status = 'active'")->fetchColumn() ?: 1.0;

                $sql = "
                    SELECT p.*,
                           COALESCE(MAX(CASE WHEN ps.algorithm = 'cosine' THEN ps.score ELSE 0 END), 0) AS content_sim,
                           COALESCE(MAX(CASE WHEN ps.algorithm = 'apriori' THEN ps.score ELSE 0 END), 0) AS cooccur_score
                    FROM products p
                    LEFT JOIN product_similarity ps 
                           ON ps.similar_product_id = p.id 
                          AND ps.product_id IN ({$placeholders})
                    WHERE p.status = 'active'
                    GROUP BY p.id
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($seedProductIds);
                $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($candidates as &$cand) {
                    $contentSim = (float)$cand['content_sim'];
                    $cooccur = (float)$cand['cooccur_score'];
                    $pop = (0.5 * min(1.0, (float)$cand['view_count'] / $maxViews)) + (0.5 * min(1.0, (float)$cand['rating'] / 5.0));
                    $cand['hybrid_score'] = round((0.4 * $contentSim) + (0.4 * $cooccur) + (0.2 * $pop), 4);
                }
                unset($cand);

                usort($candidates, fn($a, $b) => $b['hybrid_score'] <=> $a['hybrid_score']);
                $items = array_slice($candidates, 0, 6);

                Response::success([
                    'for_you'         => $items,
                    'is_personalized' => true,
                    'algorithm'       => 'hybrid_score (0.4*Content + 0.4*CoOccur + 0.2*Popularity)'
                ], 200, 'Gợi ý cá nhân hóa thành công');
                return;
            }

            // Trường hợp Cold-start (chưa có lịch sử hoặc khách vãng lai): Fallback sang sản phẩm xem nhiều & rating cao nhất
            $stmt = $pdo->query("
                SELECT * FROM products 
                WHERE status = 'active' 
                ORDER BY (view_count * 0.5 + rating * 100) DESC 
                LIMIT 6
            ");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Response::success([
                'for_you'         => $items,
                'is_personalized' => false,
                'algorithm'       => 'cold_start_fallback (popularity & rating)'
            ], 200, 'Gợi ý sản phẩm nổi bật thành công');
        } catch (Exception $e) {
            Response::error('Lỗi khi tải gợi ý: ' . $e->getMessage(), 'RECOMMENDATION_ERROR', 500);
        }
    }
}
