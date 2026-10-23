<?php
/**
 * TechStore - Order Controller
 * Quản lý đơn hàng: đặt hàng, xem danh sách đơn hàng, xem chi tiết, hủy đơn, cập nhật trạng thái đơn hàng
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;

class OrderController extends BaseController {
    /**
     * POST /api/v1/orders
     * Tạo đơn hàng mới từ giỏ hàng hoặc thông tin thanh toán
     */
    public static function createOrder(array $body): void {
        $db = self::getDb();
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
        self::saveDb($db);

        Response::success($order, 201, 'Tạo đơn hàng thành công');
    }

    /**
     * GET /api/v1/orders
     * Lấy danh sách toàn bộ đơn hàng
     */
    public static function getOrders(): void {
        $db = self::getDb();
        Response::success($db['orders'] ?? [], 200);
    }

    /**
     * GET /api/v1/orders/{id}
     * Lấy thông tin chi tiết một đơn hàng theo ID
     */
    public static function getOrder(int $id): void {
        $db = self::getDb();
        foreach ($db['orders'] as $ord) {
            if ($ord['id'] === $id) {
                Response::success($ord, 200);
            }
        }
        Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
    }

    /**
     * POST /api/v1/orders/{id}/cancel
     * Hủy đơn hàng đang chờ xử lý
     */
    public static function cancelOrder(int $id): void {
        $db = self::getDb();
        foreach ($db['orders'] as &$ord) {
            if ($ord['id'] === $id) {
                if ($ord['status'] !== 'pending') {
                    Response::error('Chỉ được hủy đơn hàng khi trạng thái là Đang chờ xử lý', 'CANNOT_CANCEL', 400);
                }
                $ord['status'] = 'cancelled';
                self::saveDb($db);
                Response::success(['id' => $id, 'status' => 'cancelled'], 200, 'Đã hủy đơn hàng thành công');
            }
        }
        Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
    }

    /**
     * PATCH /api/v1/orders/{id}/status (Admin)
     * Cập nhật trạng thái đơn hàng và thanh toán
     */
    public static function updateStatus(int $id, array $body): void {
        $db = self::getDb();
        foreach ($db['orders'] as &$ord) {
            if ($ord['id'] === $id) {
                if (isset($body['status'])) $ord['status'] = $body['status'];
                if (isset($body['payment_status'])) $ord['payment_status'] = $body['payment_status'];
                self::saveDb($db);
                Response::success($ord, 200, 'Cập nhật trạng thái đơn hàng thành công');
            }
        }
        Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
    }
}
