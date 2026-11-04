<?php
/**
 * TechStore - Category & Brand Controller (MVC)
 * Quản lý danh mục sản phẩm (Categories) và thương hiệu (Brands)
 * Đạt chuẩn RESTful Level 3 (HATEOAS, Cache-Control & ETag)
 * Ủy quyền toàn bộ xử lý nghiệp vụ & truy xuất CSDL cho CategoryModel và BrandModel
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';

use Api\Helpers\Response;
use Api\Models\CategoryModel;
use Api\Models\BrandModel;

class CategoryBrandController extends BaseController {
    /**
     * GET /api/v1/categories
     * Lấy danh sách danh mục sản phẩm (Cacheable 600s & HATEOAS)
     */
    public static function getCategories(): void {
        $links = [
            'self' => ['href' => '/api/v1/categories', 'method' => 'GET']
        ];
        Response::withCache(CategoryModel::all(), 600, 'Lấy danh sách ngành hàng thành công', null, $links);
    }

    /**
     * POST /api/v1/categories (Admin)
     * Thêm danh mục mới
     */
    public static function createCategory(array $body): void {
        $cat = CategoryModel::create($body);
        $catId = $cat['id'] ?? null;
        $links = $catId ? [
            'self'   => ['href' => "/api/v1/categories/{$catId}", 'method' => 'GET'],
            'update' => ['href' => "/api/v1/categories/{$catId}", 'method' => 'PUT'],
            'delete' => ['href' => "/api/v1/categories/{$catId}", 'method' => 'DELETE']
        ] : null;
        Response::success($cat, 201, 'Tạo danh mục thành công', null, $links);
    }

    /**
     * PUT /api/v1/categories/{id} (Admin)
     * Cập nhật danh mục
     */
    public static function updateCategory(int $id, array $body): void {
        $updated = CategoryModel::update($id, $body);
        if (!$updated) {
            Response::error('Không tìm thấy danh mục', 'NOT_FOUND', 404);
        }
        $links = [
            'self'   => ['href' => "/api/v1/categories/{$id}", 'method' => 'GET'],
            'delete' => ['href' => "/api/v1/categories/{$id}", 'method' => 'DELETE']
        ];
        Response::success($updated, 200, 'Cập nhật danh mục thành công', null, $links);
    }

    /**
     * DELETE /api/v1/categories/{id} (Admin)
     * Xóa danh mục
     */
    public static function deleteCategory(int $id): void {
        CategoryModel::delete($id);
        Response::success(null, 204);
    }

    /**
     * GET /api/v1/brands
     * Lấy danh sách thương hiệu công nghệ (Cacheable 600s & HATEOAS)
     */
    public static function getBrands(): void {
        $links = [
            'self' => ['href' => '/api/v1/brands', 'method' => 'GET']
        ];
        Response::withCache(BrandModel::all(), 600, 'Lấy danh sách thương hiệu thành công', null, $links);
    }

    /**
     * POST /api/v1/brands (Admin)
     * Thêm thương hiệu mới
     */
    public static function createBrand(array $body): void {
        $brand = BrandModel::create($body);
        $brandId = $brand['id'] ?? null;
        $links = $brandId ? [
            'self'   => ['href' => "/api/v1/brands/{$brandId}", 'method' => 'GET'],
            'update' => ['href' => "/api/v1/brands/{$brandId}", 'method' => 'PUT'],
            'delete' => ['href' => "/api/v1/brands/{$brandId}", 'method' => 'DELETE']
        ] : null;
        Response::success($brand, 201, 'Tạo thương hiệu thành công', null, $links);
    }

    /**
     * PUT /api/v1/brands/{id} (Admin)
     * Cập nhật thương hiệu
     */
    public static function updateBrand(int $id, array $body): void {
        $updated = BrandModel::update($id, $body);
        if (!$updated) {
            Response::error('Không tìm thấy hãng', 'NOT_FOUND', 404);
        }
        $links = [
            'self'   => ['href' => "/api/v1/brands/{$id}", 'method' => 'GET'],
            'delete' => ['href' => "/api/v1/brands/{$id}", 'method' => 'DELETE']
        ];
        Response::success($updated, 200, 'Cập nhật hãng thành công', null, $links);
    }

    /**
     * DELETE /api/v1/brands/{id} (Admin)
     * Xóa thương hiệu
     */
    public static function deleteBrand(int $id): void {
        BrandModel::delete($id);
        Response::success(null, 204);
    }
}
