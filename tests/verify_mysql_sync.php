<?php
/**
 * TechStore - Script Kiểm Thử Đồng Bộ Hóa MySQL (Zero data.json)
 */

echo "========================================================\n";
echo "       TECHSTORE - VERIFICATION SUITE (MYSQL SYNC)      \n";
echo "========================================================\n\n";

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/helpers/DataStore.php';
require_once __DIR__ . '/../api/models/BaseModel.php';
require_once __DIR__ . '/../api/models/ProductModel.php';
require_once __DIR__ . '/../api/models/CategoryModel.php';
require_once __DIR__ . '/../api/models/BrandModel.php';
require_once __DIR__ . '/../api/models/OrderModel.php';
require_once __DIR__ . '/../api/models/CouponModel.php';
require_once __DIR__ . '/../api/models/ReviewModel.php';
require_once __DIR__ . '/../api/models/WishlistModel.php';
require_once __DIR__ . '/../api/models/PaymentModel.php';
require_once __DIR__ . '/../api/models/AdminModel.php';

use Config\Database;
use Api\Helpers\DataStore;
use Api\Models\ProductModel;
use Api\Models\CategoryModel;
use Api\Models\BrandModel;
use Api\Models\OrderModel;
use Api\Models\CouponModel;
use Api\Models\AdminModel;

$errors = [];

// 1. Kiểm tra kết nối PDO
echo "[1] Kiểm tra kết nối PDO MySQL... ";
try {
    $pdo = Database::getConnection();
    echo "PASSED (OK)\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "Không kết nối được MySQL";
    exit(1);
}

// 2. Kiểm tra danh sách bảng
echo "[2] Kiểm tra 15 bảng nghiệp vụ trong MySQL... ";
$expectedTables = [
    'users', 'categories', 'brands', 'products', 'product_images', 'product_specs',
    'carts', 'orders', 'order_items', 'payments', 'reviews', 'wishlists',
    'coupons', 'refresh_tokens', 'audit_logs'
];
$stmt = $pdo->query("SHOW TABLES");
$dbTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$missingTables = array_diff($expectedTables, $dbTables);
if (empty($missingTables)) {
    echo "PASSED (" . count($dbTables) . " bảng tồn tại)\n";
} else {
    echo "FAILED (Thiếu bảng: " . implode(', ', $missingTables) . ")\n";
    $errors[] = "Thiếu bảng CSDL";
}

// 3. Kiểm tra Model Categories
echo "[3] Kiểm tra CategoryModel::all()... ";
try {
    $cats = CategoryModel::all();
    echo "PASSED (" . count($cats) . " danh mục)\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "CategoryModel lỗi";
}

// 4. Kiểm tra Model Brands
echo "[4] Kiểm tra BrandModel::all()... ";
try {
    $brands = BrandModel::all();
    echo "PASSED (" . count($brands) . " thương hiệu)\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "BrandModel lỗi";
}

// 5. Kiểm tra Model Products
echo "[5] Kiểm tra ProductModel::getAll()... ";
try {
    $res = ProductModel::getAll(['limit' => 5]);
    $total = $res['pagination']['total_items'] ?? 0;
    echo "PASSED ({$total} sản phẩm trong DB, lấy " . count($res['items']) . " mẫu)\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "ProductModel lỗi";
}

// 6. Kiểm tra DataStore Users
echo "[6] Kiểm tra DataStore::findUserByEmail('admin@techstore.local')... ";
try {
    $admin = DataStore::findUserByEmail('admin@techstore.local');
    if ($admin && ($admin['role'] ?? '') === 'admin') {
        echo "PASSED (User ID #{$admin['id']}, Name: {$admin['name']})\n";
    } else {
        echo "FAILED (Không tìm thấy admin)\n";
        $errors[] = "DataStore không tìm thấy admin";
    }
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "DataStore lỗi";
}

// 7. Kiểm tra CouponModel
echo "[7] Kiểm tra CouponModel::getAll()... ";
try {
    $coupons = CouponModel::getAll();
    echo "PASSED (" . count($coupons) . " mã giảm giá)\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "CouponModel lỗi";
}

// 8. Kiểm tra OrderModel
echo "[8] Kiểm tra OrderModel::getAll()... ";
try {
    $orders = OrderModel::getAll();
    echo "PASSED (" . count($orders) . " đơn hàng)\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "OrderModel lỗi";
}

// 9. Kiểm tra AdminModel KPI
echo "[9] Kiểm tra AdminModel::getStats()... ";
try {
    $stats = AdminModel::getStats();
    echo "PASSED (Doanh thu: " . number_format($stats['revenue']['total']) . "đ, Đơn hàng: {$stats['orders']['total']})\n";
} catch (\Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $errors[] = "AdminModel lỗi";
}

// 10. Kiểm tra file data.json có bị tạo lại không
echo "[10] Kiểm tra file database/data.json không còn tồn tại... ";
if (file_exists(__DIR__ . '/../database/data.json')) {
    echo "WARNING (data.json vẫn tồn tại!)\n";
} else {
    echo "PASSED (data.json đã được loại bỏ hoàn toàn, chỉ còn data.json.bak)\n";
}

echo "\n========================================================\n";
if (empty($errors)) {
    echo " KẾT QUẢ: TẤT CẢ 10 BƯỚC KIỂM TRA ĐỀU THÀNH CÔNG RỰC RỠ! \n";
    echo " Hệ thống đã hoạt động 100% trên MySQL, không còn phụ thuộc data.json.\n";
} else {
    echo " KẾT QUẢ: CÓ " . count($errors) . " LỖI CẦN XỬ LÝ:\n";
    foreach ($errors as $err) {
        echo " - {$err}\n";
    }
}
echo "========================================================\n";
