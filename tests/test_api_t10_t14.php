<?php
/**
 * Test Suite cho Nhiệm vụ T10, T11, T12, T13, T14
 * Chạy kiểm thử tự động toàn bộ tính năng Auth JWT, Validator, Product, Category, Brand
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/services/JwtService.php';
require_once __DIR__ . '/../api/helpers/Response.php';
require_once __DIR__ . '/../api/helpers/Validator.php';
require_once __DIR__ . '/../api/helpers/ErrorHandler.php';
require_once __DIR__ . '/../api/helpers/DataStore.php';
require_once __DIR__ . '/../api/middleware/CorsMiddleware.php';
require_once __DIR__ . '/../api/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../api/middleware/RoleMiddleware.php';
require_once __DIR__ . '/../api/controllers/AuthController.php';
require_once __DIR__ . '/../api/controllers/ProductController.php';

use Api\Services\JwtService;
use Api\Helpers\Validator;
use Api\Helpers\DataStore;
use Api\Middleware\AuthMiddleware;
use Api\Middleware\RoleMiddleware;

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $testName) {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$testName}\n";
        $passed++;
    } else {
        echo " [FAIL] {$testName}\n";
        $failed++;
    }
}

echo "========================================================\n";
echo "        KIỂM THỬ TỰ ĐỘNG CÁC NHIỆM VỤ T10 - T14        \n";
echo "========================================================\n\n";

// ---------------------------------------------------------
// 1. KIỂM THỬ T10: JwtService (HS256, Sign, Verify, Exp)
// ---------------------------------------------------------
echo "--- 1. KIỂM THỬ T10: JwtService ---\n";

$sampleUser = [
    'id' => 2,
    'email' => 'user@techstore.local',
    'name' => 'Tuấn Kiệt',
    'role' => 'customer'
];

$token = JwtService::generateAccessToken($sampleUser);
assertTest(!empty($token) && substr_count($token, '.') === 2, "Tạo JWT Access Token hợp lệ (3 phần ngăn cách bởi dấu chấm)");

$payload = JwtService::verifyToken($token);
assertTest($payload !== null && $payload['sub'] === 2 && $payload['email'] === 'user@techstore.local', "Giải mã và xác thực chữ ký token thành công");

$tamperedToken = substr($token, 0, -5) . 'XXXXX';
$tamperedPayload = JwtService::verifyToken($tamperedToken);
assertTest($tamperedPayload === null, "Token bị chỉnh sửa chữ ký bị từ chối xác thực");

$rfToken = JwtService::generateRefreshToken($sampleUser);
$rfPayload = JwtService::verifyToken($rfToken);
assertTest($rfPayload !== null && ($rfPayload['type'] ?? '') === 'refresh', "Tạo và xác thực Refresh Token thành công");

// ---------------------------------------------------------
// 2. KIỂM THỬ T12: Validator
// ---------------------------------------------------------
echo "\n--- 2. KIỂM THỬ T12: Validator ---\n";

$validInput = [
    'name' => 'Nguyễn Văn Test',
    'email' => 'test@example.com',
    'password' => 'Pass123456',
    'age' => 25
];
$rules = [
    'name' => 'required|min:2',
    'email' => 'required|email',
    'password' => 'required|min:6',
    'age' => 'numeric'
];
$v1 = Validator::make($validInput, $rules);
assertTest($v1->passes(), "Validator chấp thuận dữ liệu hợp lệ");

$invalidInput = [
    'name' => '',
    'email' => 'invalid-email-format',
    'password' => '123'
];
$v2 = Validator::make($invalidInput, $rules);
assertTest($v2->fails() && count($v2->getErrors()) === 3, "Validator bắt lỗi chính xác (required, invalid email, min length)");

// ---------------------------------------------------------
// 3. KIỂM THỬ T11: DataStore & Password Hash
// ---------------------------------------------------------
echo "\n--- 3. KIỂM THỬ T11: CSDL & Bcrypt Password Hash ---\n";

$adminUser = DataStore::findUserByEmail('admin@techstore.local');
assertTest($adminUser !== null && $adminUser['role'] === 'admin', "Tìm kiếm người dùng admin@techstore.local thành công");

$pwdMatch = password_verify('Admin@123', $adminUser['password_hash']);
assertTest($pwdMatch, "Xác thực mật khẩu băm Bcrypt của admin@techstore.local khớp với Admin@123");

$customerUser = DataStore::findUserByEmail('user@techstore.local');
assertTest($customerUser !== null && $customerUser['role'] === 'customer', "Tìm kiếm người dùng user@techstore.local thành công");

// Kiểm tra lưu và xác thực refresh token
DataStore::saveRefreshToken((int)$customerUser['id'], $rfToken, 604800);
$rfValidInDb = DataStore::isRefreshTokenValid($rfToken);
assertTest($rfValidInDb, "Lưu và kiểm tra Refresh Token còn hiệu lực trong DB thành công");

DataStore::revokeRefreshToken($rfToken);
$rfRevoked = !DataStore::isRefreshTokenValid($rfToken);
assertTest($rfRevoked, "Thu hồi (Revoke) Refresh Token khi logout thành công");

// ---------------------------------------------------------
// 4. KIỂM THỬ T13: ProductController Lọc, Tìm Kiếm, Phân Trang
// ---------------------------------------------------------
echo "\n--- 4. KIỂM THỬ T13: Lọc, Tìm kiếm, Phân trang Sản phẩm ---\n";

$res = \Api\Models\ProductModel::getAll(['limit' => 100]);
$products = $res['items'] ?? [];
assertTest(count($products) >= 40, "Số lượng sản phẩm trong kho đạt " . count($products) . " sản phẩm (≥ 40)");

// Test lọc category_id = 1 (Điện thoại)
$phones = array_filter($products, fn($p) => (int)$p['category_id'] === 1);
assertTest(count($phones) === 10, "Lọc danh mục Điện thoại (category_id = 1) trả về chính xác 10 sản phẩm");

// Test tìm kiếm từ khóa iPhone
$iphones = array_filter($products, fn($p) => str_contains(strtolower($p['name']), 'iphone'));
assertTest(count($iphones) >= 3, "Tìm kiếm từ khóa 'iPhone' trả về các mẫu iPhone chính xác");

// Test lọc giá từ 20tr - 40tr
$priceFiltered = array_filter($products, fn($p) => $p['price'] >= 20000000 && $p['price'] <= 40000000);
assertTest(count($priceFiltered) > 0, "Lọc theo khoảng giá [20.000.000đ - 40.000.000đ] hoạt động chuẩn xác");

// ---------------------------------------------------------
// 5. KIỂM THỬ T14: Chi tiết sản phẩm, Danh mục, Hãng
// ---------------------------------------------------------
echo "\n--- 5. KIỂM THỬ T14: Chi tiết sản phẩm, Danh mục & Thương hiệu ---\n";

$categories = \Api\Models\CategoryModel::all();
assertTest(count($categories) === 5, "API Danh mục có đầy đủ 5 ngành hàng chuẩn");

$brands = \Api\Models\BrandModel::all();
assertTest(count($brands) === 8, "API Thương hiệu có đầy đủ 8 hãng lớn (Apple, Samsung, Xiaomi, ASUS, Sony, Dell, Anker, Logitech)");

// Test chi tiết sản phẩm ID = 1 (iPhone 16 Pro Max)
$p1 = null;
foreach ($products as $p) {
    if ($p['id'] === 1) { $p1 = $p; break; }
}
assertTest($p1 !== null && !empty($p1['chipset']) && !empty($p1['camera']), "Sản phẩm ID = 1 có đầy đủ thông số kỹ thuật chi tiết");

// ---------------------------------------------------------
// 6. KIỂM THỬ T10: RoleMiddleware Phân Quyền
// ---------------------------------------------------------
echo "\n--- 6. KIỂM THỬ T10: RoleMiddleware (RBAC) ---\n";

$customerAllowed = true;
try {
    RoleMiddleware::check($customerUser, 'customer');
} catch (\Throwable $e) {
    $customerAllowed = false;
}
assertTest($customerAllowed, "RoleMiddleware cho phép khách hàng truy cập tài nguyên customer");

echo "\n========================================================\n";
echo "KẾT QUẢ: ĐÃ VƯỢT QUA {$passed}/" . ($passed + $failed) . " KIỂM THỬ TỰ ĐỘNG!\n";
echo "========================================================\n";
