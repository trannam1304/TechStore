<?php
/**
 * TechStore - Payment Controller
 * Tái cấu trúc từ logic monolith ban đầu
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class PaymentController extends BaseController {
    public function generateQr(Request $request): void {
        $body = $request->body();
        $orderId = (int)($body['order_id'] ?? 1001);
        $amount = (int)($body['amount'] ?? 1000000);
        $orderCode = $body['order_code'] ?? "TS-{$orderId}";

        $bank = 'MB';
        $account = '0987654321';
        $accountName = 'TECHSTORE VIETNAM';
        $qrUrl = "https://img.vietqr.io/image/{$bank}-{$account}-compact2.png?amount={$amount}&addInfo=" . urlencode($orderCode) . "&accountName=" . urlencode($accountName);

        Response::json([
            'qr_url' => $qrUrl,
            'bank_name' => 'Ngân hàng Quân Đội (MB Bank)',
            'account_number' => $account,
            'account_name' => $accountName,
            'amount' => $amount,
            'description' => $orderCode,
            'order_code' => $orderCode,
            'expires_in' => 900
        ], 201, true, 'Tạo mã VietQR thành công');
    }

    public function checkStatus(Request $request): void {
        $orderId = $request->param('orderId');

        $order = null;
        if (is_numeric($orderId)) {
            $order = $this->model->queryOne("SELECT id, order_code, payment_status, status FROM orders WHERE id = :id", ['id' => (int)$orderId]);
        } else {
            $order = $this->model->queryOne("SELECT id, order_code, payment_status, status FROM orders WHERE order_code = :code", ['code' => $orderId]);
        }

        if (!$order) {
            Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
        }

        Response::json([
            'order_code' => $order['order_code'],
            'payment_status' => $order['payment_status'],
            'is_paid' => $order['payment_status'] === 'paid',
            'order_status' => $order['status']
        ]);
    }

    public function confirmWebhook(Request $request): void {
        $body = $request->body();
        $orderCode = $body['order_code'] ?? ($body['description'] ?? '');

        if (!$orderCode) {
            Response::error('Thiếu mã đơn hàng', 'MISSING_ORDER_CODE', 400);
        }

        $order = $this->model->queryOne("SELECT * FROM orders WHERE order_code = :code", ['code' => $orderCode]);
        if (!$order) {
            Response::error('Đơn hàng không tồn tại', 'ORDER_NOT_FOUND', 404);
        }

        $this->model->execute(
            "UPDATE orders SET payment_status = 'paid', status = 'confirmed' WHERE id = :id",
            ['id' => $order['id']]
        );

        Response::json(['message' => 'Xác nhận thanh toán qua Webhook thành công']);
    }
}
