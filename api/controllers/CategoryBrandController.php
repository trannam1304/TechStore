<?php
/**
 * TechStore - Category & Brand Controller
 * Quản lý danh mục sản phẩm (Categories) và thương hiệu (Brands)
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;
use Api\Helpers\DataStore;
use PDO;
use Exception;

class CategoryBrandController extends BaseController {
    /**
     * GET /api/v1/categories
     * Lấy danh sách danh mục sản phẩm
     */
    public static function getCategories(): void {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
                $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success($cats, 200);
                return;
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $db = self::getDb();
        Response::success($db['categories'] ?? [], 200);
    }

    /**
     * POST /api/v1/categories (Admin)
     * Thêm danh mục mới
     */
    public static function createCategory(array $body): void {
        $db = self::getDb();
        $newId = count($db['categories']) ? max(array_column($db['categories'], 'id')) + 1 : 1;
        $cat = [
            'id' => $newId,
            'name' => $body['name'] ?? 'Danh mục mới',
            'icon' => $body['icon'] ?? '📦',
            'slug' => $body['slug'] ?? 'danh-muc-moi',
            'product_count' => 0
        ];
        $db['categories'][] = $cat;
        self::saveDb($db);
        Response::success($cat, 201, 'Tạo danh mục thành công');
    }

    /**
     * PUT /api/v1/categories/{id} (Admin)
     * Cập nhật danh mục
     */
    public static function updateCategory(int $id, array $body): void {
        $db = self::getDb();
        foreach ($db['categories'] as &$c) {
            if ($c['id'] === $id) {
                $c['name'] = $body['name'] ?? $c['name'];
                $c['icon'] = $body['icon'] ?? $c['icon'];
                if (isset($body['slug'])) $c['slug'] = $body['slug'];
                self::saveDb($db);
                Response::success($c, 200, 'Cập nhật danh mục thành công');
            }
        }
        Response::error('Không tìm thấy danh mục', 'NOT_FOUND', 404);
    }

    /**
     * DELETE /api/v1/categories/{id} (Admin)
     * Xóa danh mục
     */
    public static function deleteCategory(int $id): void {
        $db = self::getDb();
        $db['categories'] = array_values(array_filter($db['categories'], fn($c) => $c['id'] !== $id));
        self::saveDb($db);
        Response::success(null, 204);
    }

    /**
     * GET /api/v1/brands
     * Lấy danh sách thương hiệu công nghệ
     */
    public static function getBrands(): void {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT * FROM brands ORDER BY id ASC");
                $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success($brands, 200);
                return;
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $db = self::getDb();
        Response::success($db['brands'] ?? [], 200);
    }

    /**
     * POST /api/v1/brands (Admin)
     * Thêm thương hiệu mới
     */
    public static function createBrand(array $body): void {
        $db = self::getDb();
        $newId = count($db['brands']) ? max(array_column($db['brands'], 'id')) + 1 : 1;
        $brand = [
            'id' => $newId,
            'name' => $body['name'] ?? 'Hãng mới',
            'logo' => $body['logo'] ?? '🏷️'
        ];
        $db['brands'][] = $brand;
        self::saveDb($db);
        Response::success($brand, 201, 'Tạo thương hiệu thành công');
    }

    /**
     * PUT /api/v1/brands/{id} (Admin)
     * Cập nhật thương hiệu
     */
    public static function updateBrand(int $id, array $body): void {
        $db = self::getDb();
        foreach ($db['brands'] as &$b) {
            if ($b['id'] === $id) {
                $b['name'] = $body['name'] ?? $b['name'];
                $b['logo'] = $body['logo'] ?? $b['logo'];
                self::saveDb($db);
                Response::success($b, 200, 'Cập nhật hãng thành công');
            }
        }
        Response::error('Không tìm thấy hãng', 'NOT_FOUND', 404);
    }

    /**
     * DELETE /api/v1/brands/{id} (Admin)
     * Xóa thương hiệu
     */
    public static function deleteBrand(int $id): void {
        $db = self::getDb();
        $db['brands'] = array_values(array_filter($db['brands'], fn($b) => $b['id'] !== $id));
        self::saveDb($db);
        Response::success(null, 204);
    }
}
