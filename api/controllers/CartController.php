<?php
/**
 * TechStore - Cart Controller (Clean MVC Orchestrator)
 * FR04: Quản lý Giỏ hàng và Kiểm soát Tồn kho thời gian thực (Server-side Cart & Realtime Inventory)
 * Tuân thủ chuẩn RESTful API, loại bỏ phụ thuộc data.json, điều phối CartModel
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

use Api\Models\CartModel;
use Api\Middleware\AuthMiddleware;
use Api\Helpers\Response;
use Api\Helpers\Validator;
use Throwable;

class CartController extends BaseController {
    /**
     * Xác định User ID của phiên làm việc
     * 1. Lấy từ Access Token (JWT Bearer)
     * 2. Hoặc Header X-User-Id (nếu test môi trường)
     * 3. Mặc định fallback về User 2 (Khách hàng mẫu)
     */
    protected static function resolveUserId(): int {
        $payload = AuthMiddleware::optional();
        if ($payload && !empty($payload['sub'])) {
            return (int)$payload['sub'];
        }

        if (!empty($_SERVER['HTTP_X_USER_ID'])) {
            return (int)$_SERVER['HTTP_X_USER_ID'];
        }

        return 2;
    }

    /**
     * GET /api/v1/cart
     * FR04.01: Lấy thông tin giỏ hàng của người dùng hiện tại kèm tổng tiền và đối chiếu tồn kho
     */
    public static function getCart(): void {
        $userId = self::resolveUserId();
        $cart = CartModel::getCart($userId);
        Response::success($cart, 200);
    }

    /**
     * POST /api/v1/cart/items
     * FR04.02 / BR04: Thêm sản phẩm vào giỏ hàng với kiểm tra tồn kho nghiêm ngặt
     */
    public static function addItem(array $body): void {
        $userId = self::resolveUserId();

        $validator = Validator::make($body, [
            'product_id' => 'required',
            'quantity'   => 'required'
        ]);

        if ($validator->fails()) {
            Response::error('Dữ liệu yêu cầu không hợp lệ', 'VALIDATION_ERROR', 400, $validator->getErrors());
        }

        $productId = (int)($body['product_id'] ?? 0);
        $quantity = (int)($body['quantity'] ?? 1);
        $variant = trim((string)($body['variant'] ?? 'Mặc định'));

        if ($quantity <= 0) {
            Response::error('Số lượng sản phẩm thêm vào giỏ phải lớn hơn 0', 'INVALID_QUANTITY', 400);
        }

        try {
            $cart = CartModel::addItem($userId, $productId, $quantity, $variant);
            Response::success($cart, 201, 'Đã thêm sản phẩm vào giỏ hàng');
        } catch (Throwable $e) {
            $code = $e->getCode();
            $statusCode = (is_numeric($code) && (int)$code >= 400 && (int)$code < 600) ? (int)$code : 400;
            $errCode = ($statusCode === 404) ? 'NOT_FOUND' : 'INSUFFICIENT_STOCK';
            Response::error($e->getMessage(), $errCode, $statusCode);
        }
    }

    /**
     * PUT /api/v1/cart/items/{id}
     * FR04.03 / BR04: Cập nhật số lượng món đồ trong giỏ
     * - Tự động xóa nếu quantity <= 0
     * - Kiểm tra tồn kho khả dụng
     */
    public static function updateItem(int $itemId, array $body): void {
        $userId = self::resolveUserId();

        if (!isset($body['quantity'])) {
            Response::error('Thiếu trường số lượng mới (quantity)', 'VALIDATION_ERROR', 400);
        }

        $quantity = (int)$body['quantity'];

        try {
            $cart = CartModel::updateItem($userId, $itemId, $quantity);
            $msg = ($quantity <= 0) 
                ? 'Đã tự động xóa sản phẩm khỏi giỏ do số lượng về 0' 
                : 'Đã cập nhật số lượng món đồ trong giỏ';
            Response::success($cart, 200, $msg);
        } catch (Throwable $e) {
            $code = $e->getCode();
            $statusCode = (is_numeric($code) && (int)$code >= 400 && (int)$code < 600) ? (int)$code : 400;
            $errCode = ($statusCode === 404) ? 'NOT_FOUND' : 'INSUFFICIENT_STOCK';
            Response::error($e->getMessage(), $errCode, $statusCode);
        }
    }

    /**
     * DELETE /api/v1/cart/items/{id}
     * FR04.04: Xóa một sản phẩm khỏi giỏ hàng
     */
    public static function removeItem(int $itemId): void {
        $userId = self::resolveUserId();

        try {
            $cart = CartModel::removeItem($userId, $itemId);
            Response::success($cart, 200, 'Đã xóa sản phẩm khỏi giỏ hàng thành công');
        } catch (Throwable $e) {
            Response::error($e->getMessage(), 'ERROR', 400);
        }
    }

    /**
     * DELETE /api/v1/cart
     * FR04.04: Làm trống toàn bộ giỏ hàng của người dùng
     */
    public static function clearCart(): void {
        $userId = self::resolveUserId();
        CartModel::clear($userId);
        $cart = CartModel::getCart($userId);
        Response::success($cart, 200, 'Đã làm trống toàn bộ giỏ hàng');
    }
}
