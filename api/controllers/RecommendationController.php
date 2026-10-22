<?php
/**
 * TechStore - Recommendation Controller
 * Phụ trách: Nguyễn Thành Trung (Day 3: T35, T36, T39)
 * Tái cấu trúc từ logic monolith ban đầu. Thuật toán chi tiết sẽ viết tại Day 3.
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class RecommendationController extends BaseController {
    /**
     * GET /products/{id}/recommendations
     * Logic ban đầu từ monolith: lấy danh sách các sản phẩm khác làm mock
     */
    public function forProduct(Request $request): void {
        $id = (int)$request->param('id');
        $all = $this->model->query("SELECT * FROM products WHERE id != :id LIMIT 8", ['id' => $id]);

        Response::json([
            'product_id' => $id,
            'similar' => array_slice($all, 0, 4),
            'bought_together' => array_slice($all, 4, 3)
        ]);
    }

    /**
     * GET /recommendations/me
     * Logic ban đầu từ monolith: lấy danh sách sản phẩm mẫu làm mock
     */
    public function forMe(Request $request): void {
        $items = $this->model->query("SELECT * FROM products LIMIT 6");

        Response::json([
            'for_you' => $items,
            'items' => $items
        ]);
    }
}
