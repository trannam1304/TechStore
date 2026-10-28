<?php
/**
 * TechStore - Product Model (Clean Architecture)
 * Quản lý toàn bộ dữ liệu sản phẩm, hình ảnh và thông số kỹ thuật thuần MySQL PDO
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/WishlistModel.php';
require_once __DIR__ . '/CartModel.php';

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
        $rawSpecs = $specStmt->fetchAll(PDO::FETCH_ASSOC);
        $product['specs'] = self::normalizeProductSpecs($product, $rawSpecs);
        $product['options'] = self::buildProductOptions($product);

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

    private static ?array $columns = null;

    /**
     * Lấy danh sách cột thực tế của bảng products (đảm bảo tương thích ngược schema DB cũ)
     */
    public static function getColumns(): array {
        if (self::$columns === null) {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->query("SHOW COLUMNS FROM products");
                self::$columns = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
            } catch (\Throwable $e) {
                self::$columns = [];
            }
        }
        return self::$columns ?: [];
    }

    /**
     * Tạo sản phẩm mới (tự thích ứng schema bảng products)
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

        $cols = self::getColumns();
        $dataMap = [
            'name'           => $name,
            'category_id'    => $catId,
            'category_name'  => $catName,
            'brand_id'       => $brandId,
            'brand_name'     => $brandName,
            'price'          => $price,
            'original_price' => $origPrice,
            'stock'          => $stock,
            'image'          => $image,
            'images'         => $imagesJson,
            'ram'            => $ram,
            'rom'            => $rom,
            'chipset'        => $chipset,
            'screen'         => $screen,
            'battery'        => $battery,
            'camera'         => $camera,
            'description'    => $desc,
            'featured'       => $featured
        ];

        // Chỉ thêm slug nếu cột slug tồn tại trong bảng
        if (empty($cols) || in_array('slug', $cols)) {
            $dataMap['slug'] = $slug;
        }

        // Chỉ thêm status nếu cột status tồn tại trong bảng
        if (!empty($cols) && in_array('status', $cols)) {
            $dataMap['status'] = $data['status'] ?? 'active';
        }

        $fields = array_keys($dataMap);
        $placeholders = array_fill(0, count($fields), '?');
        $values = array_values($dataMap);

        $sql = "INSERT INTO products (" . implode(', ', $fields) . ", created_at) 
                VALUES (" . implode(', ', $placeholders) . ", NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

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
     * Cập nhật thông tin sản phẩm (chỉ cập nhật những cột thực sự tồn tại trong DB)
     */
    public static function update(int $id, array $data): ?array {
        $pdo = Database::getConnection();

        $allowed = ['name', 'slug', 'category_id', 'category_name', 'brand_id', 'brand_name',
                    'price', 'original_price', 'stock', 'image', 'images', 'ram', 'rom',
                    'chipset', 'screen', 'battery', 'camera', 'description', 'featured', 'status'];

        $cols = self::getColumns();
        $fields = [];
        $params = [];

        foreach ($allowed as $f) {
            if (isset($data[$f])) {
                // Tự động bỏ qua trường nếu DB cũ chưa có cột này (tránh lỗi 500)
                if (!empty($cols) && !in_array($f, $cols)) {
                    continue;
                }
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
        
        $wlTable = WishlistModel::getTableName();
        $pdo->prepare("DELETE FROM {$wlTable} WHERE product_id = ?")->execute([$id]);

        $cartTable = CartModel::getTableName();
        $pdo->prepare("DELETE FROM {$cartTable} WHERE product_id = ?")->execute([$id]);

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

    /**
     * Chuẩn hóa thông số kỹ thuật (product_specs) phù hợp đúng từng nhóm thiết bị
     */
    public static function normalizeProductSpecs(array $product, array $rawSpecs): array {
        $catId = (int)($product['category_id'] ?? 0);
        $name = mb_strtolower($product['name'] ?? '', 'UTF-8');

        if (empty($rawSpecs)) {
            $rawSpecs = [];
            if (!empty($product['chipset'])) $rawSpecs[] = ['spec_group' => 'Hiệu năng', 'spec_key' => 'Vi xử lý (CPU/Chip)', 'spec_value' => $product['chipset']];
            if (!empty($product['ram'])) $rawSpecs[] = ['spec_group' => 'Bộ nhớ', 'spec_key' => 'Dung lượng RAM', 'spec_value' => $product['ram']];
            if (!empty($product['rom'])) $rawSpecs[] = ['spec_group' => 'Bộ nhớ', 'spec_key' => 'Bộ nhớ trong (ROM/SSD)', 'spec_value' => $product['rom']];
            if (!empty($product['screen'])) $rawSpecs[] = ['spec_group' => 'Màn hình', 'spec_key' => 'Thông số hiển thị', 'spec_value' => $product['screen']];
            if (!empty($product['battery'])) $rawSpecs[] = ['spec_group' => 'Pin & Sạc', 'spec_key' => 'Pin và công nghệ sạc', 'spec_value' => $product['battery']];
            if (!empty($product['camera'])) $rawSpecs[] = ['spec_group' => 'Camera', 'spec_key' => 'Cụm camera', 'spec_value' => $product['camera']];
        }

        $normalized = [];
        foreach ($rawSpecs as $s) {
            $key = $s['spec_key'];
            $group = $s['spec_group'];
            $val = trim($s['spec_value'] ?? '');
            if ($val === '') continue;

            // Ngành 4: Âm thanh (Tai nghe, Loa)
            if ($catId === 4 || stripos($name, 'tai nghe') !== false || stripos($name, 'loa') !== false || stripos($name, 'airpods') !== false || stripos($name, 'buds') !== false) {
                if (stripos($key, 'RAM') !== false || stripos($key, 'ROM') !== false) continue;
                if (stripos($key, 'camera') !== false) {
                    $group = 'Tính năng'; $key = 'Micro & Khử ồn';
                } elseif (stripos($key, 'hiển thị') !== false || stripos($key, 'màn hình') !== false) {
                    $group = 'Âm thanh'; $key = 'Driver & Màng loa';
                } elseif (stripos($key, 'vi xử lý') !== false || stripos($key, 'chip') !== false) {
                    $group = 'Công nghệ'; $key = 'Chip xử lý & Chuẩn Codec';
                }
            }
            // Ngành 5: Smartwatch & Phụ kiện
            elseif ($catId === 5 || stripos($name, 'bàn phím') !== false || stripos($name, 'chuột') !== false || stripos($name, 'sạc') !== false || stripos($name, 'watch') !== false) {
                if (stripos($name, 'bàn phím') !== false || stripos($name, 'keyboard') !== false) {
                    if (stripos($key, 'RAM') !== false || stripos($key, 'ROM') !== false) continue;
                    if (stripos($key, 'vi xử lý') !== false || stripos($key, 'chip') !== false) { $group = 'Cơ học'; $key = 'Loại Switch cơ học'; }
                    elseif (stripos($key, 'hiển thị') !== false || stripos($key, 'màn hình') !== false) { $group = 'Đèn nền'; $key = 'Đèn nền & Cảm biến thông minh'; }
                    elseif (stripos($key, 'camera') !== false) { $group = 'Kết nối'; $key = 'Kết nối đa thiết bị & Tương thích'; }
                } elseif (stripos($name, 'chuột') !== false || stripos($name, 'mouse') !== false) {
                    if (stripos($key, 'RAM') !== false || stripos($key, 'ROM') !== false) continue;
                    if (stripos($key, 'vi xử lý') !== false || stripos($key, 'chip') !== false) { $group = 'Cảm biến'; $key = 'Cảm biến & Độ phân giải (DPI)'; }
                    elseif (stripos($key, 'hiển thị') !== false || stripos($key, 'màn hình') !== false) { $group = 'Cuộn trang'; $key = 'Con lăn MagSpeed điện từ'; }
                    elseif (stripos($key, 'camera') !== false) { $group = 'Phím bấm'; $key = 'Độ êm nút bấm & Kết nối'; }
                } elseif (stripos($name, 'sạc') !== false || stripos($name, 'charger') !== false) {
                    if (stripos($key, 'RAM') !== false || stripos($key, 'ROM') !== false) continue;
                    if (stripos($key, 'vi xử lý') !== false || stripos($key, 'chip') !== false) { $group = 'Công nghệ'; $key = 'Công nghệ sạc GaN'; }
                    elseif (stripos($key, 'hiển thị') !== false || stripos($key, 'màn hình') !== false) { $group = 'Cổng sạc'; $key = 'Cổng kết nối & Công suất'; }
                    elseif (stripos($key, 'camera') !== false) { $group = 'Chuẩn sạc'; $key = 'Chuẩn sạc nhanh hỗ trợ'; }
                    elseif (stripos($key, 'pin') !== false) { $group = 'An toàn'; $key = 'Bảo vệ nhiệt an toàn ActiveShield'; }
                } elseif (stripos($name, 'watch') !== false || stripos($name, 'đồng hồ') !== false) {
                    if (stripos($key, 'camera') !== false) { $group = 'Tính năng'; $key = 'Cảm biến sức khỏe & Tiện ích'; }
                }
            }

            $normalized[] = [
                'spec_group' => $group,
                'spec_key'   => $key,
                'spec_value' => $val
            ];
        }

        return $normalized;
    }

    /**
     * Sinh các nhóm tùy chọn (options/variants) thông minh cho sản phẩm
     */
    public static function buildProductOptions(array $product): array {
        $id = (int)($product['id'] ?? 0);
        $catId = (int)($product['category_id'] ?? 0);
        $name = mb_strtolower($product['name'] ?? '', 'UTF-8');

        // 1. Cấu hình định nghĩa chi tiết cho 42 sản phẩm mẫu trong hệ thống
        $catalogOptions = [
            // Smartphone (1 -> 10)
            1 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '256GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '512GB', 'price_delta' => 6000000],
                    ['label' => '1TB', 'price_delta' => 12000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc Titan:', 'values' => [
                    ['label' => 'Titan Sa Mạc', 'default' => true],
                    ['label' => 'Titan Tự Nhiên'],
                    ['label' => 'Titan Trắng'],
                    ['label' => 'Titan Đen']
                ]]
            ],
            2 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '128GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '256GB', 'price_delta' => 3000000],
                    ['label' => '512GB', 'price_delta' => 8000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc cá tính:', 'values' => [
                    ['label' => 'Đen', 'default' => true],
                    ['label' => 'Trắng'],
                    ['label' => 'Hồng'],
                    ['label' => 'Xanh Mòng Két'],
                    ['label' => 'Xanh Lưu Ly']
                ]]
            ],
            3 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '128GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '256GB', 'price_delta' => 3000000],
                    ['label' => '512GB', 'price_delta' => 7000000],
                    ['label' => '1TB', 'price_delta' => 11000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc Titan:', 'values' => [
                    ['label' => 'Titan Tự Nhiên', 'default' => true],
                    ['label' => 'Titan Xanh'],
                    ['label' => 'Titan Trắng'],
                    ['label' => 'Titan Đen']
                ]]
            ],
            4 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '256GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '512GB', 'price_delta' => 4000000],
                    ['label' => '1TB', 'price_delta' => 8000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc Titan:', 'values' => [
                    ['label' => 'Xám Titan', 'default' => true],
                    ['label' => 'Đen Titan'],
                    ['label' => 'Tím Titan'],
                    ['label' => 'Vàng Titan']
                ]]
            ],
            5 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '256GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '512GB', 'price_delta' => 3500000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc thời thượng:', 'values' => [
                    ['label' => 'Đen Onyx', 'default' => true],
                    ['label' => 'Xám Marble'],
                    ['label' => 'Tím Cobalt'],
                    ['label' => 'Vàng Amber']
                ]]
            ],
            6 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '256GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '512GB', 'price_delta' => 4000000],
                    ['label' => '1TB', 'price_delta' => 9000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc sang trọng:', 'values' => [
                    ['label' => 'Xám Metal', 'default' => true],
                    ['label' => 'Xanh Navy'],
                    ['label' => 'Hồng Rose']
                ]]
            ],
            7 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '16GB / 512GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '16GB / 1TB', 'price_delta' => 3000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc thân vỏ:', 'values' => [
                    ['label' => 'Đen Da Thuần Chay', 'default' => true],
                    ['label' => 'Trắng Gốm']
                ]]
            ],
            8 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '12GB / 512GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '12GB / 256GB', 'price_delta' => -1500000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc Titan:', 'values' => [
                    ['label' => 'Xám Titan', 'default' => true],
                    ['label' => 'Xanh Titan'],
                    ['label' => 'Đen Titan']
                ]]
            ],
            9 => [
                ['id' => 'storage', 'title' => 'Chọn cấu hình Gaming:', 'values' => [
                    ['label' => '16GB / 512GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '24GB / 1TB Pro Edition', 'price_delta' => 4000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc chiến binh:', 'values' => [
                    ['label' => 'Phantom Black (Đen Bóng Đêm)', 'default' => true]
                ]]
            ],
            10 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                    ['label' => '128GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '256GB', 'price_delta' => 1200000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc cá tính:', 'values' => [
                    ['label' => 'Xanh Iceblue', 'default' => true],
                    ['label' => 'Tím Lilac'],
                    ['label' => 'Xanh Navy']
                ]]
            ],

            // Laptop (11 -> 20)
            11 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình hiệu năng:', 'values' => [
                    ['label' => '18GB RAM | 512GB SSD', 'price_delta' => 0, 'default' => true],
                    ['label' => '36GB RAM | 1TB SSD', 'price_delta' => 14000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Space Black (Đen Không Gian)', 'default' => true],
                    ['label' => 'Silver (Bạc)']
                ]]
            ],
            12 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình bộ nhớ:', 'values' => [
                    ['label' => '8GB RAM | 256GB SSD', 'price_delta' => 0, 'default' => true],
                    ['label' => '16GB RAM | 512GB SSD', 'price_delta' => 6000000],
                    ['label' => '24GB RAM | 512GB SSD', 'price_delta' => 11000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Midnight (Đêm Xanh)', 'default' => true],
                    ['label' => 'Starlight (Ánh Sao)'],
                    ['label' => 'Space Gray (Xám)'],
                    ['label' => 'Silver (Bạc)']
                ]]
            ],
            13 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình máy trạm:', 'values' => [
                    ['label' => '36GB RAM | 1TB SSD', 'price_delta' => 0, 'default' => true],
                    ['label' => '48GB RAM | 1TB SSD', 'price_delta' => 10000000],
                    ['label' => '128GB RAM | 2TB SSD', 'price_delta' => 28000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Space Black', 'default' => true],
                    ['label' => 'Silver']
                ]]
            ],
            14 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình Gaming:', 'values' => [
                    ['label' => 'Core Ultra 9 / RTX 4070 / 32GB / 1TB', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Core Ultra 9 / RTX 4080 / 32GB / 2TB', 'price_delta' => 15000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Eclipse Gray (Xám Nhật Thực)', 'default' => true],
                    ['label' => 'Platinum White (Trắng Bạch Kim)']
                ]]
            ],
            15 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình vi xử lý:', 'values' => [
                    ['label' => 'Core Ultra 7 / 16GB / 1TB SSD', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Core Ultra 9 / 32GB / 1TB SSD', 'price_delta' => 4500000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Ponder Blue (Xanh Trầm)', 'default' => true],
                    ['label' => 'Foggy Silver (Bạc Sương Mù)']
                ]]
            ],
            16 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình đồ họa:', 'values' => [
                    ['label' => 'Ryzen 7 / RTX 4060 / 16GB / 512GB', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Ryzen 9 / RTX 4070 / 32GB / 1TB', 'price_delta' => 5000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Mecha Gray (Xám Cơ Giới)', 'default' => true],
                    ['label' => 'Jaeger Gray (Xám Jaeger)']
                ]]
            ],
            17 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình màn hình & Card:', 'values' => [
                    ['label' => 'Core Ultra 7 / RTX 4050 / 32GB / 1TB SSD', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Core Ultra 7 / Intel Arc / 16GB / 512GB SSD', 'price_delta' => -6000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Platinum (Bạch Kim)', 'default' => true],
                    ['label' => 'Graphite (Xám Than)']
                ]]
            ],
            18 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình hiệu năng đỉnh:', 'values' => [
                    ['label' => 'Core Ultra 9 / RTX 4070 / 32GB / 1TB', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Core Ultra 9 / RTX 4080 / 64GB / 2TB', 'price_delta' => 18000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Platinum (Bạch Kim)', 'default' => true],
                    ['label' => 'Graphite (Xám Than)']
                ]]
            ],
            19 => [
                ['id' => 'spec', 'title' => 'Chọn phiên bản vi xử lý:', 'values' => [
                    ['label' => 'Core i5 / 16GB RAM / 512GB SSD', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Core i7 / 16GB RAM / 1TB SSD', 'price_delta' => 3200000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Platinum Silver (Bạc)', 'default' => true],
                    ['label' => 'Ice Blue (Xanh Nhạt)']
                ]]
            ],
            20 => [
                ['id' => 'spec', 'title' => 'Chọn cấu hình Ryzen AI:', 'values' => [
                    ['label' => 'Ryzen 7 8845HS / 32GB / 1TB SSD', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Ryzen 7 8845HS / 16GB / 512GB SSD', 'price_delta' => -1800000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Xám Không Gian', 'default' => true]
                ]]
            ],

            // Tablet (21 -> 26)
            21 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng lưu trữ:', 'values' => [
                    ['label' => '256GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '512GB', 'price_delta' => 5000000],
                    ['label' => '1TB', 'price_delta' => 12000000]
                ]],
                ['id' => 'network', 'title' => 'Chọn kết nối mạng:', 'values' => [
                    ['label' => 'Wi-Fi', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Wi-Fi + 5G Cellular', 'price_delta' => 4500000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Space Black (Đen)', 'default' => true],
                    ['label' => 'Silver (Bạc)']
                ]]
            ],
            22 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng lưu trữ:', 'values' => [
                    ['label' => '128GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '256GB', 'price_delta' => 2500000],
                    ['label' => '512GB', 'price_delta' => 6500000]
                ]],
                ['id' => 'network', 'title' => 'Chọn kết nối mạng:', 'values' => [
                    ['label' => 'Wi-Fi', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Wi-Fi + 5G Cellular', 'price_delta' => 3500000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Space Gray (Xám)', 'default' => true],
                    ['label' => 'Blue (Xanh)'],
                    ['label' => 'Purple (Tím)'],
                    ['label' => 'Starlight (Ánh Sao)']
                ]]
            ],
            23 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng lưu trữ:', 'values' => [
                    ['label' => '64GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '256GB', 'price_delta' => 3200000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc cá tính:', 'values' => [
                    ['label' => 'Pink (Hồng)', 'default' => true],
                    ['label' => 'Blue (Xanh Dương)'],
                    ['label' => 'Yellow (Vàng)'],
                    ['label' => 'Silver (Bạc)']
                ]]
            ],
            24 => [
                ['id' => 'spec', 'title' => 'Chọn phiên bản bộ nhớ & 5G:', 'values' => [
                    ['label' => 'Wi-Fi 256GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '5G 256GB', 'price_delta' => 3000000],
                    ['label' => '5G 512GB', 'price_delta' => 6000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Graphite (Xám Than)', 'default' => true],
                    ['label' => 'Beige (Kem)']
                ]]
            ],
            25 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng lưu trữ:', 'values' => [
                    ['label' => '6GB / 128GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '8GB / 256GB', 'price_delta' => 1800000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc trẻ trung:', 'values' => [
                    ['label' => 'Xám Tự Tin', 'default' => true],
                    ['label' => 'Xanh Mint Năng Động'],
                    ['label' => 'Bạc Tinh Tế']
                ]]
            ],
            26 => [
                ['id' => 'storage', 'title' => 'Chọn dung lượng RAM / ROM:', 'values' => [
                    ['label' => '8GB / 256GB', 'price_delta' => 0, 'default' => true],
                    ['label' => '12GB / 512GB', 'price_delta' => 2200000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Graphite Gray (Xám Than)', 'default' => true]
                ]]
            ],

            // Âm thanh (27 -> 34) - TUYỆT ĐỐI KHÔNG CÓ BỘ NHỚ TRONG!
            27 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc tai nghe:', 'values' => [
                    ['label' => 'Đen Nhám (Black)', 'default' => true],
                    ['label' => 'Bạc Ánh Kim (Silver)'],
                    ['label' => 'Xanh Midnight (Navy)']
                ]]
            ],
            28 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc tai nghe:', 'values' => [
                    ['label' => 'Đen Nhám (Black)', 'default' => true],
                    ['label' => 'Trắng Bạc (Silver)']
                ]]
            ],
            29 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc loa:', 'values' => [
                    ['label' => 'Đen Huyền Bí', 'default' => true],
                    ['label' => 'Xám Khói'],
                    ['label' => 'Xanh Đại Dương']
                ]]
            ],
            30 => [
                ['id' => 'version', 'title' => 'Phiên bản hộp sạc:', 'values' => [
                    ['label' => 'Hộp sạc MagSafe (USB-C)', 'default' => true]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Trắng Tinh Khôi (White)', 'default' => true]
                ]]
            ],
            31 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc thời thượng:', 'values' => [
                    ['label' => 'Midnight (Đen)', 'default' => true],
                    ['label' => 'Starlight (Ánh Sao)'],
                    ['label' => 'Blue (Xanh Dương)'],
                    ['label' => 'Purple (Tím)'],
                    ['label' => 'Orange (Cam Đất)']
                ]]
            ],
            32 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc thời trang:', 'values' => [
                    ['label' => 'Bạc Titan (Silver)', 'default' => true],
                    ['label' => 'Trắng Tinh Khiết (White)']
                ]]
            ],
            33 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc cá tính:', 'values' => [
                    ['label' => 'Đen Obsidian', 'default' => true],
                    ['label' => 'Trắng Tuyết'],
                    ['label' => 'Xanh Cẩm Thạch']
                ]]
            ],
            34 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc loa:', 'values' => [
                    ['label' => 'Đen Thể Thao (Black)', 'default' => true]
                ]]
            ],

            // Smartwatch & Phụ kiện (35 -> 42)
            35 => [
                ['id' => 'band', 'title' => 'Chọn loại dây đeo thể thao:', 'values' => [
                    ['label' => 'Dây Alpine Loop Cam', 'default' => true],
                    ['label' => 'Dây Trail Loop Xanh/Đen'],
                    ['label' => 'Dây Ocean Vàng Đậm']
                ]],
                ['id' => 'case', 'title' => 'Chất liệu vỏ viền:', 'values' => [
                    ['label' => 'Titan Tự Nhiên (49mm)', 'default' => true]
                ]]
            ],
            36 => [
                ['id' => 'size_conn', 'title' => 'Chọn kích thước mặt & Kết nối:', 'values' => [
                    ['label' => '45mm GPS', 'price_delta' => 0, 'default' => true],
                    ['label' => '41mm GPS', 'price_delta' => -1000000],
                    ['label' => '45mm LTE (eSIM)', 'price_delta' => 2500000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc viền:', 'values' => [
                    ['label' => 'Midnight (Đen)', 'default' => true],
                    ['label' => 'Starlight (Trắng Ánh Sao)'],
                    ['label' => 'Silver (Bạc)'],
                    ['label' => 'Pink (Hồng)']
                ]]
            ],
            37 => [
                ['id' => 'band', 'title' => 'Chọn loại dây thể thao:', 'values' => [
                    ['label' => 'Dây Marine Đen Cá Tính', 'default' => true],
                    ['label' => 'Dây Marine Cam Nổi Bật'],
                    ['label' => 'Dây Vải Trail Xám']
                ]],
                ['id' => 'case', 'title' => 'Chọn màu vỏ Titan:', 'values' => [
                    ['label' => 'Titan Xám', 'default' => true],
                    ['label' => 'Titan Trắng'],
                    ['label' => 'Titan Bạc']
                ]]
            ],
            38 => [
                ['id' => 'size', 'title' => 'Chọn kích thước mặt:', 'values' => [
                    ['label' => '43mm', 'price_delta' => 0, 'default' => true],
                    ['label' => '47mm', 'price_delta' => 800000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Đen Cổ Điển', 'default' => true],
                    ['label' => 'Bạc Lịch Lãm']
                ]]
            ],
            39 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Đen Phantom', 'default' => true],
                    ['label' => 'Xám Ánh Kim']
                ]],
                ['id' => 'bundle', 'title' => 'Gói combo ưu đãi:', 'values' => [
                    ['label' => 'Bản tiêu chuẩn (Không kèm cáp)', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Combo kèm cáp Type-C 100W 1.8m', 'price_delta' => 120000]
                ]]
            ],
            40 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Đen Phantom', 'default' => true]
                ]],
                ['id' => 'bundle', 'title' => 'Gói combo ưu đãi:', 'values' => [
                    ['label' => 'Bản tiêu chuẩn (Không kèm cáp)', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Combo kèm cáp Type-C 140W bọc dù', 'price_delta' => 180000]
                ]]
            ],
            41 => [
                ['id' => 'color', 'title' => 'Chọn màu sắc chuột:', 'values' => [
                    ['label' => 'Graphite (Đen Than Chì)', 'default' => true],
                    ['label' => 'Pale Gray (Xám Sáng)'],
                    ['label' => 'Space Black (Đen Bóng)']
                ]]
            ],
            42 => [
                ['id' => 'switch', 'title' => 'Chọn loại Switch cơ học:', 'values' => [
                    ['label' => 'Tactile Quiet (Gõ êm, cảm giác khấc nhẹ)', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Linear (Gõ mượt mà, tĩnh lặng)', 'price_delta' => 0],
                    ['label' => 'Clicky (Gõ đanh nảy, âm thanh sắc)', 'price_delta' => 0]
                ]],
                ['id' => 'layout', 'title' => 'Chọn kích thước Layout:', 'values' => [
                    ['label' => 'Fullsize (100% đầy đủ phím số)', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Mini TKL (75% nhỏ gọn di động)', 'price_delta' => -200000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Xám Than Chì (Graphite)', 'price_delta' => 0, 'default' => true]
                ]]
            ]
        ];

        if (isset($catalogOptions[$id])) {
            return $catalogOptions[$id];
        }

        // 2. Fallback suy luận thông minh nếu là sản phẩm mới thêm vào
        // Trường hợp: Bàn phím cơ
        if (str_contains($name, 'bàn phím') || str_contains($name, 'keyboard')) {
            return [
                ['id' => 'switch', 'title' => 'Chọn loại Switch:', 'values' => [
                    ['label' => 'Tactile (Nâu - Gõ êm)', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Linear (Đỏ - Gõ mượt)', 'price_delta' => 0],
                    ['label' => 'Clicky (Xanh - Gõ nảy)', 'price_delta' => 0]
                ]],
                ['id' => 'layout', 'title' => 'Kích thước Layout:', 'values' => [
                    ['label' => 'Fullsize (100%)', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Tenkeyless TKL (80%)', 'price_delta' => -150000]
                ]],
                ['id' => 'color', 'title' => 'Màu sắc:', 'values' => [
                    ['label' => 'Đen / Xám Than Chì', 'default' => true],
                    ['label' => 'Trắng Tinh Tế']
                ]]
            ];
        }

        // Trường hợp: Chuột máy tính
        if (str_contains($name, 'chuột') || str_contains($name, 'mouse')) {
            return [
                ['id' => 'color', 'title' => 'Chọn màu sắc chuột:', 'values' => [
                    ['label' => 'Đen Nhám (Black)', 'default' => true],
                    ['label' => 'Xám Sáng (Gray)'],
                    ['label' => 'Trắng Tuyết (White)']
                ]]
            ];
        }

        // Trường hợp: Củ sạc, Cáp sạc, Pin dự phòng
        if (str_contains($name, 'sạc') || str_contains($name, 'charger') || str_contains($name, 'adapter')) {
            return [
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Đen Phantom', 'default' => true],
                    ['label' => 'Trắng Tinh Tế']
                ]],
                ['id' => 'bundle', 'title' => 'Gói sản phẩm:', 'values' => [
                    ['label' => 'Bản tiêu chuẩn', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Kèm cáp sạc nhanh Type-C', 'price_delta' => 100000]
                ]]
            ];
        }

        // Trường hợp: Âm thanh (Tai nghe & Loa) -> KHÔNG CÓ BỘ NHỚ
        if ($catId === 4 || str_contains($name, 'tai nghe') || str_contains($name, 'loa') || str_contains($name, 'headphone') || str_contains($name, 'earbuds') || str_contains($name, 'airpods')) {
            return [
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Đen Nhám (Black)', 'default' => true],
                    ['label' => 'Trắng Tinh Tế (White)'],
                    ['label' => 'Xám Bạc (Silver)']
                ]]
            ];
        }

        // Trường hợp: Smartwatch
        if ($catId === 5 && (str_contains($name, 'watch') || str_contains($name, 'đồng hồ'))) {
            return [
                ['id' => 'size', 'title' => 'Chọn kích thước mặt:', 'values' => [
                    ['label' => 'Tiêu chuẩn (41mm/43mm)', 'price_delta' => 0, 'default' => true],
                    ['label' => 'Bản lớn (45mm/47mm)', 'price_delta' => 800000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc & Dây đeo:', 'values' => [
                    ['label' => 'Đen Đậm - Dây Thể Thao', 'default' => true],
                    ['label' => 'Bạc Sáng - Dây Vải'],
                    ['label' => 'Titan Xám - Dây Da']
                ]]
            ];
        }

        // Trường hợp: Laptop
        if ($catId === 2 || str_contains($name, 'laptop') || str_contains($name, 'macbook')) {
            $ram = $product['ram'] ?? '16GB';
            $rom = $product['rom'] ?? '512GB SSD';
            return [
                ['id' => 'spec', 'title' => 'Chọn cấu hình hiệu năng:', 'values' => [
                    ['label' => "{$ram} | {$rom}", 'price_delta' => 0, 'default' => true],
                    ['label' => 'Nâng cấp dung lượng cao hơn', 'price_delta' => 3000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Xám Không Gian (Space Gray)', 'default' => true],
                    ['label' => 'Bạc Ánh Kim (Silver)'],
                    ['label' => 'Đen Nhám']
                ]]
            ];
        }

        // Trường hợp: Máy tính bảng (Tablet)
        if ($catId === 3 || str_contains($name, 'tablet') || str_contains($name, 'ipad') || str_contains($name, 'tab')) {
            $rom = $product['rom'] ?? '128GB';
            return [
                ['id' => 'storage', 'title' => 'Chọn dung lượng lưu trữ:', 'values' => [
                    ['label' => $rom, 'price_delta' => 0, 'default' => true],
                    ['label' => '256GB', 'price_delta' => 2000000],
                    ['label' => '512GB', 'price_delta' => 5000000]
                ]],
                ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                    ['label' => 'Xám Không Gian', 'default' => true],
                    ['label' => 'Bạc'],
                    ['label' => 'Xanh Pastel']
                ]]
            ];
        }

        // Mặc định: Điện thoại Smartphone
        $rom = $product['rom'] ?? '256GB';
        return [
            ['id' => 'storage', 'title' => 'Chọn dung lượng bộ nhớ:', 'values' => [
                ['label' => $rom, 'price_delta' => 0, 'default' => true],
                ['label' => '512GB', 'price_delta' => 3000000]
            ]],
            ['id' => 'color', 'title' => 'Chọn màu sắc:', 'values' => [
                ['label' => 'Đen Huyền Bí', 'default' => true],
                ['label' => 'Trắng Tinh Khôi'],
                ['label' => 'Titan Tự Nhiên']
            ]]
        ];
    }
}
