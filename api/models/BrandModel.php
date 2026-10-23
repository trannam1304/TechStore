<?php
/**
 * TechStore - Brand Model (MVC)
 * Đảm nhiệm toàn bộ logic truy vấn và biến đổi dữ liệu Thương hiệu (Brands)
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../helpers/DataStore.php';

use Api\Helpers\DataStore;
use PDO;
use Exception;

class BrandModel extends BaseModel {
    /**
     * Lấy danh sách toàn bộ thương hiệu
     */
    public static function all(): array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->query("SELECT * FROM brands ORDER BY id ASC");
                $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($brands)) {
                    return $brands;
                }
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $data = DataStore::getJsonData();
        return $data['brands'] ?? [];
    }

    /**
     * Tìm thương hiệu theo ID
     */
    public static function findById(int $id): ?array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $brand = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($brand) {
                    return $brand;
                }
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $data = DataStore::getJsonData();
        foreach ($data['brands'] ?? [] as $b) {
            if ((int)$b['id'] === $id) {
                return $b;
            }
        }
        return null;
    }

    /**
     * Tạo mới thương hiệu
     */
    public static function create(array $data): array {
        $name = $data['name'] ?? 'Hãng mới';
        $logo = $data['logo'] ?? '🏷️';

        $pdo = DataStore::getPdo();
        $newId = null;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO brands (name, logo) VALUES (?, ?)");
                $stmt->execute([$name, $logo]);
                $newId = (int)$pdo->lastInsertId();
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        if (!$newId) {
            $newId = count($json['brands'] ?? []) ? max(array_column($json['brands'], 'id')) + 1 : 1;
        }

        $brand = [
            'id' => $newId,
            'name' => $name,
            'logo' => $logo
        ];
        $json['brands'][] = $brand;
        DataStore::saveJsonData($json);
        return $brand;
    }

    /**
     * Cập nhật thương hiệu
     */
    public static function update(int $id, array $data): ?array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $fields = [];
                $params = [];
                if (isset($data['name'])) { $fields[] = "`name` = ?"; $params[] = $data['name']; }
                if (isset($data['logo'])) { $fields[] = "`logo` = ?"; $params[] = $data['logo']; }
                if (!empty($fields)) {
                    $params[] = $id;
                    $stmt = $pdo->prepare("UPDATE brands SET " . implode(', ', $fields) . " WHERE id = ?");
                    $stmt->execute($params);
                }
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        $found = null;
        if (isset($json['brands'])) {
            foreach ($json['brands'] as &$b) {
                if ((int)$b['id'] === $id) {
                    if (isset($data['name'])) $b['name'] = $data['name'];
                    if (isset($data['logo'])) $b['logo'] = $data['logo'];
                    $found = $b;
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
     * Xóa thương hiệu
     */
    public static function delete(int $id): bool {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
                $stmt->execute([$id]);
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        if (isset($json['brands'])) {
            $json['brands'] = array_values(array_filter($json['brands'], fn($b) => (int)$b['id'] !== $id));
            DataStore::saveJsonData($json);
        }
        return true;
    }
}
