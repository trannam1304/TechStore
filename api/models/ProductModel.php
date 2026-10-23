<?php
/**
 * TechStore - Product Model (MVC)
 * Đảm nhiệm toàn bộ logic truy vấn, phân trang, lọc đa tiêu chí và lưu trữ Sản phẩm (Products)
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../helpers/DataStore.php';

use Api\Helpers\DataStore;
use PDO;
use Exception;

class ProductModel extends BaseModel {
    /**
     * Lấy danh sách sản phẩm có phân trang, lọc đa tiêu chí và sắp xếp
     */
    public static function paginate(array $query): array {
        $search = strtolower(trim($query['search'] ?? $query['q'] ?? ''));
        $catId = isset($query['category_id']) && $query['category_id'] !== '' ? (int)$query['category_id'] : null;
        $brandId = isset($query['brand_id']) && $query['brand_id'] !== '' ? (int)$query['brand_id'] : null;
        $minPrice = isset($query['min_price']) && is_numeric($query['min_price']) ? (float)$query['min_price'] : null;
        $maxPrice = isset($query['max_price']) && is_numeric($query['max_price']) ? (float)$query['max_price'] : null;
        $ram = trim($query['ram'] ?? '');
        $rom = trim($query['rom'] ?? '');
        $sort = $query['sort'] ?? 'featured';
        $page = max(1, (int)($query['page'] ?? 1));
        $limit = max(1, min(100, (int)($query['limit'] ?? 12)));

        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $where = ["1=1"];
                $params = [];

                if ($search !== '') {
                    $where[] = "(LOWER(p.name) LIKE :search OR LOWER(p.category_name) LIKE :search OR LOWER(p.brand_name) LIKE :search OR LOWER(p.chipset) LIKE :search)";
                    $params[':search'] = "%{$search}%";
                }
                if ($catId !== null) {
                    $where[] = "p.category_id = :cat_id";
                    $params[':cat_id'] = $catId;
                }
                if ($brandId !== null) {
                    $where[] = "p.brand_id = :brand_id";
                    $params[':brand_id'] = $brandId;
                }
                if ($minPrice !== null) {
                    $where[] = "p.price >= :min_price";
                    $params[':min_price'] = $minPrice;
                }
                if ($maxPrice !== null) {
                    $where[] = "p.price <= :max_price";
                    $params[':max_price'] = $maxPrice;
                }
                if ($ram !== '') {
                    $where[] = "LOWER(p.ram) LIKE :ram";
                    $params[':ram'] = "%" . strtolower($ram) . "%";
                }
                if ($rom !== '') {
                    $where[] = "LOWER(p.rom) LIKE :rom";
                    $params[':rom'] = "%" . strtolower($rom) . "%";
                }

                $whereSql = implode(' AND ', $where);

                // Đếm tổng số bản ghi
                $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE {$whereSql}");
                $countStmt->execute($params);
                $totalItems = (int)$countStmt->fetchColumn();

                // Xác định thứ tự sắp xếp
                $orderSql = match ($sort) {
                    'price_asc'   => "p.price ASC",
                    'price_desc'  => "p.price DESC",
                    'rating_desc' => "p.rating DESC",
                    'newest'      => "p.created_at DESC",
                    default       => "p.featured DESC, p.id ASC"
                };

                $offset = ($page - 1) * $limit;
                $sql = "SELECT p.* FROM products p WHERE {$whereSql} ORDER BY {$orderSql} LIMIT {$limit} OFFSET {$offset}";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Format JSON images cho từng sản phẩm
                foreach ($items as &$item) {
                    if (isset($item['images']) && is_string($item['images'])) {
                        $item['images'] = json_decode($item['images'], true) ?? [];
                    }
                }

                return [
                    'items' => $items,
                    'total' => $totalItems,
                    'page'  => $page,
                    'limit' => $limit
                ];
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        // FALLBACK: Đọc từ data.json
        $data = DataStore::getJsonData();
        $items = $data['products'] ?? [];

        // Áp dụng bộ lọc bộ nhớ
        if ($search !== '') {
            $items = array_filter($items, function($p) use ($search) {
                return str_contains(strtolower($p['name'] ?? ''), $search)
                    || str_contains(strtolower($p['category_name'] ?? ''), $search)
                    || str_contains(strtolower($p['brand_name'] ?? ''), $search);
            });
        }
        if ($catId !== null) {
            $items = array_filter($items, fn($p) => (int)($p['category_id'] ?? 0) === $catId);
        }
        if ($brandId !== null) {
            $items = array_filter($items, fn($p) => (int)($p['brand_id'] ?? 0) === $brandId);
        }
        if ($minPrice !== null) {
            $items = array_filter($items, fn($p) => (float)($p['price'] ?? 0) >= $minPrice);
        }
        if ($maxPrice !== null) {
            $items = array_filter($items, fn($p) => (float)($p['price'] ?? 0) <= $maxPrice);
        }
        if ($ram !== '') {
            $items = array_filter($items, fn($p) => str_contains(strtolower($p['ram'] ?? ''), strtolower($ram)));
        }
        if ($rom !== '') {
            $items = array_filter($items, fn($p) => str_contains(strtolower($p['rom'] ?? ''), strtolower($rom)));
        }

        $items = array_values($items);
        $totalItems = count($items);

        // Sắp xếp
        usort($items, function($a, $b) use ($sort) {
            return match ($sort) {
                'price_asc'   => ($a['price'] ?? 0) <=> ($b['price'] ?? 0),
                'price_desc'  => ($b['price'] ?? 0) <=> ($a['price'] ?? 0),
                'rating_desc' => ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0),
                'newest'      => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''),
                default       => (($b['featured'] ?? 0) <=> ($a['featured'] ?? 0)) ?: (($a['id'] ?? 0) <=> ($b['id'] ?? 0))
            };
        });

        // Phân trang
        $offset = ($page - 1) * $limit;
        $items = array_slice($items, $offset, $limit);

        return [
            'items' => $items,
            'total' => $totalItems,
            'page'  => $page,
            'limit' => $limit
        ];
    }

    /**
     * Tìm chi tiết sản phẩm theo ID kèm gallery, thông số kỹ thuật và đánh giá
     */
    public static function findById(int $id): ?array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    return null;
                }

                // Cập nhật lượt xem (view_count)
                try {
                    $pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
                    $product['view_count'] = (int)($product['view_count'] ?? 0) + 1;
                } catch (Exception $e) {}

                // Lấy danh sách ảnh từ product_images
                try {
                    $imgStmt = $pdo->prepare("SELECT image_url, is_primary, display_order FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC");
                    $imgStmt->execute([$id]);
                    $product['gallery'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $product['gallery'] = [];
                }

                if (!empty($product['images']) && is_string($product['images'])) {
                    $product['images'] = json_decode($product['images'], true) ?? [];
                }

                // Lấy danh sách thông số kỹ thuật từ product_specs
                try {
                    $specStmt = $pdo->prepare("SELECT spec_group, spec_key, spec_value, display_order FROM product_specs WHERE product_id = ? ORDER BY display_order ASC");
                    $specStmt->execute([$id]);
                    $product['specs'] = $specStmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $product['specs'] = [];
                }

                // Lấy đánh giá tóm tắt từ reviews
                try {
                    $revStmt = $pdo->prepare("SELECT id, user_name, rating, comment, created_at FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY id DESC LIMIT 5");
                    $revStmt->execute([$id]);
                    $product['recent_reviews'] = $revStmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $product['recent_reviews'] = [];
                }

                return $product;
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        // FALLBACK: Đọc từ data.json
        $data = DataStore::getJsonData();
        foreach ($data['products'] ?? [] as $p) {
            if ((int)($p['id'] ?? 0) === $id) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Tạo sản phẩm mới
     */
    public static function create(array $data): array {
        $name = $data['name'] ?? 'Sản phẩm mới';
        $catId = (int)($data['category_id'] ?? 1);
        $brandId = (int)($data['brand_id'] ?? 1);
        $catName = $data['category_name'] ?? 'Điện thoại';
        $brandName = $data['brand_name'] ?? 'Apple';
        $price = (float)($data['price'] ?? 1000000);
        $origPrice = (float)($data['original_price'] ?? $price * 1.1);
        $stock = (int)($data['stock'] ?? 10);
        $image = $data['image'] ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop&q=80';
        $images = $data['images'] ?? [$image];
        $imagesJson = json_encode($images);
        $ram = $data['ram'] ?? '8GB';
        $rom = $data['rom'] ?? '128GB';
        $chipset = $data['chipset'] ?? '';
        $screen = $data['screen'] ?? '';
        $battery = $data['battery'] ?? '';
        $camera = $data['camera'] ?? '';
        $desc = $data['description'] ?? '';
        $featured = !empty($data['featured']) ? 1 : 0;
        $slug = $data['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $pdo = DataStore::getPdo();
        $newId = null;
        if ($pdo) {
            try {
                $sql = "INSERT INTO products (name, category_id, category_name, brand_id, brand_name, price, original_price, stock, image, images, ram, rom, chipset, screen, battery, camera, description, featured) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $catId, $catName, $brandId, $brandName, $price, $origPrice, $stock,
                    $image, $imagesJson, $ram, $rom, $chipset, $screen, $battery, $camera, $desc, $featured
                ]);
                $newId = (int)$pdo->lastInsertId();
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        if (!$newId) {
            $newId = count($json['products'] ?? []) ? max(array_column($json['products'], 'id')) + 1 : 1;
        }

        $newProduct = [
            'id' => $newId,
            'name' => $name,
            'slug' => $slug,
            'category_id' => $catId,
            'category_name' => $catName,
            'brand_id' => $brandId,
            'brand_name' => $brandName,
            'price' => $price,
            'original_price' => $origPrice,
            'stock' => $stock,
            'rating' => 5.0,
            'review_count' => 0,
            'image' => $image,
            'images' => $images,
            'ram' => $ram,
            'rom' => $rom,
            'chipset' => $chipset,
            'screen' => $screen,
            'battery' => $battery,
            'camera' => $camera,
            'description' => $desc,
            'featured' => (bool)$featured,
            'created_at' => date('Y-m-d')
        ];
        $json['products'][] = $newProduct;
        DataStore::saveJsonData($json);
        return $newProduct;
    }

    /**
     * Cập nhật sản phẩm
     */
    public static function update(int $id, array $data): ?array {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $fields = [];
                $params = [];
                $allowed = ['name', 'price', 'original_price', 'stock', 'ram', 'rom', 'chipset', 'screen', 'battery', 'camera', 'description', 'featured', 'status', 'image'];
                foreach ($allowed as $f) {
                    if (isset($data[$f])) {
                        $fields[] = "`$f` = ?";
                        $params[] = $data[$f];
                    }
                }
                if (!empty($fields)) {
                    $params[] = $id;
                    $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                }
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        $found = null;
        if (isset($json['products'])) {
            foreach ($json['products'] as &$p) {
                if ((int)$p['id'] === $id) {
                    foreach ($data as $k => $v) {
                        if ($k !== 'id') {
                            $p[$k] = $v;
                        }
                    }
                    $found = $p;
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
     * Xóa sản phẩm
     */
    public static function delete(int $id): bool {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$id]);
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        $json = DataStore::getJsonData();
        if (isset($json['products'])) {
            $json['products'] = array_values(array_filter($json['products'], fn($p) => (int)$p['id'] !== $id));
            DataStore::saveJsonData($json);
        }
        return true;
    }
}
