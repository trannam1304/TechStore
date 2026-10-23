<?php
/**
 * TechStore - Recommendation Service
 * Phụ trách: Nguyễn Thành Trung (Day 3: T35, T36, T39)
 * Thuật toán gợi ý sản phẩm liên quan (Co-occurrence & Category Based)
 */

namespace Api\Services;

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
}
