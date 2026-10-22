<?php
/**
 * TechStore - Product Controller (6 APIs)
 * Author: Nguyen Thanh Trung (Back-end & Algorithm Specialist)
 * Endpoints: GET /products, GET /products/{id}, POST /products, PUT /products/{id}, PATCH /products/{id}, DELETE /products/{id}
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;
use Api\Helpers\Validator;

class ProductController extends BaseController {
    /**
     * GET /products
     * Hỗ trợ tìm kiếm, lọc đa tiêu chí (hãng, danh mục, giá, RAM/ROM), sắp xếp và phân trang
     */
    public function index(Request $request): void {
        $category = $request->query('category');
        $brand = $request->query('brand');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $ram = $request->query('ram');
        $rom = $request->query('rom');
        $search = $request->query('q') ?? $request->query('search');
        $sort = $request->query('sort', 'featured');
        $page = max(1, (int)$request->query('page', 1));
        $limit = max(1, min(50, (int)$request->query('limit', 12)));
        $offset = ($page - 1) * $limit;

        $conditions = ["1 = 1"];
        $params = [];

        if ($category !== null && $category !== '') {
            if (is_numeric($category)) {
                $conditions[] = "category_id = :cat_id";
                $params['cat_id'] = (int)$category;
            } else {
                $conditions[] = "category_name LIKE :cat_name";
                $params['cat_name'] = "%{$category}%";
            }
        }

        if ($brand !== null && $brand !== '') {
            if (is_numeric($brand)) {
                $conditions[] = "brand_id = :brand_id";
                $params['brand_id'] = (int)$brand;
            } else {
                $conditions[] = "brand_name LIKE :brand_name";
                $params['brand_name'] = "%{$brand}%";
            }
        }

        if ($minPrice !== null && is_numeric($minPrice)) {
            $conditions[] = "price >= :min_price";
            $params['min_price'] = (float)$minPrice;
        }

        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $conditions[] = "price <= :max_price";
            $params['max_price'] = (float)$maxPrice;
        }

        if ($ram !== null && $ram !== '') {
            $conditions[] = "ram LIKE :ram";
            $params['ram'] = "%{$ram}%";
        }

        if ($rom !== null && $rom !== '') {
            $conditions[] = "rom LIKE :rom";
            $params['rom'] = "%{$rom}%";
        }

        if ($search !== null && trim($search) !== '') {
            $conditions[] = "(name LIKE :kw OR description LIKE :kw OR chipset LIKE :kw)";
            $params['kw'] = "%" . trim($search) . "%";
        }

        $whereClause = implode(" AND ", $conditions);

        // Sorting
        $orderBy = match ($sort) {
            'price_asc' => 'price ASC',
            'price_desc' => 'price DESC',
            'rating' => 'rating DESC, review_count DESC',
            'newest' => 'created_at DESC',
            default => 'featured DESC, id DESC'
        };

        // Total count
        $countSql = "SELECT COUNT(*) as total FROM products WHERE {$whereClause}";
        $totalRow = $this->model->queryOne($countSql, $params);
        $total = (int)($totalRow['total'] ?? 0);

        // Fetch products
        $dataSql = "SELECT * FROM products WHERE {$whereClause} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}";
        $items = $this->model->query($dataSql, $params);

        // Unpack json images array if needed
        foreach ($items as &$item) {
            if (!empty($item['images']) && is_string($item['images'])) {
                $item['images'] = json_decode($item['images'], true) ?? [];
            }
        }

        Response::paginated($items, $total, $page, $limit, 'Lấy danh sách sản phẩm thành công');
    }

    /**
     * GET /products/{id}
     */
    public function show(Request $request): void {
        $id = (int)$request->param('id');
        $product = $this->model->queryOne("SELECT * FROM products WHERE id = :id", ['id' => $id]);

        if (!$product) {
            Response::error('Không tìm thấy sản phẩm yêu cầu', 'PRODUCT_NOT_FOUND', 404);
        }

        if (!empty($product['images']) && is_string($product['images'])) {
            $product['images'] = json_decode($product['images'], true) ?? [];
        }

        Response::json($product);
    }

    /**
     * POST /products (Admin only)
     */
    public function store(Request $request): void {
        $body = $request->body();
        if (empty($body['name']) || !isset($body['price'])) {
            Response::error('Vui lòng cung cấp tên và giá sản phẩm', 'VALIDATION_ERROR', 422);
        }

        // Auto resolve category & brand names
        $cat = $this->model->queryOne("SELECT name FROM categories WHERE id = :id", ['id' => $body['category_id']]);
        $brand = $this->model->queryOne("SELECT name FROM brands WHERE id = :id", ['id' => $body['brand_id']]);

        $images = is_array($body['images'] ?? null) ? json_encode($body['images']) : json_encode([$body['image'] ?? '']);

        $sql = "INSERT INTO products (name, category_id, category_name, brand_id, brand_name, price, original_price,
                                     stock, rating, review_count, image, images, ram, rom, chipset, screen,
                                     battery, camera, description, featured, created_at)
                VALUES (:name, :cat_id, :cat_name, :brand_id, :brand_name, :price, :orig_price,
                        :stock, 5.0, 0, :image, :images, :ram, :rom, :chipset, :screen,
                        :battery, :camera, :desc, :featured, NOW())";

        $this->model->execute($sql, [
            'name' => $body['name'],
            'cat_id' => $body['category_id'],
            'cat_name' => $cat['name'] ?? 'Khác',
            'brand_id' => $body['brand_id'],
            'brand_name' => $brand['name'] ?? 'Khác',
            'price' => (float)$body['price'],
            'orig_price' => !empty($body['original_price']) ? (float)$body['original_price'] : (float)$body['price'],
            'stock' => (int)($body['stock'] ?? 10),
            'image' => $body['image'] ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600',
            'images' => $images,
            'ram' => $body['ram'] ?? null,
            'rom' => $body['rom'] ?? null,
            'chipset' => $body['chipset'] ?? null,
            'screen' => $body['screen'] ?? null,
            'battery' => $body['battery'] ?? null,
            'camera' => $body['camera'] ?? null,
            'desc' => $body['description'] ?? '',
            'featured' => !empty($body['featured']) ? 1 : 0
        ]);

        $newId = $this->model->lastInsertId();
        $created = $this->model->queryOne("SELECT * FROM products WHERE id = :id", ['id' => $newId]);

        Response::json($created, 201, true, 'Thêm mới sản phẩm thành công');
    }

    /**
     * PUT /products/{id} (Admin only)
     */
    public function update(Request $request): void {
        $id = (int)$request->param('id');
        $body = $request->body();

        $existing = $this->model->queryOne("SELECT id FROM products WHERE id = :id", ['id' => $id]);
        if (!$existing) {
            Response::error('Không tìm thấy sản phẩm cần cập nhật', 'NOT_FOUND', 404);
        }

        $sql = "UPDATE products SET name = :name, price = :price, original_price = :orig_price,
                                   stock = :stock, ram = :ram, rom = :rom, chipset = :chipset,
                                   screen = :screen, battery = :battery, camera = :camera,
                                   description = :desc, featured = :featured
                WHERE id = :id";

        $this->model->execute($sql, [
            'id' => $id,
            'name' => $body['name'] ?? 'Sản phẩm',
            'price' => (float)($body['price'] ?? 0),
            'orig_price' => (float)($body['original_price'] ?? 0),
            'stock' => (int)($body['stock'] ?? 0),
            'ram' => $body['ram'] ?? null,
            'rom' => $body['rom'] ?? null,
            'chipset' => $body['chipset'] ?? null,
            'screen' => $body['screen'] ?? null,
            'battery' => $body['battery'] ?? null,
            'camera' => $body['camera'] ?? null,
            'desc' => $body['description'] ?? '',
            'featured' => !empty($body['featured']) ? 1 : 0
        ]);

        $updated = $this->model->queryOne("SELECT * FROM products WHERE id = :id", ['id' => $id]);
        Response::json($updated, 200, true, 'Cập nhật sản phẩm thành công');
    }

    /**
     * PATCH /products/{id} (Admin only)
     */
    public function patch(Request $request): void {
        $id = (int)$request->param('id');
        $body = $request->body();

        $existing = $this->model->queryOne("SELECT * FROM products WHERE id = :id", ['id' => $id]);
        if (!$existing) {
            Response::error('Không tìm thấy sản phẩm', 'NOT_FOUND', 404);
        }

        $fields = [];
        $params = ['id' => $id];

        foreach (['price', 'original_price', 'stock', 'featured'] as $key) {
            if (isset($body[$key])) {
                $fields[] = "{$key} = :{$key}";
                $params[$key] = $body[$key];
            }
        }

        if (!empty($fields)) {
            $setClause = implode(', ', $fields);
            $this->model->execute("UPDATE products SET {$setClause} WHERE id = :id", $params);
        }

        $updated = $this->model->queryOne("SELECT * FROM products WHERE id = :id", ['id' => $id]);
        Response::json($updated, 200, true, 'Cập nhật một phần sản phẩm thành công');
    }

    /**
     * DELETE /products/{id} (Admin only)
     */
    public function destroy(Request $request): void {
        $id = (int)$request->param('id');
        $existing = $this->model->queryOne("SELECT id FROM products WHERE id = :id", ['id' => $id]);

        if (!$existing) {
            Response::error('Không tìm thấy sản phẩm để xóa', 'NOT_FOUND', 404);
        }

        $this->model->execute("DELETE FROM products WHERE id = :id", ['id' => $id]);
        Response::json(null, 204, true, 'Đã xóa sản phẩm');
    }
}
