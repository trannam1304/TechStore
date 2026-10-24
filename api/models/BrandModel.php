<?php
/**
 * TechStore - Brand Model (MVC)
 * Đảm nhiệm toàn bộ logic truy vấn Thương hiệu (Brands) trực tiếp từ CSDL MySQL
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;

class BrandModel extends BaseModel {
    /**
     * Lấy danh sách toàn bộ thương hiệu
     */
    public static function all(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM brands ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tìm thương hiệu theo ID
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);
        return $brand ?: null;
    }

    /**
     * Tạo mới thương hiệu
     */
    public static function create(array $data): array {
        $name = $data['name'] ?? 'Hãng mới';
        $slug = $data['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $logo = $data['logo'] ?? null;
        $description = $data['description'] ?? null;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO brands (name, slug, logo, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $logo, $description]);
        $newId = (int)$pdo->lastInsertId();

        return self::findById($newId);
    }

    /**
     * Cập nhật thương hiệu
     */
    public static function update(int $id, array $data): ?array {
        $pdo = Database::getConnection();
        $fields = [];
        $params = [];
        if (isset($data['name'])) { $fields[] = "`name` = ?"; $params[] = $data['name']; }
        if (isset($data['slug'])) { $fields[] = "`slug` = ?"; $params[] = $data['slug']; }
        if (isset($data['logo'])) { $fields[] = "`logo` = ?"; $params[] = $data['logo']; }
        if (isset($data['description'])) { $fields[] = "`description` = ?"; $params[] = $data['description']; }

        if (!empty($fields)) {
            $params[] = $id;
            $stmt = $pdo->prepare("UPDATE brands SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($params);
        }

        return self::findById($id);
    }

    /**
     * Xóa thương hiệu
     */
    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
