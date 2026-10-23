<?php
/**
 * TechStore - Product Controller (MVC)
 * Topic 1: RESTful API · PHP thuần
 * Đảm nhiệm điều phối HTTP request/response cho phân hệ Sản phẩm
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
     * Danh sách sản phẩm với bộ lọc đa tiêu chí, tìm kiếm, sắp xếp và phân trang
     */
    public static function index(array $query): void {
        $result = ProductModel::paginate($query);
        Response::paginate(
            $result['items'],
            $result['total'],
            $result['page'],
            $result['limit'],
            'Lấy danh sách sản phẩm thành công'
        );
    }

    /**
     * T14: GET /api/v1/products/{id}
     * Chi tiết sản phẩm kèm thông số kỹ thuật, bộ ảnh gallery và đánh giá tóm tắt
     */
    public static function show(int $id): void {
        $product = ProductModel::findById($id);
        if (!$product) {
            Response::error('Không tìm thấy sản phẩm yêu cầu', 'PRODUCT_NOT_FOUND', 404);
        }
        Response::success($product, 200);
    }

    /**
     * T14: GET /api/v1/categories
     * Lấy danh sách ngành hàng (ủy quyền cho CategoryModel)
     */
    public static function categories(): void {
        Response::success(CategoryModel::all(), 200);
    }

    /**
     * T14: GET /api/v1/brands
     * Lấy danh sách thương hiệu công nghệ (ủy quyền cho BrandModel)
     */
    public static function brands(): void {
        Response::success(BrandModel::all(), 200);
    }

    /**
     * T21: POST /api/v1/products (Admin)
     * Thêm sản phẩm mới
     */
    public static function create(array $body): void {
        $newProduct = ProductModel::create($body);
        Response::success($newProduct, 201, 'Tạo sản phẩm thành công');
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
        Response::success($updated, 200, 'Cập nhật sản phẩm thành công');
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
