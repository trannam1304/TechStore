# -*- coding: utf-8 -*-
with open('api/models/ProductModel.php', 'r', encoding='utf-8') as f:
    c = f.read()

target = """        // 2. Lọc theo Danh mục
        if (!empty($filters['category_id'])) {
            $conditions[] = "category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        // 3. Lọc theo Thương hiệu
        if (!empty($filters['brand_id'])) {
            $conditions[] = "brand_id = ?";
            $params[] = (int)$filters['brand_id'];
        }"""

replacement = """        // 2. Lọc theo Danh mục (Hỗ trợ category_id, category, cat, category_slug)
        $catParam = $filters['category_id'] ?? $filters['category'] ?? $filters['cat'] ?? null;
        if (!empty($catParam) && $catParam !== 'all' && $catParam !== '0') {
            if (is_numeric($catParam)) {
                $conditions[] = "category_id = ?";
                $params[] = (int)$catParam;
            } else {
                $conditions[] = "(category_name = ? OR category_id IN (SELECT id FROM categories WHERE slug = ?))";
                $params[] = $catParam;
                $params[] = $catParam;
            }
        }

        // 3. Lọc theo Thương hiệu (Hỗ trợ brand_id, brand)
        $brandParam = $filters['brand_id'] ?? $filters['brand'] ?? null;
        if (!empty($brandParam) && $brandParam !== 'all' && $brandParam !== '0') {
            if (is_numeric($brandParam)) {
                $conditions[] = "brand_id = ?";
                $params[] = (int)$brandParam;
            } else {
                $conditions[] = "(brand_name = ? OR brand_id IN (SELECT id FROM brands WHERE slug = ?))";
                $params[] = $brandParam;
                $params[] = $brandParam;
            }
        }"""

c_norm = c.replace('\r\n', '\n')
target_norm = target.replace('\r\n', '\n')
if target_norm in c_norm:
    c_norm = c_norm.replace(target_norm, replacement)
    
    # Also update normalizeProductSpecs for category 5 and 6
    old_spec = "// Ngành 5: Smartwatch & Phụ kiện\n            elseif ($catId === 5 ||"
    new_spec = "// Ngành 5: Smartwatch & Ngành 6: Phụ kiện chính hãng\n            elseif ($catId === 5 || $catId === 6 ||"
    c_norm = c_norm.replace(old_spec, new_spec)

    with open('api/models/ProductModel.php', 'w', encoding='utf-8') as f:
        f.write(c_norm)
    print('Successfully updated ProductModel.php')
else:
    print('Target not found in ProductModel.php')

# Update ProductController.php to add getProducts alias
with open('api/controllers/ProductController.php', 'r', encoding='utf-8') as f:
    pc = f.read().replace('\r\n', '\n')

old_pc = """    public static function index(array $query): void {
        $result = ProductModel::paginate($query);
        Response::paginate(
            $result['items'],
            $result['total'],
            $result['page'],
            $result['limit'],
            'Lấy danh sách sản phẩm thành công'
        );
    }"""

new_pc = """    public static function index(array $query = []): void {
        $result = ProductModel::paginate($query);
        Response::paginate(
            $result['items'],
            $result['total'],
            $result['page'],
            $result['limit'],
            'Lấy danh sách sản phẩm thành công'
        );
    }

    /**
     * Alias getProducts cho index
     */
    public static function getProducts(array $query = []): void {
        self::index($query);
    }"""

if old_pc in pc:
    pc = pc.replace(old_pc, new_pc)
    with open('api/controllers/ProductController.php', 'w', encoding='utf-8') as f:
        f.write(pc)
    print('Successfully updated ProductController.php')
else:
    print('old_pc not found in ProductController.php')

