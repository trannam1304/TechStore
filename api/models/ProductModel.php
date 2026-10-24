<?php
/**
 * TechStore - Product Model (Clean Architecture)
 * Quản lý toàn bộ dữ liệu sản phẩm, hình ảnh và thông số kỹ thuật thuần MySQL PDO
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;
use Exception;

class ProductModel extends BaseModel {
    /**
     * Phân trang sản phẩm (tương thích Controller & API Response)
     */
    public static function paginate(array $filters = []): array {
        $res = self::getAll($filters);
        return [
            'items'       => $res['items'],
            'total'       => $res['pagination']['total_items'],
            'page'        => $res['pagination']['current_page'],
            'limit'       => $res['pagination']['per_page'],
            'total_pages' => $res['pagination']['total_pages']
        ];
    }

    /**
     * Lấy danh sách sản phẩm với bộ lọc, sắp xếp và phân trang từ MySQL
     */
    public static function getAll(array $filters = []): array {
        $pdo = Database::getConnection();

        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, min(100, (int)($filters['limit'] ?? 12)));
        $offset = ($page - 1) * $limit;

        $conditions = ["1=1"];
        $params = [];

        // 1. Tìm kiếm (Tên, Danh mục, Thương hiệu)
        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $conditions[] = "(name LIKE ? OR category_name LIKE ? OR brand_name LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        // 2. Lọc theo Danh mục
        if (!empty($filters['category_id'])) {
            $conditions[] = "category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        // 3. Lọc theo Thương hiệu
        if (!empty($filters['brand_id'])) {
            $conditions[] = "brand_id = ?";
            $params[] = (int)$filters['brand_id'];
        }

        // 4. Lọc theo Khoảng giá
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $conditions[] = "price >= ?";
            $params[] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $conditions[] = "price <= ?";
            $params[] = (float)$filters['max_price'];
        }

        // 5. Lọc RAM / ROM
        if (!empty($filters['ram'])) {
            $conditions[] = "ram LIKE ?";
            $params[] = '%' . trim($filters['ram']) . '%';
        }
        if (!empty($filters['rom'])) {
            $conditions[] = "rom LIKE ?";
            $params[] = '%' . trim($filters['rom']) . '%';
        }

        // 6. Sản phẩm nổi bật
        if (isset($filters['featured'])) {
            $conditions[] = "featured = ?";
            $params[] = $filters['featured'] ? 1 : 0;
        }

        $whereSql = implode(' AND ', $conditions);

        // Đếm tổng số bản ghi
        $countSql = "SELECT COUNT(*) FROM products WHERE {$whereSql}";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalItems = (int)$countStmt->fetchColumn();

        // 7. Sắp xếp
        $sortOrder = "id DESC";
        $sort = $filters['sort'] ?? '';
        switch ($sort) {
            case 'price_asc':
                $sortOrder = "price ASC";
                break;
            case 'price_desc':
                $sortOrder = "price DESC";
                break;
            case 'rating_desc':
                $sortOrder = "rating DESC";
                break;
            case 'views_desc':
                $sortOrder = "view_count DESC";
                break;
            case 'newest':
            default:
                $sortOrder = "id DESC";
                break;
        }

        // Truy vấn danh sách
        $querySql = "SELECT * FROM products WHERE {$whereSql} ORDER BY {$sortOrder} LIMIT {$limit} OFFSET {$offset}";
        $stmt = $pdo->prepare($querySql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format kiểu dữ liệu
        foreach ($items as &$item) {
            self::formatProductFields($item);
        }

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $limit,
                'total_items'  => $totalItems,
                'total_pages'  => (int)ceil($totalItems / $limit)
            ]
        ];
    }

    /**
     * Tìm sản phẩm theo ID kèm hình ảnh phụ và thông số kỹ thuật
     */
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return null;

        self::formatProductFields($product);

        // Lấy danh sách hình ảnh từ bảng product_images
        $imgStmt = $pdo->prepare("SELECT image_url, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC");
        $imgStmt->execute([$id]);
        $subImages = $imgStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        if (!empty($subImages)) {
            $product['images'] = $subImages;
        }

        // Lấy thông số kỹ thuật từ product_specs
        $specStmt = $pdo->prepare("SELECT spec_group, spec_key, spec_value FROM product_specs WHERE product_id = ? ORDER BY spec_group ASC, display_order ASC");
        $specStmt->execute([$id]);
        $product['specs'] = $specStmt->fetchAll(PDO::FETCH_ASSOC);

        return $product;
    }

    /**
     * Tìm sản phẩm theo Slug
     */
    public static function findBySlug(string $slug): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $id = $stmt->fetchColumn();
        return $id ? self::findById((int)$id) : null;
    }

    /**
     * Tạo sản phẩm mới
     */
    public static function create(array $data): array {
        $pdo = Database::getConnection();

        $name = $data['name'] ?? 'Sản phẩm mới';
        $slug = $data['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $catId = (int)($data['category_id'] ?? 1);
        $brandId = (int)($data['brand_id'] ?? 1);
        $catName = $data['category_name'] ?? null;
        if (!$catName) {
            $catRow = CategoryModel::findById($catId);
            $catName = $catRow['name'] ?? 'Điện thoại';
        }
        $brandName = $data['brand_name'] ?? null;
        if (!$brandName) {
            $brandRow = BrandModel::findById($brandId);
            $brandName = $brandRow['name'] ?? 'Apple';
        }
        $price = (float)($data['price'] ?? 1000000);
        $origPrice = (float)($data['original_price'] ?? $price * 1.1);
        $stock = (int)($data['stock'] ?? 10);
        $image = $data['image'] ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop&q=80';
        $images = $data['images'] ?? [$image];
        $imagesJson = is_array($images) ? json_encode($images) : $images;
        $ram = $data['ram'] ?? '8GB';
        $rom = $data['rom'] ?? '128GB';
        $chipset = $data['chipset'] ?? '';
        $screen = $data['screen'] ?? '';
        $battery = $data['battery'] ?? '';
        $camera = $data['camera'] ?? '';
        $desc = $data['description'] ?? '';
        $featured = !empty($data['featured']) ? 1 : 0;

        $sql = "INSERT INTO products (
            name, slug, category_id, category_name, brand_id, brand_name, price, original_price,
            stock, image, images, ram, rom, chipset, screen, battery, camera, description, featured, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, $slug, $catId, $catName, $brandId, $brandName, $price, $origPrice,
            $stock, $image, $imagesJson, $ram, $rom, $chipset, $screen, $battery, $camera, $desc, $featured
        ]);

        $newId = (int)$pdo->lastInsertId();

        // Lưu hình ảnh vào product_images nếu có
        if (is_array($images)) {
            $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, ?, ?)");
            foreach ($images as $idx => $imgUrl) {
                $imgStmt->execute([$newId, $imgUrl, $idx === 0 ? 1 : 0, $idx]);
            }
        }

        return self::findById($newId);
    }

    /**
     * Cập nhật thông tin sản phẩm
     */
    public static function update(int $id, array $data): ?array {
        $pdo = Database::getConnection();

        $allowed = ['name', 'slug', 'category_id', 'category_name', 'brand_id', 'brand_name',
                    'price', 'original_price', 'stock', 'image', 'images', 'ram', 'rom',
                    'chipset', 'screen', 'battery', 'camera', 'description', 'featured', 'status'];

        $fields = [];
        $params = [];

        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                $fields[] = "`{$f}` = ?";
                $val = $data[$f];
                if ($f === 'images' && is_array($val)) {
                    $val = json_encode($val);
                }
                $params[] = $val;
            }
        }

        if (!empty($fields)) {
            $params[] = $id;
            $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        return self::findById($id);
    }

    /**
     * Xóa sản phẩm
     */
    public static function delete(int $id): bool {
        $pdo = Database::getConnection();
        // Xóa các bảng phụ thuộc trước
        $pdo->prepare("DELETE FROM product_specs WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM reviews WHERE product_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM wishlists WHERE product_id = ?")->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Helper chuẩn hóa định dạng các trường sản phẩm
     */
    private static function formatProductFields(array &$p): void {
        $p['id'] = (int)($p['id'] ?? 0);
        $p['category_id'] = (int)($p['category_id'] ?? 0);
        $p['brand_id'] = (int)($p['brand_id'] ?? 0);
        $p['price'] = (float)($p['price'] ?? 0);
        $p['original_price'] = (float)($p['original_price'] ?? 0);
        $p['stock'] = (int)($p['stock'] ?? 0);
        $p['rating'] = (float)($p['rating'] ?? 5.0);
        $p['review_count'] = (int)($p['review_count'] ?? 0);
        $p['view_count'] = (int)($p['view_count'] ?? 0);
        $p['featured'] = (bool)($p['featured'] ?? false);

        if (!empty($p['images']) && is_string($p['images'])) {
            $decoded = json_decode($p['images'], true);
            $p['images'] = is_array($decoded) ? $decoded : [$p['image'] ?? ''];
        } elseif (empty($p['images'])) {
            $p['images'] = !empty($p['image']) ? [$p['image']] : [];
        }
    }
}
