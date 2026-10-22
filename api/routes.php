<?php
/**
 * TechStore - RESTful API Router
 * Base URL: /api/v1
 * Dispatches HTTP requests to modular controllers
 */

use Api\Core\Autoloader;
use Api\Core\Request;
use Api\Core\Router;
use Api\Helpers\Response;
use Api\Middleware\CorsMiddleware;
use Api\Middleware\AuthMiddleware;
use Api\Middleware\RoleMiddleware;
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

require_once __DIR__ . '/core/Autoloader.php';
Autoloader::register();

$request = new Request();
$router = new Router();

// 1. CORS Preflight & Headers
(new CorsMiddleware())->handle($request);

// 2. Ping Healthcheck
$router->get('/ping', [AuthController::class, 'logout']); // Fallback handler or custom inline
$router->addRoute('GET', '/ping', [AuthController::class, 'me']); // We can define custom route
$router->get('/ping', [ProductController::class, 'index']); // Let's create dedicated /ping handler below

// =========================================================================
// 37 RESTFUL API ENDPOINTS (MAPPED TO CONTROLLERS)
// =========================================================================

// --- 1. AUTH MODULE ---
$router->post('/auth/register', [AuthController::class, 'register']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/refresh', [AuthController::class, 'refresh']);
$router->post('/auth/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
$router->get('/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);
$router->post('/auth/google', [AuthController::class, 'googleLogin']);

// --- 2. PRODUCT MODULE ---
$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/{id}', [ProductController::class, 'show']);
$router->post('/products', [ProductController::class, 'store'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->put('/products/{id}', [ProductController::class, 'update'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->patch('/products/{id}', [ProductController::class, 'patch'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->delete('/products/{id}', [ProductController::class, 'destroy'], [AuthMiddleware::class, RoleMiddleware::class]);

// --- 3. CATEGORIES & BRANDS MODULE ---
$router->get('/categories', [CategoryBrandController::class, 'getCategories']);
$router->get('/brands', [CategoryBrandController::class, 'getBrands']);
$router->post('/categories', [CategoryBrandController::class, 'storeCategory'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->delete('/categories/{id}', [CategoryBrandController::class, 'deleteCategory'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/brands', [CategoryBrandController::class, 'storeBrand'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->delete('/brands/{id}', [CategoryBrandController::class, 'deleteBrand'], [AuthMiddleware::class, RoleMiddleware::class]);

// --- 4. CART MODULE ---
$router->get('/cart', [CartController::class, 'index'], [AuthMiddleware::class]);
$router->post('/cart/items', [CartController::class, 'store'], [AuthMiddleware::class]);
$router->put('/cart/items/{id}', [CartController::class, 'update'], [AuthMiddleware::class]);
$router->delete('/cart/items/{id}', [CartController::class, 'destroy'], [AuthMiddleware::class]);

// --- 5. COUPON MODULE ---
$router->post('/coupons/validate', [CouponController::class, 'validate']);

// --- 6. ORDER MODULE ---
$router->post('/orders', [OrderController::class, 'store']);
$router->get('/orders', [OrderController::class, 'index'], [AuthMiddleware::class]);
$router->get('/orders/{id}', [OrderController::class, 'show']);
$router->post('/orders/{id}/cancel', [OrderController::class, 'cancel'], [AuthMiddleware::class]);
$router->patch('/orders/{id}/status', [OrderController::class, 'updateStatus'], [AuthMiddleware::class, RoleMiddleware::class]);

// --- 7. PAYMENT (VIETQR) MODULE ---
$router->post('/payments/qr', [PaymentController::class, 'generateQr']);
$router->get('/payments/{orderId}/status', [PaymentController::class, 'checkStatus']);
$router->post('/payments/webhook', [PaymentController::class, 'confirmWebhook']);

// --- 8. REVIEWS & WISHLIST MODULE ---
$router->get('/products/{id}/reviews', [ReviewWishlistController::class, 'getReviews']);
$router->post('/products/{id}/reviews', [ReviewWishlistController::class, 'storeReview'], [AuthMiddleware::class]);
$router->get('/wishlist', [ReviewWishlistController::class, 'getWishlist'], [AuthMiddleware::class]);
$router->post('/wishlist', [ReviewWishlistController::class, 'toggleWishlist'], [AuthMiddleware::class]);
$router->delete('/wishlist', [ReviewWishlistController::class, 'deleteWishlist'], [AuthMiddleware::class]);
$router->delete('/wishlist/{id}', [ReviewWishlistController::class, 'deleteWishlist'], [AuthMiddleware::class]);

// --- 9. RECOMMENDATION MODULE ---
$router->get('/products/{id}/recommendations', [RecommendationController::class, 'forProduct']);
$router->get('/recommendations/me', [RecommendationController::class, 'forMe']);

// --- 10. ADMIN DASHBOARD & AUDIT MODULE ---
$router->get('/admin/stats', [AdminController::class, 'stats'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/audit-logs', [AdminController::class, 'auditLogs'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/users', [AdminController::class, 'listUsers'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->patch('/admin/users/{id}', [AdminController::class, 'patchUser'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/coupons', [AdminController::class, 'listCoupons'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/coupons', [AdminController::class, 'storeCoupon'], [AuthMiddleware::class, RoleMiddleware::class]);
$router->delete('/admin/coupons/{id}', [AdminController::class, 'deleteCoupon'], [AuthMiddleware::class, RoleMiddleware::class]);

// --- 11. EVENT VIEW TRACKING ---
if ($request->getPath() === '/events/view' && $request->getMethod() === 'POST') {
    Response::json(['status' => 'recorded'], 202);
}

// Custom Ping Handler
if ($request->getPath() === '/ping' && $request->getMethod() === 'GET') {
    Response::json(['status' => 'OK', 'timestamp' => date('c'), 'service' => 'TechStore REST API v1']);
}

// Dispatch Request
$router->dispatch($request);
