<?php
/**
 * TechStore - Product Controller (MVC)
 * Topic 1: RESTful API · PHP thuần
 * Đảm nhiệm điều phối HTTP request/response cho phân hệ Sản phẩm
 * Đạt chuẩn RESTful Level 3 (HATEOAS, Cache-Control & ETag)
 * Ủy quyền toàn bộ xử lý nghiệp vụ & truy xuất CSDL cho ProductModel, CategoryModel, BrandModel
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';

use Api\Helpers\Response;
use Api\Models\ProductModel;
use Api\Models\CategoryModel;
use Api\Models\BrandModel;

class ProductController extends BaseController {
    /**
     * T13: GET /api/v1/products
     * Danh sách sản phẩm với bộ lọc đa tiêu chí, tìm kiếm, sắp xếp và phân trang (Cacheable & HATEOAS)
     */
    public static function index(array $query = []): void {
        $result = ProductModel::paginate($query);
        Response::paginate(
            $result['items'],
            $result['total'],
            $result['page'],
            $result['limit'],
            'Lấy danh sách sản phẩm thành công',
            null,
            ['max_age' => 300, 'public' => true]
        );
    }

    /**
     * Alias getProducts cho index
     */
    public static function getProducts(array $query = []): void {
        self::index($query);
    }

    /**
     * T14: GET /api/v1/products/{id}
     * Chi tiết sản phẩm kèm thông số kỹ thuật, bộ ảnh gallery và đánh giá tóm tắt (HATEOAS & Cacheable)
     */
    public static function show(int $id): void {
        $product = ProductModel::findById($id);
        if (!$product) {
            Response::error('Không tìm thấy sản phẩm yêu cầu', 'PRODUCT_NOT_FOUND', 404);
        }

        $catId = $product['category_id'] ?? 1;
        $links = [
            'self'            => ['href' => "/api/v1/products/{$id}", 'method' => 'GET'],
            'collection'      => ['href' => '/api/v1/products', 'method' => 'GET'],
            'category'        => ['href' => "/api/v1/categories/{$catId}", 'method' => 'GET'],
            'reviews'         => ['href' => "/api/v1/products/{$id}/reviews", 'method' => 'GET'],
            'recommendations' => ['href' => "/api/v1/products/{$id}/recommendations", 'method' => 'GET'],
            'add_to_cart'     => ['href' => '/api/v1/cart/items', 'method' => 'POST']
        ];

        Response::withCache($product, 300, 'Lấy chi tiết sản phẩm thành công', null, $links);
    }

    /**
     * T14: GET /api/v1/categories
     * Lấy danh sách ngành hàng (ủy quyền cho CategoryModel)
     */
    public static function categories(): void {
        $links = [
            'self' => ['href' => '/api/v1/categories', 'method' => 'GET']
        ];
        Response::withCache(CategoryModel::all(), 600, 'Lấy danh sách ngành hàng thành công', null, $links);
    }

    /**
     * T14: GET /api/v1/brands
     * Lấy danh sách thương hiệu công nghệ (ủy quyền cho BrandModel)
     */
    public static function brands(): void {
        $links = [
            'self' => ['href' => '/api/v1/brands', 'method' => 'GET']
        ];
        Response::withCache(BrandModel::all(), 600, 'Lấy danh sách thương hiệu thành công', null, $links);
    }

    /**
     * T21: POST /api/v1/products (Admin)
     * Thêm sản phẩm mới
     */
    public static function create(array $body): void {
        $newProduct = ProductModel::create($body);
        $prodId = $newProduct['id'] ?? null;
        $links = $prodId ? [
            'self'   => ['href' => "/api/v1/products/{$prodId}", 'method' => 'GET'],
            'update' => ['href' => "/api/v1/products/{$prodId}", 'method' => 'PUT'],
            'delete' => ['href' => "/api/v1/products/{$prodId}", 'method' => 'DELETE']
        ] : null;
        Response::success($newProduct, 201, 'Tạo sản phẩm thành công', null, $links);
    }

    /**
     * T21: PUT/PATCH /api/v1/products/{id} (Admin)
     * Cập nhật sản phẩm
     */
    public static function update(int $id, array $body): void {
        $updated = ProductModel::update($id, $body);
        if (!$updated) {
            Response::error('Không tìm thấy sản phẩm', 'NOT_FOUND', 404);
        }
        $links = [
            'self'   => ['href' => "/api/v1/products/{$id}", 'method' => 'GET'],
            'delete' => ['href' => "/api/v1/products/{$id}", 'method' => 'DELETE']
        ];
        Response::success($updated, 200, 'Cập nhật sản phẩm thành công', null, $links);
    }

    /**
     * T21: DELETE /api/v1/products/{id} (Admin)
     * Xóa sản phẩm
     */
    public static function destroy(int $id): void {
        ProductModel::delete($id);
        Response::success(null, 204);
    }
}
