<?php
/**
 * TechStore - Order Controller
 * Quản lý đơn hàng: đặt hàng, xem danh sách, xem chi tiết, hủy đơn, cập nhật trạng thái thuần MySQL
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/CartModel.php';

use Api\Helpers\Response;
use Api\Models\OrderModel;
use Api\Models\CartModel;
use Exception;

class OrderController extends BaseController {
    /**
     * POST /api/v1/orders
     * Tạo đơn hàng mới từ giỏ hàng hoặc payload
     */
    public static function createOrder(array $body): void {
        $user = self::getCurrentAuthUser();
        $userId = $user ? (int)$user['id'] : null;

        // Lấy danh sách sản phẩm từ body hoặc từ giỏ hàng của user
        $items = $body['items'] ?? [];
        if (empty($items) && $userId) {
            $cart = CartModel::getCart($userId);
            $items = $cart['items'] ?? [];
        }

        if (empty($items)) {
            Response::error('Giỏ hàng hoặc danh sách sản phẩm đặt hàng không được để trống', 'EMPTY_ORDER_ITEMS', 400);
            return;
        }

        $orderData = [
            'order_code'       => 'TS-' . rand(100000, 999999),
            'user_id'          => $userId,
            'customer_name'    => $body['customer_name'] ?? ($user['name'] ?? 'Khách hàng'),
            'customer_phone'   => $body['customer_phone'] ?? ($user['phone'] ?? ''),
            'customer_email'   => $body['customer_email'] ?? ($user['email'] ?? ''),
            'shipping_address' => $body['shipping_address'] ?? ($user['address'] ?? 'TP.HCM'),
            'payment_method'   => $body['payment_method'] ?? 'vietqr',
            'payment_status'   => 'pending',
            'status'           => 'pending',
            'total_amount'     => (int)($body['total_amount'] ?? 0),
            'discount_amount'  => (int)($body['discount_amount'] ?? 0),
            'shipping_fee'     => (int)($body['shipping_fee'] ?? 0),
            'coupon_code'      => $body['coupon_code'] ?? null,
            'note'             => $body['note'] ?? ''
        ];

        try {
            $order = OrderModel::createOrder($orderData, $items);
            Response::success($order, 201, 'Tạo đơn hàng thành công');
        } catch (Exception $e) {
            Response::error('Lỗi khi tạo đơn hàng: ' . $e->getMessage(), 'ORDER_CREATE_FAILED', 500);
        }
    }

    /**
     * GET /api/v1/orders
     * Lấy danh sách đơn hàng
     */
    public static function getOrders(): void {
        $user = self::getCurrentAuthUser();
        $userId = null;
        if ($user && ($user['role'] ?? '') !== 'admin') {
            $userId = (int)$user['id'];
        }

        try {
            $orders = OrderModel::getAll($userId);
            Response::success($orders, 200);
        } catch (Exception $e) {
            Response::error('Lỗi truy vấn đơn hàng: ' . $e->getMessage(), 'ORDER_FETCH_FAILED', 500);
        }
    }

    /**
     * GET /api/v1/orders/{id}
     * Lấy thông tin chi tiết một đơn hàng theo ID
     */
    public static function getOrder(int $id): void {
        try {
            $order = OrderModel::getOrderById($id);
            if (!$order) {
                Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
                return;
            }

            // Kiểm tra phân quyền: customer chỉ xem đơn của chính mình
            $user = self::getCurrentAuthUser();
            if ($user && ($user['role'] ?? '') !== 'admin') {
                if (!empty($order['user_id']) && (int)$order['user_id'] !== (int)$user['id']) {
                    Response::error('Bạn không có quyền xem đơn hàng này', 'FORBIDDEN', 403);
                    return;
                }
            }

            Response::success($order, 200);
        } catch (Exception $e) {
            Response::error('Lỗi truy vấn đơn hàng: ' . $e->getMessage(), 'ORDER_FETCH_FAILED', 500);
        }
    }

    /**
     * POST /api/v1/orders/{id}/cancel
     * Hủy đơn hàng đang chờ xử lý
     */
    public static function cancelOrder(int $id): void {
        try {
            $order = OrderModel::getOrderById($id);
            if (!$order) {
                Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
                return;
            }

            if ($order['status'] !== 'pending') {
                Response::error('Chỉ được hủy đơn hàng khi trạng thái là Đang chờ xử lý', 'CANNOT_CANCEL', 400);
                return;
            }

            $success = OrderModel::cancelOrder($id);
            if ($success) {
                Response::success(OrderModel::getOrderById($id), 200, 'Hủy đơn hàng thành công');
            } else {
                Response::error('Không thể hủy đơn hàng này', 'CANCEL_FAILED', 400);
            }
        } catch (Exception $e) {
            Response::error('Lỗi khi hủy đơn hàng: ' . $e->getMessage(), 'CANCEL_ERROR', 500);
        }
    }

    /**
     * PATCH /api/v1/orders/{id}/status
     * Cập nhật trạng thái đơn hàng (Dành cho Quản trị viên)
     */
    public static function updateStatus(int $id, array $body): void {
        $status = $body['status'] ?? null;
        $paymentStatus = $body['payment_status'] ?? null;

        if (!$status && !$paymentStatus) {
            Response::error('Vui lòng cung cấp trạng thái cần cập nhật', 'VALIDATION_ERROR', 422);
            return;
        }

        try {
            $order = OrderModel::getOrderById($id);
            if (!$order) {
                Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
                return;
            }

            OrderModel::updateStatus($id, $status ?: $order['status'], $paymentStatus);
            Response::success(OrderModel::getOrderById($id), 200, 'Cập nhật trạng thái đơn hàng thành công');
        } catch (Exception $e) {
            Response::error('Lỗi khi cập nhật trạng thái: ' . $e->getMessage(), 'UPDATE_FAILED', 500);
        }
    }
}
