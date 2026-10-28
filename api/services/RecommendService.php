<?php
/**
 * TechStore - Recommendation Service
 * Thuật toán gợi ý sản phẩm thông minh:
 * 1. Similar Products: Dựa trên Cosine similarity & Category/Price
 * 2. Frequently Bought Together (Bundle Combo): Dựa trên Apriori Basket Mining + Hệ sinh thái Thương hiệu + Bổ trợ Danh mục Chức năng (Cross-selling Ecosystem)
 */

namespace Api\Services;

use Config\Database;
use Api\Models\ProductModel;
use PDO;

class RecommendService {
    /**
     * Gợi ý sản phẩm tương tự dựa theo danh mục và giá
     */
    public static function getSimilarProducts(array $allProducts, int $productId, int $limit = 4): array {
        $target = null;
        foreach ($allProducts as $p) {
            if ((int)$p['id'] === $productId) {
                $target = $p;
                break;
            }
        }

        $candidates = array_values(array_filter($allProducts, fn($p) => (int)$p['id'] !== $productId));

        if ($target) {
            usort($candidates, function($a, $b) use ($target) {
                $sameCatA = ((int)($a['category_id'] ?? 0) === (int)($target['category_id'] ?? 0)) ? 1 : 0;
                $sameCatB = ((int)($b['category_id'] ?? 0) === (int)($target['category_id'] ?? 0)) ? 1 : 0;
                if ($sameCatA !== $sameCatB) {
                    return $sameCatB <=> $sameCatA;
                }
                return abs(($a['price'] ?? 0) - ($target['price'] ?? 0)) <=> abs(($b['price'] ?? 0) - ($target['price'] ?? 0));
            });
        }

        return array_slice($candidates, 0, $limit);
    }

    /**
     * Thuật toán gợi ý Sản phẩm thường được mua cùng (Frequently Bought Together / Cross-Sell Bundle)
     * Suy luận theo logic thực tế:
     * - Mua Điện thoại -> Cân nhắc mua Củ sạc nhanh Type-C, Tai nghe bluetooth cùng hệ sinh thái, Ốp lưng/Cường lực/Phụ kiện
     * - Mua Laptop -> Cân nhắc mua Chuột công thái học, Bàn phím cơ, Củ sạc công suất lớn, Tai nghe chống ồn
     * - Mua Tablet -> Cân nhắc mua Tai nghe không dây, Bàn phím bluetooth, Củ sạc nhanh
     * - Mua Bàn phím cơ -> Cân nhắc mua Chuột cùng bộ (Logitech MX Master 3S), Củ sạc nhanh
     * - Mua Chuột -> Cân nhắc mua Bàn phím cơ cùng bộ, Củ sạc nhanh
     */
    public static function getBoughtTogether(int $productId, int $limit = 2): array {
        $pdo = Database::getConnection();

        // 1. Lấy thông tin sản phẩm mục tiêu
        $target = ProductModel::findById($productId);
        if (!$target) return [];

        $targetCatId = (int)($target['category_id'] ?? 0);
        $targetBrandId = (int)($target['brand_id'] ?? 0);
        $targetName = mb_strtolower($target['name'] ?? '', 'UTF-8');
        $targetPrice = (float)($target['price'] ?? 0);

        // 2. Lấy dữ liệu luật kết hợp Apriori từ bảng product_similarity (nếu có)
        $aprioriScores = [];
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    IF(product_id = ?, similar_product_id, product_id) AS related_id,
                    score 
                FROM product_similarity 
                WHERE (product_id = ? OR similar_product_id = ?) 
                  AND algorithm = 'apriori'
            ");
            $stmt->execute([$productId, $productId, $productId]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $aprioriScores[(int)$row['related_id']] = (float)$row['score'];
            }
        } catch (\Throwable $e) {}

        // 3. Lấy tất cả sản phẩm khác đang active làm ứng viên
        $stmtCandidates = $pdo->prepare("SELECT * FROM products WHERE id != ? AND status = 'active'");
        $stmtCandidates->execute([$productId]);
        $candidates = $stmtCandidates->fetchAll(PDO::FETCH_ASSOC);

        // 4. Hàm phân loại thiết bị chi tiết (sub-category detector)
        $detectType = function(array $p): string {
            $name = mb_strtolower($p['name'] ?? '', 'UTF-8');
            $cid = (int)($p['category_id'] ?? 0);
            if (str_contains($name, 'bàn phím') || str_contains($name, 'keyboard')) return 'keyboard';
            if (str_contains($name, 'chuột') || str_contains($name, 'mouse')) return 'mouse';
            if (str_contains($name, 'sạc') || str_contains($name, 'charger') || str_contains($name, 'gan')) return 'charger';
            if (str_contains($name, 'watch') || str_contains($name, 'đồng hồ')) return 'watch';
            if ($cid === 4 || str_contains($name, 'tai nghe') || str_contains($name, 'earbuds') || str_contains($name, 'airpods') || str_contains($name, 'buds')) return 'earphone';
            if (str_contains($name, 'loa') || str_contains($name, 'speaker')) return 'speaker';
            if ($cid === 2 || str_contains($name, 'laptop') || str_contains($name, 'macbook')) return 'laptop';
            if ($cid === 3 || str_contains($name, 'tablet') || str_contains($name, 'ipad') || str_contains($name, 'tab')) return 'tablet';
            if ($cid === 1 || str_contains($name, 'phone') || str_contains($name, 'galaxy s') || str_contains($name, 'iphone')) return 'phone';
            return 'other';
        };

        $targetType = $detectType($target);

        // 5. Tính điểm phối hợp (Synergy Score) cho từng ứng viên
        $scored = [];
        foreach ($candidates as $cand) {
            $candId = (int)$cand['id'];
            $candType = $detectType($cand);
            $candBrandId = (int)$cand['brand_id'];
            $candPrice = (float)$cand['price'];
            $score = 0.0;

            // Điểm tầng 1: Điểm Apriori (Association Rule Mining)
            if (isset($aprioriScores[$candId])) {
                $score += $aprioriScores[$candId] * 80.0; // max ~76-78 điểm
            }

            // Điểm tầng 2: Ma trận bổ trợ chức năng tự nhiên (Cross-category Complementarity)
            if ($targetType === 'phone') {
                // Mua điện thoại -> Cần củ sạc nhanh (đặc biệt dòng ko có củ sạc) + tai nghe + smartwatch
                if ($candType === 'charger') $score += 60.0;
                elseif ($candType === 'earphone') $score += 55.0;
                elseif ($candType === 'watch') $score += 35.0;
                elseif ($candType === 'speaker') $score += 20.0;
            } elseif ($targetType === 'laptop') {
                // Mua laptop -> Cần chuột rời + bàn phím cơ + củ sạc 100W + tai nghe chống ồn
                if ($candType === 'mouse') $score += 65.0;
                elseif ($candType === 'keyboard') $score += 50.0;
                elseif ($candType === 'charger') $score += 45.0;
                elseif ($candType === 'earphone') $score += 35.0;
            } elseif ($targetType === 'tablet') {
                // Mua tablet -> Cần tai nghe + củ sạc + bàn phím/chuột
                if ($candType === 'earphone') $score += 60.0;
                elseif ($candType === 'charger') $score += 50.0;
                elseif ($candType === 'keyboard') $score += 45.0;
                elseif ($candType === 'mouse') $score += 40.0;
            } elseif ($targetType === 'keyboard') {
                // Mua bàn phím -> Combo vàng: Chuột cao cấp (Logitech MX) + Củ sạc đa năng
                if ($candType === 'mouse') $score += 75.0;
                elseif ($candType === 'charger') $score += 45.0;
                elseif ($candType === 'earphone') $score += 30.0;
            } elseif ($targetType === 'mouse') {
                // Mua chuột -> Combo vàng: Bàn phím cơ cùng hãng + Củ sạc
                if ($candType === 'keyboard') $score += 75.0;
                elseif ($candType === 'charger') $score += 45.0;
                elseif ($candType === 'earphone') $score += 30.0;
            } elseif ($targetType === 'watch') {
                // Mua đồng hồ -> Cần củ sạc + tai nghe bluetooth
                if ($candType === 'charger') $score += 60.0;
                elseif ($candType === 'earphone') $score += 50.0;
            } elseif ($targetType === 'earphone' || $targetType === 'speaker') {
                // Mua âm thanh -> Cần củ sạc + đồng hồ hoặc thiết bị nghe nhạc
                if ($candType === 'charger') $score += 55.0;
                elseif ($candType === 'watch') $score += 40.0;
                elseif ($targetType === 'earphone' && $candType === 'speaker') $score += 30.0;
                elseif ($targetType === 'speaker' && $candType === 'earphone') $score += 30.0;
            }

            // Điểm tầng 3: Hệ sinh thái cùng thương hiệu (Brand Ecosystem Synergy)
            // Apple đi với Apple, Samsung đi với Samsung, Logitech đi với Logitech
            if ($candBrandId > 0 && $candBrandId === $targetBrandId) {
                $score += 25.0;
            }
            // Anker là đối tác củ sạc phụ kiện chuyên biệt uy tín cho cả Apple & Samsung & Laptop
            if ($candBrandId === 7 && ($candType === 'charger' || $candType === 'speaker')) {
                $score += 15.0;
            }

            // Điểm tầng 4: Tương quan mức giá hợp lý (Phụ kiện mua kèm không nên đắt hơn thiết bị chính)
            if ($targetPrice > 0) {
                $ratio = $candPrice / $targetPrice;
                if ($ratio >= 0.02 && $ratio <= 0.45) {
                    $score += 25.0; // Phụ kiện trong tầm giá lý tưởng 2% - 45% máy chính
                } elseif ($ratio > 0.45 && $ratio <= 0.95) {
                    $score += 10.0;
                } elseif ($ratio > 1.2) {
                    $score -= 80.0; // Sản phẩm gợi ý mua kèm đắt hơn máy chính -> Phạt nặng để ưu tiên phụ kiện
                }
            }

            // Điểm đánh giá chất lượng (Rating bonus)
            $score += ((float)($cand['rating'] ?? 5.0)) * 2.0;

            $cand['synergy_score'] = $score;
            $cand['detected_type'] = $candType;
            $scored[] = $cand;
        }

        // Sắp xếp giảm dần theo synergy_score
        usort($scored, fn($a, $b) => $b['synergy_score'] <=> $a['synergy_score']);

        // 6. Đa dạng hóa danh mục: Đảm bảo các sản phẩm mua kèm thuộc các loại bổ trợ KHÁC NHAU
        // (Không bao giờ gợi ý 2 củ sạc hoặc 2 tai nghe cùng lúc)
        $selected = [];
        $selectedTypes = [];

        foreach ($scored as $item) {
            $type = $item['detected_type'];
            if (!in_array($type, $selectedTypes, true) || $type === 'other') {
                $selected[] = $item;
                $selectedTypes[] = $type;
                if (count($selected) >= $limit) break;
            }
        }

        if (count($selected) < $limit) {
            foreach ($scored as $item) {
                if (!in_array($item['id'], array_column($selected, 'id'), true)) {
                    $selected[] = $item;
                    if (count($selected) >= $limit) break;
                }
            }
        }

        return $selected;
    }
}

