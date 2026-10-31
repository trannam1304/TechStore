<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/models/ProductModel.php';
require_once __DIR__ . '/../api/models/CategoryModel.php';
require_once __DIR__ . '/../api/models/BrandModel.php';

use Api\Models\ProductModel;
use Api\Models\CategoryModel;
use Api\Models\BrandModel;

echo "=== TEST 1: CATEGORIES COUNT ===\n";
$cats = CategoryModel::all();
foreach ($cats as $c) {
    echo "Category [{$c['id']}] {$c['name']} (slug: {$c['slug']}): {$c['product_count']} products\n";
}

echo "\n=== TEST 2: ALL PRODUCTS ===\n";
$all = ProductModel::getAll(['limit' => 100]);
echo "Total products count: " . count($all['items']) . " (Total in DB: {$all['pagination']['total_items']})\n";

echo "\n=== TEST 3: FILTER BY category_id = 5 (Smartwatch) ===\n";
$watch = ProductModel::getAll(['category_id' => 5, 'limit' => 50]);
echo "Smartwatch products count: " . count($watch['items']) . "\n";
foreach ($watch['items'] as $p) {
    echo "  - [ID {$p['id']}] {$p['name']} ({$p['brand_name']}) - " . number_format($p['price']) . " VND\n";
}

echo "\n=== TEST 4: FILTER BY category_id = 6 (Phụ kiện chính hãng) ===\n";
$acc = ProductModel::getAll(['category_id' => 6, 'limit' => 50]);
echo "Accessory products count: " . count($acc['items']) . "\n";
foreach ($acc['items'] as $p) {
    echo "  - [ID {$p['id']}] {$p['name']} ({$p['brand_name']}) - " . number_format($p['price']) . " VND\n";
}

echo "\n=== TEST 5: FILTER BY category_id = 'all' ===\n";
$allFilter = ProductModel::getAll(['category_id' => 'all', 'limit' => 100]);
echo "Category 'all' count: " . count($allFilter['items']) . "\n";

echo "\n=== TEST 6: FILTER BY category slug = 'smartwatch' ===\n";
$watchSlug = ProductModel::getAll(['category' => 'smartwatch', 'limit' => 50]);
echo "Smartwatch by slug count: " . count($watchSlug['items']) . "\n";

echo "\n=== ALL TESTS COMPLETED ===\n";

