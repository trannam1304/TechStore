# -*- coding: utf-8 -*-

path = 'api/models/ProductModel.php'
with open(path, 'r', encoding='utf-8') as f:
    code = f.read()

code = code.replace('\r\n', '\n')

old_limit = """        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, min(100, (int)($filters['limit'] ?? 12)));
        $offset = ($page - 1) * $limit;"""

new_limit = """        $page = max(1, (int)($filters['page'] ?? 1));
        $limitParam = $filters['limit'] ?? null;
        if ($limitParam === 'all' || $limitParam === '0' || $limitParam === 0) {
            $limit = 1000;
        } elseif (isset($filters['limit']) && is_numeric($filters['limit'])) {
            $limit = max(1, min(500, (int)$filters['limit']));
        } else {
            // Mặc định trả về tối đa 100 sản phẩm để bao quát toàn bộ danh sách sản phẩm
            $limit = 100;
        }
        $offset = ($page - 1) * $limit;"""

old_sort = """        // 7. Sắp xếp
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
        }"""

new_sort = """        // 7. Sắp xếp
        $sortOrder = "featured DESC, id ASC";
        $sort = $filters['sort'] ?? '';
        switch ($sort) {
            case 'price_asc':
                $sortOrder = "price ASC";
                break;
            case 'price_desc':
                $sortOrder = "price DESC";
                break;
            case 'rating_desc':
            case 'rating':
                $sortOrder = "rating DESC";
                break;
            case 'views_desc':
                $sortOrder = "view_count DESC";
                break;
            case 'newest':
                $sortOrder = "id DESC";
                break;
            case 'featured':
            default:
                $sortOrder = "featured DESC, id ASC";
                break;
        }"""

if old_limit in code:
    code = code.replace(old_limit, new_limit, 1)
    print("Replaced limit logic in ProductModel.php!")
else:
    print("Warning: old_limit not found!")

if old_sort in code:
    code = code.replace(old_sort, new_sort, 1)
    print("Replaced sort logic in ProductModel.php!")
else:
    print("Warning: old_sort not found!")

with open(path, 'w', encoding='utf-8') as f:
    f.write(code)

print("ProductModel.php saved!")

