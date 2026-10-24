<?php
/**
 * TechStore - Payment Controller
 * Quản lý cổng thanh toán: tạo mã VietQR động, tra cứu trạng thái thanh toán và webhook xác nhận thuần MySQL
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/PaymentModel.php';
require_once __DIR__ . '/../models/OrderModel.php';

use Api\Helpers\Response;
use Api\Models\PaymentModel;
use Api\Models\OrderModel;
use Exception;

class PaymentController extends BaseController {
    /**
     * POST /api/v1/payments/qr
     * Sinh mã VietQR động kèm thông tin chuyển khoản chính xác và lưu vào MySQL
     */
    public static function generateQr(array $body): void {
        $orderId = (int)($body['order_id'] ?? 0);
        $amount = (int)($body['amount'] ?? 0);
        $orderCode = $body['order_code'] ?? "TS-{$orderId}";

        if ($orderId > 0 && $amount <= 0) {
            $order = OrderModel::getOrderById($orderId);
            if ($order) {
                $amount = (int)$order['total_amount'];
                $orderCode = $order['order_code'];
            }
        }

        $bank = getenv('BANK_ID') ?: 'MB';
        $account = getenv('ACCOUNT_NO') ?: '0987654321';
        $accountName = getenv('ACCOUNT_NAME') ?: 'TECHSTORE VIETNAM';
        $template = getenv('VIETQR_TEMPLATE') ?: 'compact2';

        $qrUrl = "https://img.vietqr.io/image/{$bank}-{$account}-{$template}.png?amount={$amount}&addInfo=" . urlencode($orderCode) . "&accountName=" . urlencode($accountName);

        $paymentData = [
            'order_id'       => $orderId,
            'payment_code'   => 'PAY-' . $orderCode,
            'payment_method' => 'vietqr',
            'amount'         => $amount,
            'status'         => 'pending',
            'qr_code_url'    => $qrUrl,
            'payment_note'   => "Thanh toán đơn hàng {$orderCode}"
        ];

        if ($orderId > 0) {
            try {
                PaymentModel::createOrUpdatePayment($orderId, $paymentData);
            } catch (Exception $e) {
                // Tiếp tục trả mã QR ngay cả khi log payment bị gián đoạn
            }
        }

        Response::success([
            'qr_url'         => $qrUrl,
            'bank_name'      => 'Ngân hàng Quân Đội (MB Bank)',
            'account_number' => $account,
            'account_name'   => $accountName,
            'amount'         => $amount,
            'order_code'     => $orderCode,
            'expires_in'     => 900
        ], 201, 'Khởi tạo mã VietQR thành công');
    }

    /**
     * GET /api/v1/payments/{orderId}/status
     * Kiểm tra trạng thái thanh toán của một đơn hàng
     */
    public static function getStatus(int $orderId): void {
        try {
            $order = OrderModel::getOrderById($orderId);
            if (!$order) {
                Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
                return;
            }

            $payment = PaymentModel::getByOrderId($orderId);

            Response::success([
                'order_id'       => $orderId,
                'order_code'     => $order['order_code'],
                'payment_status' => $order['payment_status'],
                'order_status'   => $order['status'],
                'total_amount'   => (int)$order['total_amount'],
                'paid_at'        => $payment['paid_at'] ?? null,
                'transaction_id' => $payment['transaction_id'] ?? null
            ], 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi tra cứu thanh toán: ' . $e->getMessage(), 'PAYMENT_FETCH_ERROR', 500);
        }
    }

    /**
     * POST /api/v1/payments/webhook
     * Webhook giả lập / cổng ngân hàng tự động cập nhật trạng thái thanh toán
     */
    public static function webhook(array $body): void {
        $orderCode = $body['order_code'] ?? '';
        $transactionId = $body['transaction_id'] ?? ('TXN-' . time());

        if (!$orderCode) {
            Response::error('Thiếu mã đơn hàng order_code', 'VALIDATION_ERROR', 422);
            return;
        }

        try {
            $order = OrderModel::getOrderByCode($orderCode);
            if (!$order) {
                Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
                return;
            }

            PaymentModel::markAsPaid((int)$order['id'], $transactionId);
            Response::success(['order_code' => $orderCode, 'status' => 'paid'], 200, 'Xác nhận thanh toán thành công');
        } catch (Exception $e) {
            Response::error('Lỗi khi xử lý thanh toán: ' . $e->getMessage(), 'WEBHOOK_ERROR', 500);
        }
    }
}
