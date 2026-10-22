<?php
/**
 * TechStore - Order Controller (5 APIs)
 * Endpoints: POST /orders, GET /orders, GET /orders/{id}, POST /orders/{id}/cancel, PATCH /orders/{id}/status
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;
use Api\Helpers\Validator;

class OrderController extends BaseController {
    public function store(Request $request): void {
        $user = $this->getAuthUser($request);
        $userId = $user ? (int)$user['sub'] : null;
        $body = $request->body();

        if (empty($body['customer_name']) || empty($body['customer_phone']) || empty($body['shipping_address'])) {
            Response::error('Vui lòng điền đầy đủ thông tin nhận hàng (Họ tên, SĐT, Địa chỉ)', 'VALIDATION_ERROR', 422);
        }

        $items = $body['items'] ?? [];
        if (empty($items)) {
            // Try fetching from user's cart if authenticated
            if ($userId) {
                $cartItems = $this->model->query(
                    "SELECT c.product_id, c.quantity, c.variant, p.name as product_name, p.price, p.image
                     FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = :uid",
                    ['uid' => $userId]
                );
                $items = $cartItems;
            }
        }

        if (empty($items)) {
            Response::error('Giỏ hàng trống, không thể tạo đơn hàng', 'EMPTY_ORDER', 400);
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)$item['price'] * (int)$item['quantity'];
        }

        $discount = (float)($body['discount_amount'] ?? 0);
        $totalAmount = max(0, $subtotal - $discount);
        $paymentMethod = $body['payment_method'] ?? 'cod';
        $orderCode = 'TS-' . strtoupper(substr(uniqid(), -6));

        // Use PDO Transaction
        try {
            $this->model->beginTransaction();

            $orderSql = "INSERT INTO orders (order_code, user_id, customer_name, customer_phone, customer_email,
                                           shipping_address, payment_method, payment_status, status, total_amount,
                                           discount_amount, created_at)
                         VALUES (:code, :uid, :name, :phone, :email, :addr, :pay_method, :pay_status, 'pending',
                                 :total, :discount, NOW())";

            $this->model->execute($orderSql, [
                'code' => $orderCode,
                'uid' => $userId,
                'name' => $body['customer_name'],
                'phone' => $body['customer_phone'],
                'email' => $body['customer_email'] ?? ($user['email'] ?? ''),
                'addr' => $body['shipping_address'],
                'pay_method' => $paymentMethod,
                'pay_status' => 'pending',
                'total' => $totalAmount,
                'discount' => $discount
            ]);

            $orderId = $this->model->lastInsertId();

            // Insert Order Items
            $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, variant, image)
                        VALUES (:oid, :pid, :pname, :price, :qty, :variant, :image)";

            foreach ($items as $it) {
                $this->model->execute($itemSql, [
                    'oid' => $orderId,
                    'pid' => $it['product_id'] ?? ($it['id'] ?? 1),
                    'pname' => $it['product_name'] ?? ($it['name'] ?? 'Sản phẩm'),
                    'price' => (float)$it['price'],
                    'qty' => (int)$it['quantity'],
                    'variant' => $it['variant'] ?? 'Mặc định',
                    'image' => $it['image'] ?? ''
                ]);
            }

            // Clear user's cart if authenticated
            if ($userId) {
                $this->model->execute("DELETE FROM cart WHERE user_id = :uid", ['uid' => $userId]);
            }

            $this->model->commit();

            Response::json([
                'order_id' => $orderId,
                'order_code' => $orderCode,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'status' => 'pending'
            ], 201, true, 'Đặt hàng thành công');

        } catch (\Throwable $e) {
            $this->model->rollBack();
            Response::error('Lỗi khi lưu đơn hàng: ' . $e->getMessage(), 'TRANSACTION_FAILED', 500);
        }
    }

    public function index(Request $request): void {
        $user = $this->requireAuthUser($request);
        $isAdmin = ($user['role'] ?? '') === 'admin';

        if ($isAdmin) {
            // Admin sees all orders
            $status = $request->query('status');
            $params = [];
            $where = "1=1";
            if ($status) {
                $where .= " AND status = :st";
                $params['st'] = $status;
            }
            $orders = $this->model->query("SELECT * FROM orders WHERE {$where} ORDER BY id DESC LIMIT 100", $params);
        } else {
            // Customer sees their own orders
            $orders = $this->model->query("SELECT * FROM orders WHERE user_id = :uid ORDER BY id DESC", ['uid' => $user['sub']]);
        }

        Response::json($orders);
    }

    public function show(Request $request): void {
        $id = $request->param('id');
        $order = null;

        if (is_numeric($id)) {
            $order = $this->model->queryOne("SELECT * FROM orders WHERE id = :id", ['id' => (int)$id]);
        } else {
            $order = $this->model->queryOne("SELECT * FROM orders WHERE order_code = :code", ['code' => $id]);
        }

        if (!$order) {
            Response::error('Không tìm thấy đơn hàng', 'ORDER_NOT_FOUND', 404);
        }

        $items = $this->model->query("SELECT * FROM order_items WHERE order_id = :oid", ['oid' => $order['id']]);
        $order['items'] = $items;

        Response::json($order);
    }

    public function cancel(Request $request): void {
        $user = $this->requireAuthUser($request);
        $id = (int)$request->param('id');

        $order = $this->model->queryOne("SELECT * FROM orders WHERE id = :id", ['id' => $id]);
        if (!$order) {
            Response::error('Đơn hàng không tồn tại', 'NOT_FOUND', 404);
        }

        if ($order['user_id'] != $user['sub'] && ($user['role'] ?? '') !== 'admin') {
            Response::error('Bạn không có quyền hủy đơn này', 'FORBIDDEN', 403);
        }

        if ($order['status'] !== 'pending') {
            Response::error('Chỉ có thể hủy đơn hàng khi đang ở trạng thái chờ duyệt', 'CANNOT_CANCEL', 400);
        }

        $this->model->execute("UPDATE orders SET status = 'cancelled' WHERE id = :id", ['id' => $id]);
        Response::json(['message' => 'Hủy đơn hàng thành công']);
    }

    public function updateStatus(Request $request): void {
        $id = (int)$request->param('id');
        $body = $request->body();
        $status = $body['status'] ?? null;
        $paymentStatus = $body['payment_status'] ?? null;

        $existing = $this->model->queryOne("SELECT id FROM orders WHERE id = :id", ['id' => $id]);
        if (!$existing) {
            Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
        }

        $updates = [];
        $params = ['id' => $id];

        if ($status) {
            $updates[] = "status = :st";
            $params['st'] = $status;
        }

        if ($paymentStatus) {
            $updates[] = "payment_status = :pst";
            $params['pst'] = $paymentStatus;
        }

        if (!empty($updates)) {
            $setClause = implode(', ', $updates);
            $this->model->execute("UPDATE orders SET {$setClause} WHERE id = :id", $params);
        }

        $updated = $this->model->queryOne("SELECT * FROM orders WHERE id = :id", ['id' => $id]);
        Response::json($updated, 200, true, 'Cập nhật trạng thái đơn hàng thành công');
    }
}
