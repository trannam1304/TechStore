<?php
/**
 * TechStore - RESTful API Router
 * Base URL: /api/v1
 * Chuyển tiếp các yêu cầu HTTP sang Controller chuyên biệt theo kiến trúc MVC chuẩn mực
 */

// 1. Load các dependencies và middleware
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/services/JwtService.php';
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/helpers/Validator.php';
require_once __DIR__ . '/helpers/ErrorHandler.php';
require_once __DIR__ . '/helpers/DataStore.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/RoleMiddleware.php';

// 2. Load toàn bộ 11 Controller chuyên biệt
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CategoryBrandController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/CouponController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/PaymentController.php';
require_once __DIR__ . '/controllers/ReviewWishlistController.php';
require_once __DIR__ . '/controllers/RecommendationController.php';
require_once __DIR__ . '/controllers/AdminController.php';

use Api\Services\JwtService;
use Api\Helpers\Response;
use Api\Helpers\Validator;
use Api\Helpers\ErrorHandler;
use Api\Helpers\DataStore;
use Api\Middleware\CorsMiddleware;
use Api\Middleware\AuthMiddleware;
use Api\Middleware\RoleMiddleware;
use Api\Controllers\BaseController;
use Api\Controllers\AuthController;
use Api\Controllers\ProductController;
use Api\Controllers\CategoryBrandController;
use Api\Controllers\CartController;
use Api\Controllers\CouponController;
use Api\Controllers\OrderController;
use Api\Controllers\PaymentController;
use Api\Controllers\ReviewWishlistController;
use Api\Controllers\RecommendationController;
use Api\Controllers\AdminController;

// 3. Xử lý CORS Preflight và Đăng ký ErrorHandler chuẩn JSON
CorsMiddleware::handle();
ErrorHandler::register();

// Helper hàm jsonRes và jsonErr (Tương thích ngược)
function jsonRes($data = null, int $status = 200, bool $success = true, string $message = ''): void {
    BaseController::jsonRes($data, $status, $success, $message);
}

function jsonErr(string $message, string $code = 'ERROR', int $status = 400): void {
    BaseController::jsonErr($message, $code, $status);
}

function getDb(): array {
    return BaseController::getDb();
}

function saveDb(array $data): void {
    BaseController::saveDb($data);
}

function getCurrentAuthUser(?array $db = null): ?array {
    return BaseController::getCurrentAuthUser($db);
}

// 4. Trích xuất Request URL, HTTP Method và Body dữ liệu
$reqUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Hỗ trợ X-HTTP-Method-Override
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $reqMethod = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
}

// Bỏ tiền tố /api/v1 hoặc /api
$subPath = preg_replace('#^/api(/v1)?#', '', $reqUri);
$subPath = '/' . ltrim($subPath, '/');
if ($subPath === '' || $subPath === '/') {
    $subPath = '/';
}

// Đọc dữ liệu JSON Body
$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true) ?? [];

// =========================================================================
// ROUTING TẬP TRUNG - ĐIỀU PHỐI ĐẾN CONTROLLER TƯƠNG ỨNG
// =========================================================================

// 0. HEALTH CHECK PING
if ($subPath === '/ping' && $reqMethod === 'GET') {
    Response::success(['status' => 'OK', 'timestamp' => date('c'), 'service' => 'TechStore REST API v1']);
}

// -------------------------------------------------------------------------
// 1. AUTHENTICATION & JWT (AuthController)
// -------------------------------------------------------------------------
if ($subPath === '/auth/register' && $reqMethod === 'POST') {
    AuthController::register($body);
}

if ($subPath === '/auth/login' && $reqMethod === 'POST') {
    AuthController::login($body);
}

if ($subPath === '/auth/refresh' && $reqMethod === 'POST') {
    AuthController::refresh($body);
}

if ($subPath === '/auth/logout' && $reqMethod === 'POST') {
    $user = AuthMiddleware::handle();
    AuthController::logout($user, $body);
}

if ($subPath === '/auth/me' && $reqMethod === 'GET') {
    $user = AuthMiddleware::handle();
    AuthController::me($user);
}

if ($subPath === '/auth/google' && $reqMethod === 'POST') {
    AuthController::google($body);
}

// -------------------------------------------------------------------------
// 2. PRODUCTS (ProductController)
// -------------------------------------------------------------------------
if ($subPath === '/products' && $reqMethod === 'GET') {
    ProductController::index($_GET);
}

if (preg_match('#^/products/(\d+)$#', $subPath, $m) && $reqMethod === 'GET') {
    ProductController::show((int)$m[1]);
}

if ($subPath === '/products' && $reqMethod === 'POST') {
    ProductController::create($body);
}

if (preg_match('#^/products/(\d+)$#', $subPath, $m) && ($reqMethod === 'PUT' || $reqMethod === 'PATCH')) {
    ProductController::update((int)$m[1], $body);
}

if (preg_match('#^/products/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    ProductController::destroy((int)$m[1]);
}

// -------------------------------------------------------------------------
// 3. CATEGORIES & BRANDS (CategoryBrandController)
// -------------------------------------------------------------------------
if ($subPath === '/categories' && $reqMethod === 'GET') {
    CategoryBrandController::getCategories();
}

if ($subPath === '/categories' && $reqMethod === 'POST') {
    CategoryBrandController::createCategory($body);
}

if (preg_match('#^/categories/(\d+)$#', $subPath, $m) && $reqMethod === 'PUT') {
    CategoryBrandController::updateCategory((int)$m[1], $body);
}

if (preg_match('#^/categories/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    CategoryBrandController::deleteCategory((int)$m[1]);
}

if ($subPath === '/brands' && $reqMethod === 'GET') {
    CategoryBrandController::getBrands();
}

if ($subPath === '/brands' && $reqMethod === 'POST') {
    CategoryBrandController::createBrand($body);
}

if (preg_match('#^/brands/(\d+)$#', $subPath, $m) && $reqMethod === 'PUT') {
    CategoryBrandController::updateBrand((int)$m[1], $body);
}

if (preg_match('#^/brands/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    CategoryBrandController::deleteBrand((int)$m[1]);
}

// -------------------------------------------------------------------------
// 4. CART (CartController)
// -------------------------------------------------------------------------
if ($subPath === '/cart' && $reqMethod === 'GET') {
    CartController::getCart();
}

if ($subPath === '/cart/items' && $reqMethod === 'POST') {
    CartController::addItem($body);
}

if (preg_match('#^/cart/items/(\d+)$#', $subPath, $m) && $reqMethod === 'PUT') {
    CartController::updateItem((int)$m[1], $body);
}

if (preg_match('#^/cart/items/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    CartController::removeItem((int)$m[1]);
}

if ($subPath === '/cart' && $reqMethod === 'DELETE') {
    CartController::clearCart();
}

// -------------------------------------------------------------------------
// 5. COUPONS (CouponController)
// -------------------------------------------------------------------------
if ($subPath === '/coupons/validate' && $reqMethod === 'POST') {
    CouponController::validateCoupon($body);
}

if ($subPath === '/coupons' && $reqMethod === 'GET') {
    CouponController::getCoupons();
}

// -------------------------------------------------------------------------
// 6. ORDERS (OrderController)
// -------------------------------------------------------------------------
if ($subPath === '/orders' && $reqMethod === 'POST') {
    OrderController::createOrder($body);
}

if ($subPath === '/orders' && $reqMethod === 'GET') {
    OrderController::getOrders();
}

if (preg_match('#^/orders/(\d+)$#', $subPath, $m) && $reqMethod === 'GET') {
    OrderController::getOrder((int)$m[1]);
}

if (preg_match('#^/orders/(\d+)/cancel$#', $subPath, $m) && $reqMethod === 'POST') {
    OrderController::cancelOrder((int)$m[1]);
}

if (preg_match('#^/orders/(\d+)/status$#', $subPath, $m) && $reqMethod === 'PATCH') {
    OrderController::updateStatus((int)$m[1], $body);
}

// -------------------------------------------------------------------------
// 7. PAYMENTS (VIETQR) (PaymentController)
// -------------------------------------------------------------------------
if ($subPath === '/payments/qr' && $reqMethod === 'POST') {
    PaymentController::generateQr($body);
}

if (preg_match('#^/payments/(\d+)/status$#', $subPath, $m) && $reqMethod === 'GET') {
    PaymentController::getStatus((int)$m[1]);
}

if ($subPath === '/payments/webhook' && $reqMethod === 'POST') {
    PaymentController::webhook($body);
}

// -------------------------------------------------------------------------
// 8. ADMIN DASHBOARD & USERS (AdminController)
// -------------------------------------------------------------------------
if ($subPath === '/admin/users' && $reqMethod === 'GET') {
    AdminController::listUsers();
}

if (preg_match('#^/admin/users/(\d+)$#', $subPath, $m) && $reqMethod === 'PATCH') {
    AdminController::patchUser((int)$m[1], $body);
}

if ($subPath === '/admin/stats' && $reqMethod === 'GET') {
    AdminController::stats();
}

if ($subPath === '/admin/audit-logs' && $reqMethod === 'GET') {
    AdminController::auditLogs();
}

// -------------------------------------------------------------------------
// 9. REVIEWS & WISHLIST (ReviewWishlistController)
// -------------------------------------------------------------------------
if (preg_match('#^/products/(\d+)/reviews$#', $subPath, $m) && $reqMethod === 'GET') {
    ReviewWishlistController::getProductReviews((int)$m[1]);
}

if (preg_match('#^/products/(\d+)/reviews$#', $subPath, $m) && $reqMethod === 'POST') {
    ReviewWishlistController::createReview((int)$m[1], $body);
}

if ($subPath === '/wishlist' && $reqMethod === 'GET') {
    ReviewWishlistController::getWishlist();
}

if ($subPath === '/wishlist' && $reqMethod === 'POST') {
    ReviewWishlistController::addToWishlist($body);
}

if (preg_match('#^/wishlist/(\d+)$#', $subPath, $m) && $reqMethod === 'DELETE') {
    ReviewWishlistController::removeFromWishlist((int)$m[1]);
}

// -------------------------------------------------------------------------
// 10. RECOMMENDATIONS & EVENTS (RecommendationController)
// -------------------------------------------------------------------------
if ($subPath === '/events/view' && $reqMethod === 'POST') {
    RecommendationController::trackView($body);
}

if (preg_match('#^/products/(\d+)/recommendations$#', $subPath, $m) && $reqMethod === 'GET') {
    RecommendationController::forProduct((int)$m[1]);
}

if ($subPath === '/recommendations/me' && $reqMethod === 'GET') {
    RecommendationController::forMe();
}

// -------------------------------------------------------------------------
// FALLBACK 404
// -------------------------------------------------------------------------
Response::error("Endpoint không tồn tại: {$reqMethod} {$subPath}", 'ROUTE_NOT_FOUND', 404);
