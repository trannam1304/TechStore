<?php
/**
 * TechStore - Category Model (MVC)
 * Đảm nhiệm toàn bộ logic truy vấn và biến đổi dữ liệu Danh mục sản phẩm (Categories)
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../helpers/DataStore.php';

use Api\Helpers\DataStore;
use PDO;
use Exception;

class CategoryModel extends BaseModel {
    /**
     * Lấy danh sách toàn bộ danh mục ngành hàng
     */
    public static function all(): array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
                $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($cats)) {
                    return $cats;
                }
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $data = DataStore::getJsonData();
        return $data['categories'] ?? [];
    }

    /**
     * Tìm danh mục theo ID
     */
    public static function findById(int $id): ?array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $cat = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($cat) {
                    return $cat;
                }
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $data = DataStore::getJsonData();
        foreach ($data['categories'] ?? [] as $c) {
            if ((int)$c['id'] === $id) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Tạo mới danh mục
     */
    public static function create(array $data): array {
        $name = $data['name'] ?? 'Danh mục mới';
        $icon = $data['icon'] ?? '📦';
        $slug = $data['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $pdo = DataStore::getPdo();
        $newId = null;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, icon, slug, product_count) VALUES (?, ?, ?, 0)");
                $stmt->execute([$name, $icon, $slug]);
                $newId = (int)$pdo->lastInsertId();
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        if (!$newId) {
            $newId = count($json['categories'] ?? []) ? max(array_column($json['categories'], 'id')) + 1 : 1;
        }

        $cat = [
            'id' => $newId,
            'name' => $name,
            'icon' => $icon,
            'slug' => $slug,
            'product_count' => 0
        ];
        $json['categories'][] = $cat;
        DataStore::saveJsonData($json);
        return $cat;
    }

    /**
     * Cập nhật thông tin danh mục
     */
    public static function update(int $id, array $data): ?array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $fields = [];
                $params = [];
                if (isset($data['name'])) { $fields[] = "`name` = ?"; $params[] = $data['name']; }
                if (isset($data['icon'])) { $fields[] = "`icon` = ?"; $params[] = $data['icon']; }
                if (isset($data['slug'])) { $fields[] = "`slug` = ?"; $params[] = $data['slug']; }
                if (!empty($fields)) {
                    $params[] = $id;
                    $stmt = $pdo->prepare("UPDATE categories SET " . implode(', ', $fields) . " WHERE id = ?");
                    $stmt->execute($params);
                }
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        $found = null;
        if (isset($json['categories'])) {
            foreach ($json['categories'] as &$c) {
                if ((int)$c['id'] === $id) {
                    if (isset($data['name'])) $c['name'] = $data['name'];
                    if (isset($data['icon'])) $c['icon'] = $data['icon'];
                    if (isset($data['slug'])) $c['slug'] = $data['slug'];
                    $found = $c;
                    break;
                }
            }
            if ($found) {
                DataStore::saveJsonData($json);
                return $found;
            }
        }

        if ($pdo) {
            return self::findById($id);
        }
        return null;
    }

    /**
     * Xóa danh mục
     */
    public static function delete(int $id): bool {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        if (isset($json['categories'])) {
            $json['categories'] = array_values(array_filter($json['categories'], fn($c) => (int)$c['id'] !== $id));
            DataStore::saveJsonData($json);
        }
        return true;
    }
}
