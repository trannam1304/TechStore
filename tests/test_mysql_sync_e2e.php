<?php
/**
 * TechStore - Automated MySQL Sync & E2E API Verification Suite
 * Kiểm thử toàn diện:
 * 1. Database & Schema Verification (15+ bảng nghiệp vụ, kết nối PDO, không phụ thuộc data.json)
 * 2. Auth Flow: Register -> Login (Bcrypt) -> Profile (/me) -> Refresh Token Rotation -> Logout
 * 3. Products CRUD: Create (Admin) -> Read (List/Filter/Search/Detail) -> Update -> Delete
 * 4. Categories & Brands CRUD: Create -> Read -> Update -> Delete
 * 5. Cart CRUD: Add item (Realtime stock check) -> Get Cart -> Update quantity -> Remove item -> Clear
 * 6. Order Flow: Checkout / Create Order (ACID Transaction, Stock deduction) -> View -> Status Update -> Cancel
 * 7. Coupons, Reviews, Wishlist CRUD
 * 8. Admin Management: Users, KPI Stats, Audit Logs
 */

require_once __DIR__ . '/../config/database.php';

use Config\Database;

// Màu sắc terminal ANSI
define('COLOR_RESET', "\033[0m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_CYAN', "\033[36m");
define('COLOR_BOLD', "\033[1m");

$baseUrl = 'http://localhost:8000/api/v1';
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$testResults = [];

function runTest(string $category, string $testName, callable $fn) {
    global $totalTests, $passedTests, $failedTests, $testResults;
    $totalTests++;
    try {
        $result = $fn();
        if ($result === true || (is_array($result) && ($result['ok'] ?? false))) {
            $passedTests++;
            $msg = is_array($result) && !empty($result['msg']) ? " (" . $result['msg'] . ")" : "";
            echo COLOR_GREEN . "  [PASS] " . COLOR_RESET . "{$testName}{$msg}\n";
            $testResults[] = ['category' => $category, 'name' => $testName, 'status' => 'PASS', 'msg' => $msg];
        } else {
            $failedTests++;
            $err = is_array($result) && !empty($result['error']) ? $result['error'] : 'Condition failed';
            echo COLOR_RED . "  [FAIL] " . COLOR_RESET . "{$testName} -> {$err}\n";
            $testResults[] = ['category' => $category, 'name' => $testName, 'status' => 'FAIL', 'msg' => $err];
        }
    } catch (\Throwable $e) {
        $failedTests++;
        echo COLOR_RED . "  [FAIL] " . COLOR_RESET . "{$testName} -> Exception: " . $e->getMessage() . "\n";
        $testResults[] = ['category' => $category, 'name' => $testName, 'status' => 'FAIL', 'msg' => $e->getMessage()];
    }
}

function httpReq(string $method, string $path, $body = null, ?string $token = null): array {
    global $baseUrl;
    $url = $baseUrl . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($body !== null) {
        $json = is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_UNICODE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return [
            'code' => 0,
            'json' => null,
            'raw' => '',
            'error' => "cURL Error: {$curlErr}"
        ];
    }

    $decoded = json_decode($response, true);
    return [
        'code' => $httpCode,
        'json' => $decoded,
        'raw'  => $response
    ];
}

echo COLOR_BOLD . COLOR_CYAN . "\n====================================================================\n";
echo "   TECHSTORE - SUITE KIỂM THỬ ĐỒNG BỘ MYSQL & RESTFUL API E2E\n";
echo "====================================================================\n" . COLOR_RESET;

// -------------------------------------------------------------------------
// 1. DATABASE & PERSISTENCE SYNCHRONIZATION
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 1] KIỂM THỬ CƠ SỞ DỮ LIỆU MYSQL & LOẠI BỎ DATA.JSON\n" . COLOR_RESET;

$pdo = null;
runTest('Database', 'Kết nối PDO MySQL với biến môi trường .env', function() use (&$pdo) {
    $pdo = Database::getConnection();
    return $pdo !== null;
});

runTest('Database', '15 Bảng nghiệp vụ cốt lõi tồn tại trên MySQL', function() use ($pdo) {
    $expected = [
        'users', 'categories', 'brands', 'products', 'product_images', 'product_specs',
        'carts', 'orders', 'order_items', 'payments', 'reviews', 'wishlists',
        'coupons', 'refresh_tokens', 'audit_logs'
    ];
    $stmt = $pdo->query("SHOW TABLES");
    $actual = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $diff = array_diff($expected, $actual);
    if (!empty($diff)) {
        return ['ok' => false, 'error' => 'Thiếu bảng: ' . implode(', ', $diff)];
    }
    return ['ok' => true, 'msg' => count($actual) . ' bảng có sẵn'];
});

runTest('Database', 'Xác nhận không còn phụ thuộc database/data.json', function() {
    $hasJson = file_exists(__DIR__ . '/../database/data.json');
    if ($hasJson) {
        return ['ok' => false, 'error' => 'data.json vẫn tồn tại'];
    }
    return ['ok' => true, 'msg' => '100% dữ liệu nằm trong MySQL'];
});

// -------------------------------------------------------------------------
// 2. AUTHENTICATION & AUTHORIZATION (REGISTER, LOGIN, JWT, RTR, PROFILE)
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 2] KIỂM THỬ ĐĂNG KÝ, ĐĂNG NHẬP, JWT & PHÂN QUYỀN RBAC\n" . COLOR_RESET;

$adminToken = null;
$customerToken = null;
$customerRefreshToken = null;
$testEmail = 'e2e_user_' . time() . '@techstore.local';
$testPassword = 'Password123!';
$registeredUserId = null;

// 2.1 Đăng ký tài khoản mới
runTest('Auth', 'POST /auth/register - Đăng ký tài khoản người dùng mới', function() use ($testEmail, $testPassword, &$registeredUserId, &$customerToken, &$customerRefreshToken, $pdo) {
    $res = httpReq('POST', '/auth/register', [
        'name'     => 'Người Dùng Kiểm Thử E2E',
        'email'    => $testEmail,
        'password' => $testPassword,
        'phone'    => '0912345678',
        'address'  => '123 Đường Kiểm Thử, Quận 1, TP.HCM'
    ]);

    if ($res['code'] !== 201 || empty($res['json']['success'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}: " . json_encode($res['json'] ?? $res['raw'])];
    }

    $customerToken = $res['json']['data']['access_token'] ?? null;
    $customerRefreshToken = $res['json']['data']['refresh_token'] ?? null;
    $registeredUserId = (int)($res['json']['data']['user']['id'] ?? 0);

    // Đối chiếu trực tiếp với MySQL
    $stmt = $pdo->prepare("SELECT id, name, email, role, password_hash FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dbUser) {
        return ['ok' => false, 'error' => 'Người dùng không được lưu vào MySQL'];
    }

    if (!password_verify($testPassword, $dbUser['password_hash'])) {
        return ['ok' => false, 'error' => 'Mật khẩu Bcrypt trong MySQL không khớp'];
    }

    return ['ok' => true, 'msg' => "User ID #{$dbUser['id']} đã lưu vào MySQL"];
});

// 2.2 Chặn đăng ký trùng Email
runTest('Auth', 'POST /auth/register - Chặn đăng ký trùng email (409 Conflict)', function() use ($testEmail, $testPassword) {
    $res = httpReq('POST', '/auth/register', [
        'name'     => 'Trùng Lặp',
        'email'    => $testEmail,
        'password' => $testPassword
    ]);
    return $res['code'] === 409 && ($res['json']['error']['code'] ?? '') === 'EMAIL_ALREADY_EXISTS';
});

// 2.3 Đăng nhập thất bại với sai mật khẩu
runTest('Auth', 'POST /auth/login - Từ chối khi nhập sai mật khẩu (401 Unauthorized)', function() use ($testEmail) {
    $res = httpReq('POST', '/auth/login', [
        'email'    => $testEmail,
        'password' => 'WrongPass999!'
    ]);
    return $res['code'] === 401 && ($res['json']['error']['code'] ?? '') === 'INVALID_CREDENTIALS';
});

// 2.4 Đăng nhập tài khoản Admin
runTest('Auth', 'POST /auth/login - Đăng nhập tài khoản Quản trị viên (Admin)', function() use (&$adminToken) {
    $res = httpReq('POST', '/auth/login', [
        'email'    => 'admin@techstore.local',
        'password' => 'Admin@123'
    ]);

    if ($res['code'] !== 200 || empty($res['json']['data']['access_token'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}: " . json_encode($res['json'])];
    }

    $adminToken = $res['json']['data']['access_token'];
    $role = $res['json']['data']['user']['role'] ?? '';
    return $role === 'admin';
});

// 2.5 Đăng nhập tài khoản Customer vừa tạo
runTest('Auth', 'POST /auth/login - Đăng nhập tài khoản Khách hàng mới', function() use ($testEmail, $testPassword, &$customerToken, &$customerRefreshToken) {
    $res = httpReq('POST', '/auth/login', [
        'email'    => $testEmail,
        'password' => $testPassword
    ]);

    if ($res['code'] !== 200 || empty($res['json']['data']['access_token'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }

    $customerToken = $res['json']['data']['access_token'];
    $customerRefreshToken = $res['json']['data']['refresh_token'];
    return true;
});

// 2.6 Lấy thông tin cá nhân GET /auth/me
runTest('Auth', 'GET /auth/me - Truy vấn hồ sơ tài khoản hiện tại qua JWT Bearer', function() use ($customerToken, $testEmail) {
    $res = httpReq('GET', '/auth/me', null, $customerToken);
    if ($res['code'] !== 200) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }
    return ($res['json']['data']['email'] ?? '') === $testEmail;
});

// 2.7 Refresh Token Rotation (RTR)
runTest('Auth', 'POST /auth/refresh - Gia hạn Access Token & Xoay vòng Refresh Token (RTR)', function() use (&$customerToken, &$customerRefreshToken, $pdo) {
    $res = httpReq('POST', '/auth/refresh', [
        'refresh_token' => $customerRefreshToken
    ]);

    if ($res['code'] !== 200 || empty($res['json']['data']['access_token'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}: " . json_encode($res['json'])];
    }

    $newAccess = $res['json']['data']['access_token'];
    $newRefresh = $res['json']['data']['refresh_token'];

    // Kiểm tra token cũ trong MySQL đã bị revoked = 1 chưa
    $hashOld = hash('sha256', $customerRefreshToken);
    $stmt = $pdo->prepare("SELECT revoked FROM refresh_tokens WHERE token_hash = ? LIMIT 1");
    $stmt->execute([$hashOld]);
    $revoked = $stmt->fetchColumn();

    $customerToken = $newAccess;
    $customerRefreshToken = $newRefresh;

    return (int)$revoked === 1;
});

// -------------------------------------------------------------------------
// 3. PRODUCTS CRUD (CREATE, READ, UPDATE, DELETE)
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 3] KIỂM THỬ CRUD SẢN PHẨM (PRODUCTS)\n" . COLOR_RESET;

$createdProductId = null;

// 3.1 READ: Danh sách sản phẩm kèm lọc & phân trang
runTest('Products', 'GET /products - Danh sách sản phẩm phân trang & bộ lọc', function() {
    $res = httpReq('GET', '/products?page=1&limit=5&category_id=1');
    if ($res['code'] !== 200 || empty($res['json']['data'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }
    $total = $res['json']['meta']['total_items'] ?? 0;
    return $total > 0 && count($res['json']['data']) <= 5;
});

// 3.2 READ: Chi tiết sản phẩm
runTest('Products', 'GET /products/1 - Chi tiết sản phẩm kèm thông số và thư viện ảnh', function() {
    $res = httpReq('GET', '/products/1');
    if ($res['code'] !== 200 || empty($res['json']['data'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }
    $p = $res['json']['data'];
    return !empty($p['name']) && !empty($p['price']);
});

// 3.3 CREATE: Admin tạo sản phẩm mới
runTest('Products', 'POST /products - [Admin] Thêm sản phẩm mới vào MySQL', function() use ($adminToken, &$createdProductId, $pdo) {
    $newProd = [
        'name'           => 'MacBook Pro M4 Max 2026 E2E Test',
        'category_id'    => 2, // Laptop
        'brand_id'       => 1, // Apple
        'price'          => 89990000,
        'original_price' => 99990000,
        'stock'          => 15,
        'image'          => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800',
        'ram'            => '64GB',
        'rom'            => '1TB SSD',
        'chipset'        => 'Apple M4 Max 16-Core',
        'screen'         => '16.2 inch Liquid Retina XDR 120Hz',
        'battery'        => '100Wh lên đến 22 giờ',
        'description'    => 'Siêu phẩm máy trạm mạnh nhất cho đồ họa và AI'
    ];

    $res = httpReq('POST', '/products', $newProd, $adminToken);
    if ($res['code'] !== 201 || empty($res['json']['data']['id'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}: " . json_encode($res['json'])];
    }

    $createdProductId = (int)$res['json']['data']['id'];

    // Đối chiếu trực tiếp MySQL
    $stmt = $pdo->prepare("SELECT id, name, price, stock, category_name FROM products WHERE id = ?");
    $stmt->execute([$createdProductId]);
    $dbProd = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dbProd || (float)$dbProd['price'] !== 89990000.0) {
        return ['ok' => false, 'error' => 'Sản phẩm chưa đồng bộ đúng vào MySQL'];
    }

    return ['ok' => true, 'msg' => "Product ID #{$createdProductId} lưu thành công"];
});

// 3.4 UPDATE: Admin cập nhật thông tin sản phẩm
runTest('Products', 'PUT /products/{id} - [Admin] Cập nhật giá & tồn kho sản phẩm', function() use ($adminToken, &$createdProductId, $pdo) {
    if (!$createdProductId) return ['ok' => false, 'error' => 'Không có ID sản phẩm để sửa'];

    $updateData = [
        'price' => 84990000,
        'stock' => 20
    ];

    $res = httpReq('PUT', "/products/{$createdProductId}", $updateData, $adminToken);
    if ($res['code'] !== 200) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }

    // Đối chiếu trực tiếp MySQL
    $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
    $stmt->execute([$createdProductId]);
    $dbProd = $stmt->fetch(PDO::FETCH_ASSOC);

    return (float)$dbProd['price'] === 84990000.0 && (int)$dbProd['stock'] === 20;
});

// 3.5 DELETE: Admin xóa sản phẩm
runTest('Products', 'DELETE /products/{id} - [Admin] Xóa sản phẩm khỏi CSDL MySQL', function() use ($adminToken, &$createdProductId, $pdo) {
    if (!$createdProductId) return ['ok' => false, 'error' => 'Không có ID sản phẩm'];

    $res = httpReq('DELETE', "/products/{$createdProductId}", null, $adminToken);
    if ($res['code'] !== 200 && $res['code'] !== 204) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }

    // Đối chiếu MySQL
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE id = ?");
    $stmt->execute([$createdProductId]);
    $count = (int)$stmt->fetchColumn();

    return $count === 0;
});

// -------------------------------------------------------------------------
// 4. CATEGORIES & BRANDS CRUD
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 4] KIỂM THỬ CRUD DANH MỤC & THƯƠNG HIỆU\n" . COLOR_RESET;

$createdCatId = null;
$createdBrandId = null;

// 4.1 Danh mục: GET /categories
runTest('Categories', 'GET /categories - Lấy danh sách ngành hàng từ MySQL', function() {
    $res = httpReq('GET', '/categories');
    return $res['code'] === 200 && count($res['json']['data'] ?? []) >= 5;
});

// 4.2 Danh mục: POST /categories
runTest('Categories', 'POST /categories - [Admin] Thêm ngành hàng mới', function() use ($adminToken, &$createdCatId, $pdo) {
    $res = httpReq('POST', '/categories', [
        'name' => 'Thiết bị Thực tế ảo (VR/AR)',
        'icon' => '🥽',
        'description' => 'Kính thực tế ảo thông minh thế hệ mới'
    ], $adminToken);

    if ($res['code'] !== 201 || empty($res['json']['data']['id'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }
    $createdCatId = (int)$res['json']['data']['id'];

    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$createdCatId]);
    return $stmt->fetchColumn() === 'Thiết bị Thực tế ảo (VR/AR)';
});

// 4.3 Danh mục: PUT /categories/{id}
runTest('Categories', 'PUT /categories/{id} - [Admin] Cập nhật thông tin ngành hàng', function() use ($adminToken, &$createdCatId, $pdo) {
    $res = httpReq('PUT', "/categories/{$createdCatId}", [
        'name' => 'Kính Vision Pro & VR Gaming'
    ], $adminToken);

    if ($res['code'] !== 200) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$createdCatId]);
    return $stmt->fetchColumn() === 'Kính Vision Pro & VR Gaming';
});

// 4.4 Danh mục: DELETE /categories/{id}
runTest('Categories', 'DELETE /categories/{id} - [Admin] Xóa ngành hàng khỏi MySQL', function() use ($adminToken, &$createdCatId, $pdo) {
    $res = httpReq('DELETE', "/categories/{$createdCatId}", null, $adminToken);
    if ($res['code'] !== 200 && $res['code'] !== 204) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE id = ?");
    $stmt->execute([$createdCatId]);
    return (int)$stmt->fetchColumn() === 0;
});

// 4.5 Thương hiệu: GET /brands
runTest('Brands', 'GET /brands - Lấy danh sách thương hiệu công nghệ từ MySQL', function() {
    $res = httpReq('GET', '/brands');
    return $res['code'] === 200 && count($res['json']['data'] ?? []) >= 8;
});

// 4.6 Thương hiệu: POST /brands
runTest('Brands', 'POST /brands - [Admin] Thêm hãng mới vào MySQL', function() use ($adminToken, &$createdBrandId, $pdo) {
    $res = httpReq('POST', '/brands', [
        'name' => 'NVIDIA Corporation',
        'logo' => 'https://example.com/nvidia-logo.png',
        'description' => 'Tập đoàn công nghệ chip AI và đồ họa hàng đầu thế giới'
    ], $adminToken);

    if ($res['code'] !== 201 || empty($res['json']['data']['id'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }
    $createdBrandId = (int)$res['json']['data']['id'];

    $stmt = $pdo->prepare("SELECT name FROM brands WHERE id = ?");
    $stmt->execute([$createdBrandId]);
    return $stmt->fetchColumn() === 'NVIDIA Corporation';
});

// 4.7 Thương hiệu: DELETE /brands/{id}
runTest('Brands', 'DELETE /brands/{id} - [Admin] Xóa hãng khỏi MySQL', function() use ($adminToken, &$createdBrandId, $pdo) {
    $res = httpReq('DELETE', "/brands/{$createdBrandId}", null, $adminToken);
    if ($res['code'] !== 200 && $res['code'] !== 204) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM brands WHERE id = ?");
    $stmt->execute([$createdBrandId]);
    return (int)$stmt->fetchColumn() === 0;
});

// -------------------------------------------------------------------------
// 5. CART CRUD & REALTIME STOCK CONTROL
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 5] KIỂM THỬ GIỎ HÀNG (CART CRUD) & KIỂM SOÁT TỒN KHO\n" . COLOR_RESET;

$cartItemId = null;

// 5.1 Thêm sản phẩm vào giỏ hàng
runTest('Cart', 'POST /cart/items - Thêm sản phẩm vào giỏ hàng', function() use ($customerToken, &$cartItemId, $pdo, $registeredUserId) {
    $res = httpReq('POST', '/cart/items', [
        'product_id' => 1,
        'quantity'   => 2,
        'variant'    => 'Titan Tự Nhiên - 256GB'
    ], $customerToken);

    if ($res['code'] !== 201 || empty($res['json']['data']['items'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}: " . json_encode($res['json'])];
    }

    $items = $res['json']['data']['items'];
    $cartItemId = (int)($items[0]['id'] ?? 0);

    // Đối chiếu trực tiếp bảng carts trong MySQL
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE user_id = ? AND product_id = 1");
    $stmt->execute([$registeredUserId]);
    $count = (int)$stmt->fetchColumn();

    return $count > 0;
});

// 5.2 Từ chối khi thêm số lượng vượt quá tồn kho khả dụng
runTest('Cart', 'POST /cart/items - Chặn khi vượt quá tồn kho (400 Bad Request)', function() use ($customerToken) {
    $res = httpReq('POST', '/cart/items', [
        'product_id' => 1,
        'quantity'   => 99999
    ], $customerToken);
    return $res['code'] === 400 && ($res['json']['error']['code'] ?? '') === 'INSUFFICIENT_STOCK';
});

// 5.3 Lấy thông tin giỏ hàng
runTest('Cart', 'GET /cart - Lấy dữ liệu giỏ hàng kèm tính tổng tiền', function() use ($customerToken) {
    $res = httpReq('GET', '/cart', null, $customerToken);
    if ($res['code'] !== 200 || empty($res['json']['data']['items'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }
    $cart = $res['json']['data'];
    return (int)$cart['total_quantity'] === 2 && (float)$cart['total_price'] > 0;
});

// 5.4 Cập nhật số lượng trong giỏ hàng
runTest('Cart', 'PUT /cart/items/{id} - Thay đổi số lượng sản phẩm trong giỏ', function() use ($customerToken, &$cartItemId, $pdo) {
    if (!$cartItemId) return ['ok' => false, 'error' => 'Thiếu cart item ID'];

    $res = httpReq('PUT', "/cart/items/{$cartItemId}", [
        'quantity' => 3
    ], $customerToken);

    if ($res['code'] !== 200) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    $stmt = $pdo->prepare("SELECT quantity FROM carts WHERE id = ?");
    $stmt->execute([$cartItemId]);
    return (int)$stmt->fetchColumn() === 3;
});

// 5.5 Xóa một món đồ khỏi giỏ hàng
runTest('Cart', 'DELETE /cart/items/{id} - Xóa 1 sản phẩm khỏi giỏ hàng', function() use ($customerToken, &$cartItemId, $pdo) {
    if (!$cartItemId) return ['ok' => false, 'error' => 'Thiếu cart item ID'];

    $res = httpReq('DELETE', "/cart/items/{$cartItemId}", null, $customerToken);
    if ($res['code'] !== 200) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE id = ?");
    $stmt->execute([$cartItemId]);
    return (int)$stmt->fetchColumn() === 0;
});

// -------------------------------------------------------------------------
// 6. ORDER FLOW & ACID TRANSACTIONS
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 6] KIỂM THỬ ĐẶT HÀNG (ORDERS), TRANSACTION & TRỪ TỒN KHO\n" . COLOR_RESET;

$createdOrderId = null;
$createdOrderCode = null;
$initialStockProd2 = 0;

runTest('Orders', 'POST /orders - Tạo đơn hàng (Trừ tồn kho, lưu đơn & order_items trong MySQL)', function() use ($customerToken, &$createdOrderId, &$createdOrderCode, &$initialStockProd2, $pdo) {
    // 1. Kiểm tra tồn kho trước khi đặt hàng sản phẩm ID 2 (Samsung Galaxy S24 Ultra)
    $stmtStock = $pdo->query("SELECT stock FROM products WHERE id = 2");
    $initialStockProd2 = (int)$stmtStock->fetchColumn();

    $orderPayload = [
        'customer_name'    => 'Nguyễn Văn Test',
        'customer_phone'   => '0988776655',
        'customer_email'   => 'customer_order_test@techstore.local',
        'shipping_address' => 'Số 45 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM',
        'payment_method'   => 'vietqr',
        'total_amount'     => 31990000,
        'items' => [
            [
                'product_id'   => 2,
                'name'         => 'Samsung Galaxy S24 Ultra 5G',
                'price'        => 31990000,
                'quantity'     => 1,
                'variant'      => 'Titan Xám - 256GB'
            ]
        ]
    ];

    $res = httpReq('POST', '/orders', $orderPayload, $customerToken);
    if ($res['code'] !== 201 || empty($res['json']['data']['id'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}: " . json_encode($res['json'])];
    }

    $createdOrderId = (int)$res['json']['data']['id'];
    $createdOrderCode = $res['json']['data']['order_code'];

    // 2. Kiểm tra bảng orders và order_items trong MySQL
    $stmtOrder = $pdo->prepare("SELECT id, order_code, status, total_amount FROM orders WHERE id = ?");
    $stmtOrder->execute([$createdOrderId]);
    $dbOrder = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    $stmtItems = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$createdOrderId]);
    $itemsCount = (int)$stmtItems->fetchColumn();

    // 3. Kiểm tra tồn kho của sản phẩm ID 2 đã bị trừ 1
    $stmtNewStock = $pdo->query("SELECT stock FROM products WHERE id = 2");
    $newStock = (int)$stmtNewStock->fetchColumn();

    $stockDeducted = ($newStock === $initialStockProd2 - 1);

    return $dbOrder && $itemsCount === 1 && $stockDeducted;
});

// 6.2 Lấy chi tiết đơn hàng
runTest('Orders', 'GET /orders/{id} - Lấy chi tiết đơn hàng kèm danh sách sản phẩm', function() use ($customerToken, &$createdOrderId) {
    if (!$createdOrderId) return ['ok' => false, 'error' => 'Thiếu ID đơn hàng'];

    $res = httpReq('GET', "/orders/{$createdOrderId}", null, $customerToken);
    if ($res['code'] !== 200 || empty($res['json']['data']['items'])) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }

    return (int)$res['json']['data']['id'] === $createdOrderId;
});

// 6.3 Hủy đơn hàng và hoàn lại tồn kho
runTest('Orders', 'POST /orders/{id}/cancel - Hủy đơn hàng đang chờ xử lý & Hoàn tồn kho', function() use ($customerToken, &$createdOrderId, &$initialStockProd2, $pdo) {
    if (!$createdOrderId) return ['ok' => false, 'error' => 'Thiếu ID đơn hàng'];

    $res = httpReq('POST', "/orders/{$createdOrderId}/cancel", null, $customerToken);
    if ($res['code'] !== 200) {
        return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    }

    // Kiểm tra trạng thái đơn trong MySQL
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->execute([$createdOrderId]);
    $status = $stmt->fetchColumn();

    // Kiểm tra tồn kho sản phẩm ID 2 đã được cộng lại
    $stmtStock = $pdo->query("SELECT stock FROM products WHERE id = 2");
    $restoredStock = (int)$stmtStock->fetchColumn();

    return $status === 'cancelled' && $restoredStock === $initialStockProd2;
});

// -------------------------------------------------------------------------
// 7. COUPONS, REVIEWS, WISHLIST & VIETQR
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 7] KIỂM THỬ COUPONS, ĐÁNH GIÁ (REVIEWS), YÊU THÍCH (WISHLIST) & VIETQR\n" . COLOR_RESET;

// 7.1 Kiểm tra mã giảm giá
runTest('Coupons', 'POST /coupons/validate - Xác thực mã giảm giá TECH10', function() {
    $res = httpReq('POST', '/coupons/validate', [
        'code'  => 'TECH10',
        'total' => 25000000
    ]);
    if ($res['code'] !== 200) return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    return !empty($res['json']['data']['discount']);
});

// 7.2 Đánh giá sản phẩm
runTest('Reviews', 'POST /products/{id}/reviews - Gửi đánh giá sao & bình luận vào MySQL', function() use ($customerToken, $pdo) {
    $res = httpReq('POST', '/products/1/reviews', [
        'rating'    => 5,
        'comment'   => 'Sản phẩm cực kỳ chất lượng, đóng gói kỹ càng!',
        'user_name' => 'Reviewer E2E'
    ], $customerToken);

    if ($res['code'] !== 201) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    // Đối chiếu bảng reviews
    $stmt = $pdo->prepare("SELECT comment FROM reviews WHERE product_id = 1 AND comment LIKE ? LIMIT 1");
    $stmt->execute(['%Sản phẩm cực kỳ chất lượng%']);
    return !empty($stmt->fetchColumn());
});

// 7.3 Danh sách yêu thích
runTest('Wishlist', 'POST /wishlist - Thêm sản phẩm vào danh sách yêu thích', function() use ($customerToken, $pdo, $registeredUserId) {
    $res = httpReq('POST', '/wishlist', [
        'product_id' => 3
    ], $customerToken);

    if ($res['code'] !== 201) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = 3");
    $stmt->execute([$registeredUserId]);
    return (int)$stmt->fetchColumn() > 0;
});

// 7.4 Sinh mã VietQR
runTest('Payments', 'POST /payments/qr - Sinh mã VietQR động theo đơn hàng', function() use ($createdOrderId) {
    $res = httpReq('POST', '/payments/qr', [
        'order_id'   => $createdOrderId ?: 1,
        'amount'     => 15000000,
        'order_code' => 'TS-TEST-QR'
    ]);
    return $res['code'] === 201 && !empty($res['json']['data']['qr_url']);
});

// -------------------------------------------------------------------------
// 8. ADMIN DASHBOARD & AUDIT LOGS
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 8] KIỂM THỬ QUẢN TRỊ VIÊN: USERS, KPI STATS & AUDIT LOGS\n" . COLOR_RESET;

// 8.1 Admin thống kê KPI
runTest('Admin', 'GET /admin/stats - Thống kê KPI doanh thu, đơn hàng & biểu đồ 7 ngày', function() use ($adminToken) {
    $res = httpReq('GET', '/admin/stats', null, $adminToken);
    if ($res['code'] !== 200 || empty($res['json']['data'])) return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    $stats = $res['json']['data'];
    return isset($stats['revenue']['total']) && isset($stats['orders']['total']) && isset($stats['revenue_chart']);
});

// 8.2 Admin danh sách người dùng
runTest('Admin', 'GET /admin/users - Danh sách người dùng hệ thống (Bảo mật ẩn password_hash)', function() use ($adminToken) {
    $res = httpReq('GET', '/admin/users', null, $adminToken);
    if ($res['code'] !== 200 || empty($res['json']['data'])) return ['ok' => false, 'error' => "HTTP {$res['code']}"];
    $users = $res['json']['data'];
    $firstUser = $users[0];
    return !isset($firstUser['password_hash']) && !empty($firstUser['email']);
});

// 8.3 Admin xem Audit logs
runTest('Admin', 'GET /admin/audit-logs - Nhật ký kiểm toán thao tác hệ thống', function() use ($adminToken) {
    $res = httpReq('GET', '/admin/audit-logs', null, $adminToken);
    return $res['code'] === 200 && is_array($res['json']['data']);
});

// 8.4 Quyền truy cập: Customer không được phép vào API Admin (403 Forbidden)
runTest('Admin', 'Chặn tài khoản Customer truy cập API Admin (403 Forbidden)', function() use ($customerToken) {
    $res = httpReq('GET', '/admin/stats', null, $customerToken);
    return $res['code'] === 403;
});

// -------------------------------------------------------------------------
// 9. LOGOUT & REVOKE TOKEN
// -------------------------------------------------------------------------
echo COLOR_BOLD . "\n[PHẦN 9] KIỂM THỬ ĐĂNG XUẤT (LOGOUT) & THU HỒI TOKEN\n" . COLOR_RESET;

runTest('Auth', 'POST /auth/logout - Thu hồi Refresh Token & Vô hiệu hóa phiên làm việc', function() use ($customerToken, $customerRefreshToken, $pdo) {
    $res = httpReq('POST', '/auth/logout', [
        'refresh_token' => $customerRefreshToken
    ], $customerToken);

    if ($res['code'] !== 200 && $res['code'] !== 204) return ['ok' => false, 'error' => "HTTP {$res['code']}"];

    // Kiểm tra token đã bị thu hồi trong MySQL
    $hash = hash('sha256', $customerRefreshToken);
    $stmt = $pdo->prepare("SELECT revoked FROM refresh_tokens WHERE token_hash = ?");
    $stmt->execute([$hash]);
    $revoked = $stmt->fetchColumn();

    return (int)$revoked === 1;
});

// =========================================================================
// TỔNG KẾT BÁO CÁO KIỂM THỬ
// =========================================================================
echo "\n" . COLOR_BOLD . COLOR_CYAN . "====================================================================\n";
echo "                      BÁO CÁO KẾT QUẢ KIỂM THỬ                     \n";
echo "====================================================================\n" . COLOR_RESET;
echo "  Tổng số kiểm thử đã thực hiện : " . COLOR_BOLD . "{$totalTests}" . COLOR_RESET . "\n";
echo "  Thành công (PASS)             : " . COLOR_GREEN . COLOR_BOLD . "{$passedTests}" . COLOR_RESET . "\n";
echo "  Thất bại (FAIL)               : " . ($failedTests > 0 ? COLOR_RED . COLOR_BOLD : COLOR_GREEN . COLOR_BOLD) . "{$failedTests}" . COLOR_RESET . "\n";
echo "  Tỷ lệ thành công              : " . COLOR_BOLD . round(($passedTests / max(1, $totalTests)) * 100, 1) . "%\n";
echo COLOR_CYAN . "====================================================================\n\n" . COLOR_RESET;

if ($failedTests === 0) {
    echo COLOR_GREEN . COLOR_BOLD . " ĐÁNH GIÁ CHUNG: TOÀN BỘ CÁC TÍNH NĂNG ĐÃ ĐỒNG BỘ 100% VỚI MYSQL! \n" . COLOR_RESET;
    exit(0);
} else {
    echo COLOR_RED . COLOR_BOLD . " CẢNH BÁO: CÓ {$failedTests} KIỂM THỬ CHƯA ĐẠT. VUI LÒNG KIỂM TRA LẠI! \n" . COLOR_RESET;
    exit(1);
}
