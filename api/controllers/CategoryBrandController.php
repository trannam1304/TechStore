<?php
/**
 * TechStore - Category & Brand Controller (4 APIs)
 * Author: Nguyen Thanh Trung (Back-end & Algorithm Specialist)
 * Endpoints: GET/POST/PUT/DELETE /categories, GET/POST/DELETE /brands
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class CategoryBrandController extends BaseController {
    public function getCategories(Request $request): void {
        $categories = $this->model->query("SELECT * FROM categories ORDER BY id ASC");
        Response::json($categories);
    }

    public function getBrands(Request $request): void {
        $brands = $this->model->query("SELECT * FROM brands ORDER BY id ASC");
        Response::json($brands);
    }

    public function storeCategory(Request $request): void {
        $body = $request->body();
        $name = trim($body['name'] ?? '');
        $icon = $body['icon'] ?? '📱';
        $slug = $body['slug'] ?? strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

        if (!$name) {
            Response::error('Tên danh mục không được để trống', 'VALIDATION_ERROR', 422);
        }

        $this->model->execute(
            "INSERT INTO categories (name, icon, slug, product_count, created_at) VALUES (:name, :icon, :slug, 0, NOW())",
            ['name' => $name, 'icon' => $icon, 'slug' => $slug]
        );

        $newId = $this->model->lastInsertId();
        $cat = $this->model->queryOne("SELECT * FROM categories WHERE id = :id", ['id' => $newId]);
        Response::json($cat, 201, true, 'Thêm danh mục thành công');
    }

    public function deleteCategory(Request $request): void {
        $id = (int)$request->param('id');
        $this->model->execute("DELETE FROM categories WHERE id = :id", ['id' => $id]);
        Response::json(null, 204, true, 'Đã xóa danh mục');
    }

    public function storeBrand(Request $request): void {
        $body = $request->body();
        $name = trim($body['name'] ?? '');
        $logo = $body['logo'] ?? $name;

        if (!$name) {
            Response::error('Tên thương hiệu không được để trống', 'VALIDATION_ERROR', 422);
        }

        $this->model->execute(
            "INSERT INTO brands (name, logo, created_at) VALUES (:name, :logo, NOW())",
            ['name' => $name, 'logo' => $logo]
        );

        $newId = $this->model->lastInsertId();
        $brand = $this->model->queryOne("SELECT * FROM brands WHERE id = :id", ['id' => $newId]);
        Response::json($brand, 201, true, 'Thêm thương hiệu thành công');
    }

    public function deleteBrand(Request $request): void {
        $id = (int)$request->param('id');
        $this->model->execute("DELETE FROM brands WHERE id = :id", ['id' => $id]);
        Response::json(null, 204, true, 'Đã xóa thương hiệu');
    }
}
