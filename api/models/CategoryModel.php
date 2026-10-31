<?php
/**
 * TechStore - Category Model (MVC)
 * Đảm nhiệm toàn bộ logic truy vấn Danh mục sản phẩm (Categories) trực tiếp từ CSDL MySQL
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;

class CategoryModel extends BaseModel {
    /**
     * Lấy danh sách toàn bộ danh mục ngành hàng kèm số lượng sản phẩm
     */
    public static function all(): array {
        $pdo = Database::getConnection();
        $sql = "SELECT c.*, COUNT(p.id) as actual_product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY c.id ASC";
        $stmt = $pdo->query($sql);
        $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cats as &$cat) {
            $cat['product_count'] = (int)($cat['actual_product_count'] ?? $cat['product_count'] ?? 0);
            unset($cat['actual_product_count']);
        }
        return $cats;
    }

    /**
     * Tìm danh mục theo ID
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);
        return $cat ?: null;
    }

    /**
     * Tạo mới danh mục
     */
    public static function create(array $data): array {
        $name = $data['name'] ?? 'Danh mục mới';
        $icon = $data['icon'] ?? '📱';
        $slug = $data['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $description = $data['description'] ?? null;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO categories (name, icon, slug, description, product_count) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$name, $icon, $slug, $description]);
        $newId = (int)$pdo->lastInsertId();

        return self::findById($newId);
    }

    /**
     * Cập nhật thông tin danh mục
     */
    public static function update(int $id, array $data): ?array {
        $pdo = Database::getConnection();
        $fields = [];
        $params = [];
        if (isset($data['name'])) { $fields[] = "`name` = ?"; $params[] = $data['name']; }
        if (isset($data['icon'])) { $fields[] = "`icon` = ?"; $params[] = $data['icon']; }
        if (isset($data['slug'])) { $fields[] = "`slug` = ?"; $params[] = $data['slug']; }
        if (isset($data['description'])) { $fields[] = "`description` = ?"; $params[] = $data['description']; }

        if (!empty($fields)) {
            $params[] = $id;
            $stmt = $pdo->prepare("UPDATE categories SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($params);
        }

        return self::findById($id);
    }

    /**
     * Xóa danh mục
     */
    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
