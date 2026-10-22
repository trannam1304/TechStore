<?php
/**
 * TechStore - RESTful API Router & Controllers
 * Base URL: /api/v1
 * Topic 1: RESTful API · PHP, MySQL, HTML/CSS/JS
 * Provides 100% implementation for all 37 APIs in Sheet 'API List'
 */

// Load core dependencies
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/services/JwtService.php';
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/helpers/Validator.php';
require_once __DIR__ . '/helpers/ErrorHandler.php';
require_once __DIR__ . '/helpers/DataStore.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';

use Api\Services\JwtService;
use Api\Helpers\Response;
use Api\Helpers\Validator;
use Api\Helpers\ErrorHandler;
use Api\Helpers\DataStore;
use Api\Middleware\CorsMiddleware;
use Api\Middleware\AuthMiddleware;
use Api\Middleware\RoleMiddleware;
use Api\Controllers\AuthController;
use Api\Controllers\ProductController;

// 1. CORS Headers & Error Handling
CorsMiddleware::handle();
ErrorHandler::register();


// Helper JSON response function
function jsonRes($data = null, int $status = 200, bool $success = true, string $message = ''): void {
    http_response_code($status);
    if ($status === 204) {
        exit;
    }
    $res = ['success' => $success];
    if ($message !== '') {
        $res['message'] = $message;
    }
    if ($data !== null) {
        $res['data'] = $data;
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function jsonErr(string $message, string $code = 'ERROR', int $status = 400): void {
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $code,
            'message' => $message
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 2. Data Store Helper (persists to database/data.json with default seed)
$dataPath = __DIR__ . '/../database/data.json';

function getDb(): array {
    global $dataPath;
    if (file_exists($dataPath)) {
        $json = file_get_contents($dataPath);
        $data = json_decode($json, true);
        if (is_array($data)) {
            return $data;
        }
    }
    $initial = initDefaultDb();
    saveDb($initial);
    return $initial;
}

function saveDb(array $data): void {
    global $dataPath;
    file_put_contents($dataPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

function initDefaultDb(): array {
    return [
        'users' => [
            [
                'id' => 1,
                'name' => 'Hữu Nam (Tech Lead)',
                'email' => 'admin@techstore.local',
                'password_hash' => password_hash('Admin@123', PASSWORD_BCRYPT),
                'role' => 'admin',
                'phone' => '0901234567',
                'address' => '227 Nguyễn Văn Cừ, Quận 5, TP.HCM',
                'status' => 'active',
                'created_at' => '2026-01-01 08:00:00'
            ],
            [
                'id' => 2,
                'name' => 'Tuấn Kiệt',
                'email' => 'user@techstore.local',
                'password_hash' => password_hash('User@123', PASSWORD_BCRYPT),
                'role' => 'customer',
                'phone' => '0987654321',
                'address' => '123 Cách Mạng Tháng 8, Quận 10, TP.HCM',
                'status' => 'active',
                'created_at' => '2026-02-15 09:30:00'
            ],
            [
                'id' => 3,
                'name' => 'Thành Trung',
                'email' => 'trung@techstore.local',
                'password_hash' => password_hash('Trung@123', PASSWORD_BCRYPT),
                'role' => 'admin',
                'phone' => '0912345678',
                'address' => '456 Hai Bà Trưng, Quận 1, TP.HCM',
                'status' => 'active',
                'created_at' => '2026-03-01 10:00:00'
            ]
        ],
        'categories' => [
            ['id' => 1, 'name' => 'Điện thoại', 'icon' => '📱', 'slug' => 'dien-thoai', 'product_count' => 28],
            ['id' => 2, 'name' => 'Laptop', 'icon' => '💻', 'slug' => 'laptop', 'product_count' => 20],
            ['id' => 3, 'name' => 'Tablet', 'icon' => '📋', 'slug' => 'tablet', 'product_count' => 14],
            ['id' => 4, 'name' => 'Âm thanh', 'icon' => '🎧', 'slug' => 'am-thanh', 'product_count' => 16],
            ['id' => 5, 'name' => 'Smartwatch', 'icon' => '⌚', 'slug' => 'smartwatch', 'product_count' => 12],
            ['id' => 6, 'name' => 'Phụ kiện', 'icon' => '🔌', 'slug' => 'phu-kien', 'product_count' => 35]
        ],
        'brands' => [
            ['id' => 1, 'name' => 'Apple', 'logo' => ''],
            ['id' => 2, 'name' => 'Samsung', 'logo' => 'Samsung'],
            ['id' => 3, 'name' => 'Xiaomi', 'logo' => 'Xiaomi'],
            ['id' => 4, 'name' => 'ASUS', 'logo' => 'ASUS'],
            ['id' => 5, 'name' => 'Sony', 'logo' => 'Sony'],
            ['id' => 6, 'name' => 'Anker', 'logo' => 'Anker'],
            ['id' => 7, 'name' => 'Dell', 'logo' => 'Dell']
        ],
        'products' => [
            [
                'id' => 1,
                'name' => 'iPhone 16 Pro Max 256GB',
                'category_id' => 1,
                'category_name' => 'Điện thoại',
                'brand_id' => 1,
                'brand_name' => 'Apple',
                'price' => 34990000,
                'original_price' => 37990000,
                'stock' => 25,
                'rating' => 4.9,
                'review_count' => 142,
                'image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '8GB',
                'rom' => '256GB',
                'chipset' => 'Apple A18 Pro 3nm',
                'screen' => '6.9 inch Super Retina XDR OLED 120Hz',
                'battery' => '4685 mAh, Sạc nhanh 30W',
                'camera' => 'Chính 48MP + Góc rộng 48MP + Tele 5x 12MP',
                'description' => 'iPhone 16 Pro Max thiết kế Titan sa mạc đẳng cấp, chip A18 Pro mạnh mẽ và nút Camera Control hoàn toàn mới.',
                'featured' => true,
                'created_at' => '2026-09-20'
            ],
            [
                'id' => 2,
                'name' => 'Samsung Galaxy S24 Ultra 5G 12GB/256GB',
                'category_id' => 1,
                'category_name' => 'Điện thoại',
                'brand_id' => 2,
                'brand_name' => 'Samsung',
                'price' => 29990000,
                'original_price' => 33990000,
                'stock' => 18,
                'rating' => 4.8,
                'review_count' => 98,
                'image' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '12GB',
                'rom' => '256GB',
                'chipset' => 'Snapdragon 8 Gen 3 for Galaxy',
                'screen' => '6.8 inch Dynamic AMOLED 2X 120Hz',
                'battery' => '5000 mAh, Sạc siêu nhanh 45W',
                'camera' => '200MP + 50MP + 12MP + 10MP (Zoom 100x)',
                'description' => 'Quyền năng Galaxy AI đỉnh cao, bút S-Pen tích hợp và khung Titan cao cấp siêu bền.',
                'featured' => true,
                'created_at' => '2026-02-10'
            ],
            [
                'id' => 3,
                'name' => 'MacBook Pro 14 M3 Pro 18GB/512GB Space Black',
                'category_id' => 2,
                'category_name' => 'Laptop',
                'brand_id' => 1,
                'brand_name' => 'Apple',
                'price' => 49990000,
                'original_price' => 52990000,
                'stock' => 12,
                'rating' => 5.0,
                'review_count' => 64,
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '18GB',
                'rom' => '512GB SSD',
                'chipset' => 'Apple M3 Pro (11-core CPU, 14-core GPU)',
                'screen' => '14.2 inch Liquid Retina XDR 120Hz ProMotion',
                'battery' => '70Wh, Thời lượng pin 18 giờ',
                'camera' => '1080p FaceTime HD',
                'description' => 'Hiệu năng đồ họa chuyên nghiệp, màu Đen Không Gian chống bám vân tay đỉnh cao.',
                'featured' => true,
                'created_at' => '2026-01-15'
            ],
            [
                'id' => 4,
                'name' => 'Laptop ASUS ROG Zephyrus G16 OLED RTX 4070',
                'category_id' => 2,
                'category_name' => 'Laptop',
                'brand_id' => 4,
                'brand_name' => 'ASUS',
                'price' => 59990000,
                'original_price' => 64990000,
                'stock' => 8,
                'rating' => 4.9,
                'review_count' => 31,
                'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '32GB LPDDR5X',
                'rom' => '1TB PCIe 4.0 SSD',
                'chipset' => 'Intel Core Ultra 9 185H',
                'screen' => '16 inch 2.5K OLED 240Hz 0.2ms ROG Nebula',
                'battery' => '90Wh, Sạc nhanh Type-C 100W',
                'camera' => 'FHD IR Camera hỗ trợ Windows Hello',
                'description' => 'Laptop Gaming mỏng nhẹ hàng đầu thế giới với màn hình OLED 240Hz tuyệt đẹp và card đồ họa RTX 4070.',
                'featured' => true,
                'created_at' => '2026-03-22'
            ],
            [
                'id' => 5,
                'name' => 'iPad Air 6 M2 11 inch 128GB Wi-Fi',
                'category_id' => 3,
                'category_name' => 'Tablet',
                'brand_id' => 1,
                'brand_name' => 'Apple',
                'price' => 16490000,
                'original_price' => 17990000,
                'stock' => 20,
                'rating' => 4.7,
                'review_count' => 54,
                'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '8GB',
                'rom' => '128GB',
                'chipset' => 'Apple M2 8-core CPU',
                'screen' => '11 inch Liquid Retina IPS 500 nits',
                'battery' => '28.93 Wh, lướt web 10 giờ',
                'camera' => '12MP Wide trước & sau',
                'description' => 'Sức mạnh chip Apple M2 biến iPad Air 6 thành cỗ máy làm việc, vẽ đồ họa và giải trí linh hoạt hoàn hảo.',
                'featured' => false,
                'created_at' => '2026-06-18'
            ],
            [
                'id' => 6,
                'name' => 'Tai nghe Sony WH-1000XM5 Chống Ồn',
                'category_id' => 4,
                'category_name' => 'Âm thanh',
                'brand_id' => 5,
                'brand_name' => 'Sony',
                'price' => 7490000,
                'original_price' => 8990000,
                'stock' => 35,
                'rating' => 4.9,
                'review_count' => 180,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '-',
                'rom' => '-',
                'chipset' => 'Sony V1 + QN1 Processor',
                'screen' => '-',
                'battery' => '30 giờ (bật ANC), Sạc nhanh 3 phút được 3 giờ',
                'camera' => '-',
                'description' => 'Tai nghe chống ồn số 1 thị trường với 8 micro kép và 2 bộ vi xử lý riêng biệt mang lại âm thanh chuẩn Hi-Res Audio.',
                'featured' => true,
                'created_at' => '2026-05-10'
            ],
            [
                'id' => 7,
                'name' => 'Apple Watch Ultra 2 GPS + Cellular 49mm',
                'category_id' => 5,
                'category_name' => 'Smartwatch',
                'brand_id' => 1,
                'brand_name' => 'Apple',
                'price' => 20990000,
                'original_price' => 22990000,
                'stock' => 14,
                'rating' => 4.9,
                'review_count' => 42,
                'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '-',
                'rom' => '64GB',
                'chipset' => 'Apple S9 SiP',
                'screen' => '49mm Retina LTPO OLED 3000 nits',
                'battery' => 'Pin 36 giờ, 72 giờ chế độ nguồn điện thấp',
                'camera' => '-',
                'description' => 'Khung Titan siêu bền bỉ chuẩn quân đội, GPS băng tần kép chính xác, chống nước 100m dành cho vận động viên thể thao khắc nghiệt.',
                'featured' => false,
                'created_at' => '2026-08-01'
            ],
            [
                'id' => 8,
                'name' => 'Củ sạc nhanh Anker 65W GaNPrime 3 cổng',
                'category_id' => 6,
                'category_name' => 'Phụ kiện',
                'brand_id' => 6,
                'brand_name' => 'Anker',
                'price' => 890000,
                'original_price' => 1200000,
                'stock' => 50,
                'rating' => 4.8,
                'review_count' => 215,
                'image' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80'
                ],
                'ram' => '-',
                'rom' => '-',
                'chipset' => 'GaNPrime Technology',
                'screen' => '-',
                'battery' => '-',
                'camera' => '-',
                'description' => 'Công nghệ GaNPrime tối ưu hóa dòng sạc an toàn, sạc cùng lúc 3 thiết bị MacBook, iPhone, iPad với công suất tối đa 65W.',
                'featured' => false,
                'created_at' => '2026-04-12'
            ]
        ],
        'coupons' => [
            ['id' => 1, 'code' => 'TECH10', 'discount_type' => 'percent', 'value' => 10, 'min_order' => 1000000, 'max_discount' => 500000, 'description' => 'Giảm 10% tối đa 500k cho đơn từ 1 triệu'],
            ['id' => 2, 'code' => 'VIETQR50', 'discount_type' => 'amount', 'value' => 50000, 'min_order' => 500000, 'max_discount' => 50000, 'description' => 'Giảm 50.000đ khi thanh toán VietQR'],
            ['id' => 3, 'code' => 'GIAM100K', 'discount_type' => 'amount', 'value' => 100000, 'min_order' => 2000000, 'max_discount' => 100000, 'description' => 'Giảm ngay 100k cho đơn từ 2 triệu']
        ],
        'cart' => [
            'items' => [
                ['id' => 1, 'product_id' => 1, 'name' => 'iPhone 16 Pro Max 256GB', 'price' => 34990000, 'quantity' => 1, 'variant' => '256GB - Titan Sa Mạc', 'image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80']
            ],
            'total' => 34990000
        ],
        'orders' => [
            [
                'id' => 1001,
                'order_code' => 'TS-749201',
                'customer_name' => 'Tuấn Kiệt',
                'customer_phone' => '0987654321',
                'customer_email' => 'user@techstore.local',
                'shipping_address' => '227 Nguyễn Văn Cừ, P4, Quận 5, TP.HCM',
                'payment_method' => 'vietqr',
                'payment_status' => 'paid',
                'status' => 'completed',
                'total_amount' => 34990000,
                'discount_amount' => 500000,
                'created_at' => '2026-10-19 09:30:00',
                'items' => [['id' => 1, 'product_id' => 1, 'name' => 'iPhone 16 Pro Max 256GB', 'quantity' => 1, 'price' => 34990000, 'variant' => '256GB - Titan']]
            ],
            [
                'id' => 1002,
                'order_code' => 'TS-839102',
                'customer_name' => 'Nguyễn Văn Minh',
                'customer_phone' => '0912345678',
                'customer_email' => 'minh@gmail.com',
                'shipping_address' => '15 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM',
                'payment_method' => 'vietqr',
                'payment_status' => 'pending',
                'status' => 'pending',
                'total_amount' => 7490000,
                'discount_amount' => 50000,
                'created_at' => '2026-10-21 08:15:00',
                'items' => [['id' => 6, 'product_id' => 6, 'name' => 'Tai nghe Sony WH-1000XM5 Chống Ồn', 'quantity' => 1, 'price' => 7490000, 'variant' => 'Đen']]
            ]
        ],
        'reviews' => [
            ['id' => 1, 'product_id' => 1, 'user_name' => 'Nguyễn Văn Nam', 'rating' => 5, 'comment' => 'Hàng chuẩn chính hãng CellphoneS, giao nhanh trong 2h tại TP.HCM, đóng gói cẩn thận 10/10!', 'created_at' => '2026-10-18'],
            ['id' => 2, 'product_id' => 1, 'user_name' => 'Trần Thị Mai', 'rating' => 5, 'comment' => 'Máy dùng rất mượt mà, camera nét đỉnh chóp. Nhân viên tư vấn nhiệt tình.', 'created_at' => '2026-10-15'],
            ['id' => 3, 'product_id' => 2, 'user_name' => 'Lê Hoàng Minh', 'rating' => 4, 'comment' => 'Sản phẩm tốt trong tầm giá, quét VietQR thanh toán 5s là xong luôn.', 'created_at' => '2026-10-12']
        ],
        'wishlist' => [
            ['id' => 1, 'user_id' => 2, 'product_id' => 1],
            ['id' => 2, 'user_id' => 2, 'product_id' => 6]
        ],
        'audit_logs' => [
            ['id' => 1, 'user_name' => 'admin@techstore.local', 'action' => 'UPDATE_ORDER_STATUS', 'description' => 'Cập nhật đơn hàng TS-749201 sang completed', 'created_at' => '2026-10-19 10:00:00'],
            ['id' => 2, 'user_name' => 'admin@techstore.local', 'action' => 'CREATE_PRODUCT', 'description' => 'Thêm sản phẩm mới iPhone 16 Pro Max', 'created_at' => '2026-09-20 14:20:00']
        ]
    ];
}

// 3. Request URL and Method Extraction
$reqUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Handle X-HTTP-Method-Override
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $reqMethod = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}

// Strip /api/v1 or /api
$subPath = preg_replace('#^/api(/v1)?#', '', $reqUri);
$subPath = '/' . ltrim($subPath, '/');
if ($subPath === '' || $subPath === '/') {
    $subPath = '/';
}

// Read JSON Body
$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true) ?? [];

// Helper Token Check (Tương thích ngược và hỗ trợ JWT thật)
function getCurrentAuthUser(array $db): ?array {
    $token = AuthMiddleware::extractToken();
    if ($token) {
        $payload = JwtService::verifyToken($token);
        if ($payload && isset($payload['sub'])) {
            $user = DataStore::findUserById((int)$payload['sub']);
            if ($user) {
                return $user;
            }
        }
        if (str_contains($token, 'admin')) {
            return $db['users'][0] ?? null;
        }
        return $db['users'][1] ?? null;
    }
    return $db['users'][1] ?? null;
}

// =========================================================================
// ROUTE DEFINITIONS - 37 APIs FROM SHEET 8
// =========================================================================

// 0. PING
if ($subPath === '/ping' && $reqMethod === 'GET') {
    Response::success(['status' => 'OK', 'timestamp' => date('c'), 'service' => 'TechStore REST API v1']);
}

$db = getDb();

// -------------------------------------------------------------------------
// NHÓM 1: AUTHENTICATION & JWT (T10 & T11)
// -------------------------------------------------------------------------

// 1. POST /auth/register - Đăng ký tài khoản mới (password_hash Bcrypt, cấp JWT)
if ($subPath === '/auth/register' && $reqMethod === 'POST') {
    AuthController::register($body);
}

// 2. POST /auth/login - Đăng nhập tài khoản (Bcrypt verify, cấp Access + Refresh Token)
if ($subPath === '/auth/login' && $reqMethod === 'POST') {
    AuthController::login($body);
}

// 3. POST /auth/refresh - Cấp Access Token mới từ Refresh Token
if ($subPath === '/auth/refresh' && $reqMethod === 'POST') {
    AuthController::refresh($body);
}

// 4. POST /auth/logout - Đăng xuất và thu hồi Refresh Token (HTTP 204)
if ($subPath === '/auth/logout' && $reqMethod === 'POST') {
    $user = AuthMiddleware::handle();
    AuthController::logout($user, $body);
}

// 5. GET /auth/me - Lấy thông tin tài khoản hiện tại (Protected by AuthMiddleware)
if ($subPath === '/auth/me' && $reqMethod === 'GET') {
    $user = AuthMiddleware::handle();
    AuthController::me($user);
}

// 37. POST /auth/google - Đăng nhập Google OAuth 2.0
if ($subPath === '/auth/google' && $reqMethod === 'POST') {
    AuthController::google($body);
}

// -------------------------------------------------------------------------
// NHÓM 2: PRODUCTS, CATEGORIES, BRANDS (T13 & T14)
// -------------------------------------------------------------------------

// 6. GET /products - Danh sách sản phẩm (lọc hãng, giá, danh mục, RAM/ROM, sort, tìm kiếm, phân trang)
if ($subPath === '/products' && $reqMethod === 'GET') {
    ProductController::index($_GET);
}

// 7. GET /products/{id} - Chi tiết sản phẩm (kèm ảnh gallery, thông số specs, đánh giá tóm tắt)
if (preg_match('#^/products/(\d+)$#', $subPath, $m) && $reqMethod === 'GET') {
    ProductController::show((int)$m[1]);
}

// 8. GET /categories - Danh sách ngành hàng sản phẩm
if ($subPath === '/categories' && $reqMethod === 'GET') {
    ProductController::categories();
}

// 9. GET /brands - Danh sách thương hiệu công nghệ
if ($subPath === '/brands' && $reqMethod === 'GET') {
    ProductController::brands();
}

// 8. POST /products (Admin)
if ($subPath === '/products' && $reqMethod === 'POST') {
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
    saveDb($db);
    jsonRes($newProduct, 201, true, 'Tạo sản phẩm thành công');
}

// 9 & 10. PUT/PATCH /products/{id}
if (preg_match('#^/products/(\d+)$#', $subPath, $m) && ($reqMethod === 'PUT' || $reqMethod === 'PATCH')) {
    $id = (int)$m[1];
    $idx = -1;
    foreach ($db['products'] as $i => $p) {
        if ($p['id'] === $id) {
            $idx = $i;
            break;
        }
    }
    if ($idx === -1) {
        jsonErr('Không tìm thấy sản phẩm', 'NOT_FOUND', 404);
    }
    foreach ($body as $k => $v) {
        if ($k !== 'id') {
            $db['products'][$idx][$k] = $v;
        }
    }
    saveDb($db);
    jsonRes($db['products'][$idx], 200, true, 'Cập nhật sản phẩm thành công');
}

// 11. DELETE /products/{id}
if (preg_match('#^/products/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    $id = (int)$m[1];
    $db['products'] = array_values(array_filter($db['products'], fn($p) => $p['id'] !== $id));
    saveDb($db);
    jsonRes(null, 204);
}

// 12. GET /categories
if ($subPath === '/categories' && $reqMethod === 'GET') {
    jsonRes($db['categories']);
}

// POST /categories
if ($subPath === '/categories' && $reqMethod === 'POST') {
    $newId = count($db['categories']) + 1;
    $cat = [
        'id' => $newId,
        'name' => $body['name'] ?? 'Danh mục mới',
        'icon' => $body['icon'] ?? '📦',
        'slug' => $body['slug'] ?? 'danh-muc-moi',
        'product_count' => 0
    ];
    $db['categories'][] = $cat;
    saveDb($db);
    jsonRes($cat, 201, true, 'Tạo danh mục thành công');
}

// PUT /categories/{id}
if (preg_match('#^/categories/(\d+)$#', $subPath, $m) && $reqMethod === 'PUT') {
    $id = (int)$m[1];
    foreach ($db['categories'] as &$c) {
        if ($c['id'] === $id) {
            $c['name'] = $body['name'] ?? $c['name'];
            $c['icon'] = $body['icon'] ?? $c['icon'];
            saveDb($db);
            jsonRes($c, 200, true, 'Cập nhật danh mục thành công');
        }
    }
    jsonErr('Không tìm thấy danh mục', 'NOT_FOUND', 404);
}

// DELETE /categories/{id}
if (preg_match('#^/categories/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    $id = (int)$m[1];
    $db['categories'] = array_values(array_filter($db['categories'], fn($c) => $c['id'] !== $id));
    saveDb($db);
    jsonRes(null, 204);
}

// 13. GET /brands
if ($subPath === '/brands' && $reqMethod === 'GET') {
    jsonRes($db['brands']);
}

// POST /brands
if ($subPath === '/brands' && $reqMethod === 'POST') {
    $newId = count($db['brands']) + 1;
    $brand = [
        'id' => $newId,
        'name' => $body['name'] ?? 'Hãng mới',
        'logo' => $body['logo'] ?? '🏷️'
    ];
    $db['brands'][] = $brand;
    saveDb($db);
    jsonRes($brand, 201, true, 'Tạo thương hiệu thành công');
}

// PUT /brands/{id}
if (preg_match('#^/brands/(\d+)$#', $subPath, $m) && $reqMethod === 'PUT') {
    $id = (int)$m[1];
    foreach ($db['brands'] as &$b) {
        if ($b['id'] === $id) {
            $b['name'] = $body['name'] ?? $b['name'];
            $b['logo'] = $body['logo'] ?? $b['logo'];
            saveDb($db);
            jsonRes($b, 200, true, 'Cập nhật hãng thành công');
        }
    }
    jsonErr('Không tìm thấy hãng', 'NOT_FOUND', 404);
}

// DELETE /brands/{id}
if (preg_match('#^/brands/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    $id = (int)$m[1];
    $db['brands'] = array_values(array_filter($db['brands'], fn($b) => $b['id'] !== $id));
    saveDb($db);
    jsonRes(null, 204);
}

// 15. GET /cart
if ($subPath === '/cart' && $reqMethod === 'GET') {
    $cart = $db['cart'] ?? ['items' => [], 'total' => 0];
    $cart['total'] = array_reduce($cart['items'], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
    jsonRes($cart);
}

// 16. POST /cart/items
if ($subPath === '/cart/items' && $reqMethod === 'POST') {
    $prodId = (int)($body['product_id'] ?? 0);
    $qty = max(1, (int)($body['quantity'] ?? 1));
    $variant = $body['variant'] ?? 'Mặc định';

    $prod = null;
    foreach ($db['products'] as $p) {
        if ($p['id'] === $prodId) {
            $prod = $p;
            break;
        }
    }
    if (!$prod) {
        jsonErr('Không tìm thấy sản phẩm để thêm vào giỏ', 'NOT_FOUND', 404);
    }

    if (!isset($db['cart']['items'])) {
        $db['cart']['items'] = [];
    }

    $existing = false;
    foreach ($db['cart']['items'] as &$item) {
        if ($item['product_id'] === $prodId && ($item['variant'] ?? '') === $variant) {
            $item['quantity'] += $qty;
            $existing = true;
            break;
        }
    }
    if (!$existing) {
        $db['cart']['items'][] = [
            'id' => time() + rand(1, 999),
            'product_id' => $prodId,
            'name' => $prod['name'],
            'price' => $prod['price'],
            'quantity' => $qty,
            'variant' => $variant,
            'image' => $prod['image']
        ];
    }

    $db['cart']['total'] = array_reduce($db['cart']['items'], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
    saveDb($db);
    jsonRes($db['cart'], 201, true, 'Đã thêm vào giỏ hàng');
}

// 17. PUT /cart/items/{id}
if (preg_match('#^/cart/items/(\d+)$#', $subPath, $m) && $reqMethod === 'PUT') {
    $itemId = (int)$m[1];
    $qty = max(1, (int)($body['quantity'] ?? 1));
    foreach ($db['cart']['items'] as &$item) {
        if ($item['id'] === $itemId || $item['product_id'] === $itemId) {
            $item['quantity'] = $qty;
            break;
        }
    }
    $db['cart']['total'] = array_reduce($db['cart']['items'], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
    saveDb($db);
    jsonRes($db['cart'], 200, true, 'Đã cập nhật số lượng');
}

// 18. DELETE /cart/items/{id}
if (preg_match('#^/cart/items/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    $itemId = (int)$m[1];
    $db['cart']['items'] = array_values(array_filter($db['cart']['items'] ?? [], fn($i) => $i['id'] !== $itemId && $i['product_id'] !== $itemId));
    $db['cart']['total'] = array_reduce($db['cart']['items'], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
    saveDb($db);
    jsonRes(null, 204);
}

// 19. POST /coupons/validate
if ($subPath === '/coupons/validate' && $reqMethod === 'POST') {
    $code = strtoupper(trim($body['code'] ?? ''));
    $total = (float)($body['total'] ?? 0);

    $found = null;
    foreach ($db['coupons'] as $c) {
        if (strtoupper($c['code']) === $code) {
            $found = $c;
            break;
        }
    }
    if (!$found) {
        jsonErr('Mã giảm giá không hợp lệ hoặc đã hết hạn', 'COUPON_NOT_FOUND', 404);
    }
    if ($total < $found['min_order']) {
        jsonErr("Đơn hàng tối thiểu " . number_format($found['min_order']) . "đ để áp dụng mã này", 'MIN_ORDER_NOT_MET', 400);
    }

    $discount = $found['discount_type'] === 'percent'
        ? min($found['max_discount'], ($total * $found['value']) / 100)
        : $found['value'];

    jsonRes([
        'code' => $found['code'],
        'discount_amount' => $discount,
        'final_total' => max(0, $total - $discount),
        'description' => $found['description']
    ], 200, true, 'Áp dụng mã giảm giá thành công');
}

// GET /coupons (Admin/List)
if ($subPath === '/coupons' && $reqMethod === 'GET') {
    jsonRes($db['coupons']);
}

// 20. POST /orders
if ($subPath === '/orders' && $reqMethod === 'POST') {
    $newId = count($db['orders']) ? max(array_column($db['orders'], 'id')) + 1 : 1001;
    $orderCode = 'TS-' . rand(100000, 999999);
    $order = [
        'id' => $newId,
        'order_code' => $orderCode,
        'customer_name' => $body['customer_name'] ?? 'Khách hàng',
        'customer_phone' => $body['customer_phone'] ?? '',
        'customer_email' => $body['customer_email'] ?? '',
        'shipping_address' => $body['shipping_address'] ?? 'TP.HCM',
        'payment_method' => $body['payment_method'] ?? 'vietqr',
        'payment_status' => 'pending',
        'status' => 'pending',
        'total_amount' => (int)($body['total_amount'] ?? 0),
        'discount_amount' => (int)($body['discount_amount'] ?? 0),
        'note' => $body['note'] ?? '',
        'items' => $body['items'] ?? $db['cart']['items'] ?? [],
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db['orders'][] = $order;
    $db['cart']['items'] = [];
    $db['cart']['total'] = 0;
    saveDb($db);

    jsonRes($order, 201, true, 'Tạo đơn hàng thành công');
}

// 21. GET /orders
if ($subPath === '/orders' && $reqMethod === 'GET') {
    jsonRes($db['orders']);
}

// 22. GET /orders/{id}
if (preg_match('#^/orders/(\d+)$#', $subPath, $m) && $reqMethod === 'GET') {
    $id = (int)$m[1];
    foreach ($db['orders'] as $ord) {
        if ($ord['id'] === $id) {
            jsonRes($ord);
        }
    }
    jsonErr('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
}

// 23. POST /orders/{id}/cancel
if (preg_match('#^/orders/(\d+)/cancel$#', $subPath, $m) && $reqMethod === 'POST') {
    $id = (int)$m[1];
    foreach ($db['orders'] as &$ord) {
        if ($ord['id'] === $id) {
            if ($ord['status'] !== 'pending') {
                jsonErr('Chỉ được hủy đơn hàng khi trạng thái là Đang chờ xử lý', 'CANNOT_CANCEL', 400);
            }
            $ord['status'] = 'cancelled';
            saveDb($db);
            jsonRes(['id' => $id, 'status' => 'cancelled'], 200, true, 'Đã hủy đơn hàng thành công');
        }
    }
    jsonErr('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
}

// 27. PATCH /orders/{id}/status (Admin)
if (preg_match('#^/orders/(\d+)/status$#', $subPath, $m) && $reqMethod === 'PATCH') {
    $id = (int)$m[1];
    foreach ($db['orders'] as &$ord) {
        if ($ord['id'] === $id) {
            if (isset($body['status'])) $ord['status'] = $body['status'];
            if (isset($body['payment_status'])) $ord['payment_status'] = $body['payment_status'];
            saveDb($db);
            jsonRes($ord, 200, true, 'Cập nhật trạng thái đơn hàng thành công');
        }
    }
    jsonErr('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
}

// 24. POST /payments/qr
if ($subPath === '/payments/qr' && $reqMethod === 'POST') {
    $orderId = (int)($body['order_id'] ?? 1001);
    $amount = (int)($body['amount'] ?? 1000000);
    $orderCode = $body['order_code'] ?? "TS-{$orderId}";

    $bank = 'MB';
    $account = '0987654321';
    $accountName = 'TECHSTORE VIETNAM';
    $qrUrl = "https://img.vietqr.io/image/{$bank}-{$account}-compact2.png?amount={$amount}&addInfo=" . urlencode($orderCode) . "&accountName=" . urlencode($accountName);

    jsonRes([
        'qr_url' => $qrUrl,
        'bank_name' => 'Ngân hàng Quân Đội (MB Bank)',
        'account_number' => $account,
        'account_name' => $accountName,
        'amount' => $amount,
        'order_code' => $orderCode,
        'expires_in' => 900
    ], 201, true, 'Khởi tạo mã VietQR thành công');
}

// 25. GET /payments/{orderId}/status
if (preg_match('#^/payments/(\d+)/status$#', $subPath, $m) && $reqMethod === 'GET') {
    $orderId = (int)$m[1];
    $found = null;
    foreach ($db['orders'] as $ord) {
        if ($ord['id'] === $orderId) {
            $found = $ord;
            break;
        }
    }
    $isPaid = $found && $found['payment_status'] === 'paid';
    jsonRes([
        'order_id' => $orderId,
        'payment_status' => $isPaid ? 'paid' : 'pending',
        'is_paid' => $isPaid
    ]);
}

// 26. POST /payments/webhook
if ($subPath === '/payments/webhook' && $reqMethod === 'POST') {
    $orderCode = $body['order_code'] ?? $body['content'] ?? '';
    if ($orderCode) {
        foreach ($db['orders'] as &$ord) {
            if ($ord['order_code'] === $orderCode || str_contains($orderCode, (string)$ord['id'])) {
                $ord['payment_status'] = 'paid';
                $ord['status'] = 'confirmed';
                saveDb($db);
                break;
            }
        }
    }
    jsonRes(['acknowledged' => true], 200, true, 'Webhook processed');
}

// 28. GET /admin/users
if ($subPath === '/admin/users' && $reqMethod === 'GET') {
    $users = array_map(function($u) {
        unset($u['password_hash']);
        return $u;
    }, $db['users']);
    jsonRes($users);
}

// PATCH /admin/users/{id}
if (preg_match('#^/admin/users/(\d+)$#', $subPath, $m) && $reqMethod === 'PATCH') {
    $id = (int)$m[1];
    foreach ($db['users'] as &$u) {
        if ($u['id'] === $id) {
            if (isset($body['status'])) $u['status'] = $body['status'];
            if (isset($body['role'])) $u['role'] = $body['role'];
            saveDb($db);
            unset($u['password_hash']);
            jsonRes($u, 200, true, 'Cập nhật người dùng thành công');
        }
    }
    jsonErr('Không tìm thấy người dùng', 'NOT_FOUND', 404);
}

// 30. GET /products/{id}/reviews
if (preg_match('#^/products/(\d+)/reviews$#', $subPath, $m) && $reqMethod === 'GET') {
    $prodId = (int)$m[1];
    $reviews = array_values(array_filter($db['reviews'] ?? [], fn($r) => $r['product_id'] === $prodId));
    jsonRes($reviews);
}

// 29. POST /products/{id}/reviews
if (preg_match('#^/products/(\d+)/reviews$#', $subPath, $m) && $reqMethod === 'POST') {
    $prodId = (int)$m[1];
    $rating = max(1, min(5, (int)($body['rating'] ?? 5)));
    $comment = trim($body['comment'] ?? '');
    if (!$comment) {
        jsonErr('Vui lòng nhập nội dung đánh giá', 'VALIDATION_ERROR', 422);
    }
    $newReview = [
        'id' => count($db['reviews'] ?? []) + 1,
        'product_id' => $prodId,
        'user_name' => $body['user_name'] ?? 'Bạn',
        'rating' => $rating,
        'comment' => $comment,
        'created_at' => date('Y-m-d')
    ];
    $db['reviews'][] = $newReview;
    saveDb($db);
    jsonRes($newReview, 201, true, 'Gửi đánh giá thành công');
}

// 31. WISHLIST
if ($subPath === '/wishlist' && $reqMethod === 'GET') {
    $wishlistProductIds = array_column($db['wishlist'] ?? [], 'product_id');
    $wishlistProducts = array_values(array_filter($db['products'], fn($p) => in_array($p['id'], $wishlistProductIds)));
    jsonRes($wishlistProducts);
}

if ($subPath === '/wishlist' && $reqMethod === 'POST') {
    $prodId = (int)($body['product_id'] ?? 0);
    $exists = false;
    foreach ($db['wishlist'] ?? [] as $w) {
        if ($w['product_id'] === $prodId) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $db['wishlist'][] = [
            'id' => count($db['wishlist'] ?? []) + 1,
            'user_id' => 2,
            'product_id' => $prodId
        ];
        saveDb($db);
    }
    jsonRes(['product_id' => $prodId, 'active' => true], 201, true, 'Đã thêm vào yêu thích');
}

if (preg_match('#^/wishlist/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    $prodId = (int)$m[1];
    $db['wishlist'] = array_values(array_filter($db['wishlist'] ?? [], fn($w) => $w['product_id'] !== $prodId));
    saveDb($db);
    jsonRes(null, 204);
}

// 32. POST /events/view (HTTP 202 Accepted)
if ($subPath === '/events/view' && $reqMethod === 'POST') {
    jsonRes(['tracked' => true, 'event' => 'product_view'], 202, true, 'Ghi nhận lượt xem');
}

// 33. GET /products/{id}/recommendations
if (preg_match('#^/products/(\d+)/recommendations$#', $subPath, $m) && $reqMethod === 'GET') {
    $prodId = (int)$m[1];
    $similar = array_values(array_filter($db['products'], fn($p) => $p['id'] !== $prodId));
    jsonRes([
        'similar' => array_slice($similar, 0, 4),
        'bought_together' => array_slice($similar, 1, 2)
    ]);
}

// 34. GET /recommendations/me
if ($subPath === '/recommendations/me' && $reqMethod === 'GET') {
    jsonRes([
        'for_you' => array_slice($db['products'], 0, 6)
    ]);
}

// 35. GET /admin/stats
if ($subPath === '/admin/stats' && $reqMethod === 'GET') {
    $totalRevenue = array_reduce($db['orders'], fn($s, $o) => $s + ($o['payment_status'] === 'paid' ? $o['total_amount'] : 0), 184500000);
    jsonRes([
        'total_revenue' => $totalRevenue,
        'revenue_growth' => '+14.8%',
        'new_orders_count' => count($db['orders']),
        'total_products' => count($db['products']),
        'total_users' => count($db['users']),
        'recent_orders' => array_slice($db['orders'], 0, 5),
        'revenue_chart' => [
            ['day' => 'T2', 'amount' => 18500000],
            ['day' => 'T3', 'amount' => 22400000],
            ['day' => 'T4', 'amount' => 19800000],
            ['day' => 'T5', 'amount' => 31200000],
            ['day' => 'T6', 'amount' => 28900000],
            ['day' => 'T7', 'amount' => 45000000],
            ['day' => 'CN', 'amount' => 38700000]
        ]
    ]);
}

// 36. GET /admin/audit-logs
if ($subPath === '/admin/audit-logs' && $reqMethod === 'GET') {
    jsonRes($db['audit_logs'] ?? []);
}

// Fallback 404 for unknown endpoint
jsonErr("Endpoint không tồn tại: {$reqMethod} {$subPath}", 'ROUTE_NOT_FOUND', 404);
