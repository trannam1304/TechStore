<?php
/**
 * TechStore - Product Controller
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T13 & T14:
 * - T13: GET /products (lọc hãng, giá, danh mục, RAM/ROM, sắp xếp, tìm kiếm từ khóa, phân trang)
 * - T14: GET /products/{id} (ảnh, thông số kỹ thuật, đánh giá tóm tắt) + API categories, brands
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;
use Api\Helpers\DataStore;
use PDO;
use Exception;

class ProductController extends BaseController {
    /**
     * T13: GET /api/v1/products
     * Danh sách sản phẩm với bộ lọc đa tiêu chí, tìm kiếm, sắp xếp và phân trang
     */
    public static function index(array $query): void {
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
                $where = ["p.status = 'active'"];
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

                Response::paginate($items, $totalItems, $page, $limit, 'Lấy danh sách sản phẩm thành công');
                return;
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        // FALLBACK: Đọc và lọc từ database/data.json
        $data = DataStore::getJsonData();
        $items = $data['products'] ?? [];

        $filtered = array_values(array_filter($items, function($p) use ($search, $catId, $brandId, $minPrice, $maxPrice, $ram, $rom) {
            if ($search !== '') {
                $name = strtolower($p['name'] ?? '');
                $cat = strtolower($p['category_name'] ?? '');
                $brand = strtolower($p['brand_name'] ?? '');
                $chip = strtolower($p['chipset'] ?? '');
                if (!str_contains($name, $search) && !str_contains($cat, $search) && !str_contains($brand, $search) && !str_contains($chip, $search)) {
                    return false;
                }
            }
            if ($catId !== null && (int)($p['category_id'] ?? 0) !== $catId) {
                return false;
            }
            if ($brandId !== null && (int)($p['brand_id'] ?? 0) !== $brandId) {
                return false;
            }
            if ($minPrice !== null && ($p['price'] ?? 0) < $minPrice) {
                return false;
            }
            if ($maxPrice !== null && ($p['price'] ?? 0) > $maxPrice) {
                return false;
            }
            if ($ram !== '' && !str_contains(strtolower($p['ram'] ?? ''), strtolower($ram))) {
                return false;
            }
            if ($rom !== '' && !str_contains(strtolower($p['rom'] ?? ''), strtolower($rom))) {
                return false;
            }
            return true;
        }));

        // Sắp xếp
        usort($filtered, function($a, $b) use ($sort) {
            return match ($sort) {
                'price_asc'   => ($a['price'] ?? 0) <=> ($b['price'] ?? 0),
                'price_desc'  => ($b['price'] ?? 0) <=> ($a['price'] ?? 0),
                'rating_desc' => ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0),
                'newest'      => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''),
                default       => (($b['featured'] ?? 0) ? 1 : 0) <=> (($a['featured'] ?? 0) ? 1 : 0)
            };
        });

        $totalItems = count($filtered);
        $offset = ($page - 1) * $limit;
        $paginatedItems = array_slice($filtered, $offset, $limit);

        Response::paginate($paginatedItems, $totalItems, $page, $limit, 'Lấy danh sách sản phẩm thành công');
    }

    /**
     * T14: GET /api/v1/products/{id}
     * Chi tiết sản phẩm kèm thư viện ảnh, thông số kỹ thuật có cấu trúc và đánh giá tóm tắt
     */
    public static function show(int $id): void {
        $pdo = DataStore::getPdo();
        if ($pdo) {
            try {
                // Lấy thông tin sản phẩm chính
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    Response::error('Không tìm thấy sản phẩm yêu cầu', 'PRODUCT_NOT_FOUND', 404);
                }

                // Cập nhật lượt xem (view_count)
                $pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
                $product['view_count'] = (int)($product['view_count'] ?? 0) + 1;

                // Lấy danh sách ảnh từ product_images
                $imgStmt = $pdo->prepare("SELECT image_url, is_primary, display_order FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC");
                $imgStmt->execute([$id]);
                $imageList = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
                $product['gallery'] = $imageList;

                if (!empty($product['images']) && is_string($product['images'])) {
                    $product['images'] = json_decode($product['images'], true) ?? [];
                }

                // Lấy danh sách thông số kỹ thuật từ product_specs
                $specStmt = $pdo->prepare("SELECT spec_group, spec_key, spec_value, display_order FROM product_specs WHERE product_id = ? ORDER BY display_order ASC");
                $specStmt->execute([$id]);
                $product['specs'] = $specStmt->fetchAll(PDO::FETCH_ASSOC);

                // Lấy đánh giá tóm tắt từ reviews
                $revStmt = $pdo->prepare("SELECT id, user_name, rating, comment, created_at FROM reviews WHERE product_id = ? AND status = 'approved' ORDER BY id DESC LIMIT 5");
                $revStmt->execute([$id]);
                $product['recent_reviews'] = $revStmt->fetchAll(PDO::FETCH_ASSOC);

                Response::success($product, 200);
                return;
            } catch (Exception $e) {
                // fallback JSON
            }
        }

        // FALLBACK: Đọc từ data.json
        $data = DataStore::getJsonData();
        $found = null;
        foreach ($data['products'] ?? [] as &$p) {
            if ((int)$p['id'] === $id) {
                $p['view_count'] = (int)($p['view_count'] ?? 0) + 1;
                $found = $p;
                break;
            }
        }

        if (!$found) {
            Response::error('Không tìm thấy sản phẩm yêu cầu', 'PRODUCT_NOT_FOUND', 404);
        }

        // Lấy specs tổng hợp
        $specs = [];
        if (!empty($found['chipset'])) $specs[] = ['spec_group' => 'Hiệu năng', 'spec_key' => 'Vi xử lý (CPU/Chip)', 'spec_value' => $found['chipset']];
        if (!empty($found['ram']))     $specs[] = ['spec_group' => 'Bộ nhớ', 'spec_key' => 'Dung lượng RAM', 'spec_value' => $found['ram']];
        if (!empty($found['rom']))     $specs[] = ['spec_group' => 'Bộ nhớ', 'spec_key' => 'Bộ nhớ trong (ROM/SSD)', 'spec_value' => $found['rom']];
        if (!empty($found['screen']))  $specs[] = ['spec_group' => 'Màn hình', 'spec_key' => 'Thông số hiển thị', 'spec_value' => $found['screen']];
        if (!empty($found['battery'])) $specs[] = ['spec_group' => 'Pin & Sạc', 'spec_key' => 'Pin và công nghệ sạc', 'spec_value' => $found['battery']];
        if (!empty($found['camera']))  $specs[] = ['spec_group' => 'Camera', 'spec_key' => 'Cụm camera', 'spec_value' => $found['camera']];
        $found['specs'] = $specs;

        // Lấy đánh giá liên quan
        $reviews = [];
        foreach ($data['reviews'] ?? [] as $rv) {
            if ((int)$rv['product_id'] === $id && ($rv['status'] ?? 'approved') === 'approved') {
                $reviews[] = $rv;
            }
        }
        $found['recent_reviews'] = array_slice($reviews, 0, 5);

        Response::success($found, 200);
    }

    /**
     * T14: GET /api/v1/categories
     * Lấy danh sách ngành hàng
     */
    public static function categories(): void {
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

        $data = DataStore::getJsonData();
        Response::success($data['categories'] ?? [], 200);
    }

    /**
     * T14: GET /api/v1/brands
     * Lấy danh sách thương hiệu công nghệ
     */
    public static function brands(): void {
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

        $data = DataStore::getJsonData();
        Response::success($data['brands'] ?? [], 200);
    }

    /**
     * POST /api/v1/products (Admin)
     * Thêm sản phẩm mới
     */
    public static function create(array $body): void {
        $db = self::getDb();
        $newId = count($db['products']) ? max(array_column($db['products'], 'id')) + 1 : 1;
        $newProduct = [
            'id' => $newId,
            'name' => $body['name'] ?? 'Sản phẩm mới',
            'category_id' => (int)($body['category_id'] ?? 1),
            'category_name' => $body['category_name'] ?? 'Điện thoại',
            'brand_id' => (int)($body['brand_id'] ?? 1),
            'brand_name' => $body['brand_name'] ?? 'Apple',
            'price' => (int)($body['price'] ?? 1000000),
            'original_price' => (int)($body['original_price'] ?? $body['price'] ?? 1200000),
            'stock' => (int)($body['stock'] ?? 10),
            'rating' => 5.0,
            'review_count' => 0,
            'image' => $body['image'] ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop&q=80',
            'images' => $body['images'] ?? [$body['image'] ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=80'],
            'ram' => $body['ram'] ?? '8GB',
            'rom' => $body['rom'] ?? '128GB',
            'chipset' => $body['chipset'] ?? '',
            'screen' => $body['screen'] ?? '',
            'battery' => $body['battery'] ?? '',
            'camera' => $body['camera'] ?? '',
            'description' => $body['description'] ?? '',
            'featured' => !empty($body['featured']),
            'created_at' => date('Y-m-d')
        ];
        $db['products'][] = $newProduct;
        self::saveDb($db);
        Response::success($newProduct, 201, 'Tạo sản phẩm thành công');
    }

    /**
     * PUT/PATCH /api/v1/products/{id} (Admin)
     * Cập nhật sản phẩm
     */
    public static function update(int $id, array $body): void {
        $db = self::getDb();
        $idx = -1;
        foreach ($db['products'] as $i => $p) {
            if ($p['id'] === $id) {
                $idx = $i;
                break;
            }
        }
        if ($idx === -1) {
            Response::error('Không tìm thấy sản phẩm', 'NOT_FOUND', 404);
        }
        foreach ($body as $k => $v) {
            if ($k !== 'id') {
                $db['products'][$idx][$k] = $v;
            }
        }
        self::saveDb($db);
        Response::success($db['products'][$idx], 200, 'Cập nhật sản phẩm thành công');
    }

    /**
     * DELETE /api/v1/products/{id} (Admin)
     * Xóa sản phẩm
     */
    public static function destroy(int $id): void {
        $db = self::getDb();
        $db['products'] = array_values(array_filter($db['products'], fn($p) => $p['id'] !== $id));
        self::saveDb($db);
        Response::success(null, 204);
    }
}
