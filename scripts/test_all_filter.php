<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../api/models/ProductModel.php';

$res = Api\Models\ProductModel::getAll();
echo "Total items returned: " . count($res['items']) . PHP_EOL;
echo "Pagination total_items: " . $res['pagination']['total_items'] . PHP_EOL;
echo "First 12 products for 'Tất cả sản phẩm':" . PHP_EOL;
foreach (array_slice($res['items'], 0, 12) as $p) {
    echo "  - [ID {$p['id']}] {$p['name']} (Cat: {$p['category_name']}, Featured: {$p['featured']})" . PHP_EOL;
}
