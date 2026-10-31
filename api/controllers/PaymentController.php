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
     * Kiểm tra trạng thái thanh toán của một đơn hàng (BR05 & SYS01)
     */
    public static function getStatus(int $orderId): void {
        try {
            $order = OrderModel::getOrderById($orderId);
            if (!$order) {
                Response::error('Không tìm thấy đơn hàng', 'NOT_FOUND', 404);
                return;
            }

            // Tự động kiểm tra thời hạn 15 phút (900s) cho phương thức thanh toán VietQR (SYS01 & BR05)
            $isVietQr = strtolower($order['payment_method'] ?? '') === 'vietqr';
            $isPending = strtolower($order['status'] ?? '') === 'pending' && strtolower($order['payment_status'] ?? '') === 'pending';
            $createdAt = strtotime($order['created_at'] ?? 'now');

            if ($isVietQr && $isPending && ($createdAt + 900 < time())) {
                OrderModel::expireOrder($orderId);
                $order = OrderModel::getOrderById($orderId);
            }

            $payment = PaymentModel::getByOrderId($orderId);
            $paymentStatus = strtoupper($order['payment_status'] ?? 'PENDING');

            Response::success([
                'order_id'       => $orderId,
                'order_code'     => $order['order_code'],
                'payment_status' => $paymentStatus, // 'PENDING' | 'PAID' | 'EXPIRED'
                'order_status'   => strtoupper($order['status']),
                'total_amount'   => (int)$order['total_amount'],
                'is_paid'        => ($paymentStatus === 'PAID'),
                'is_expired'     => ($paymentStatus === 'EXPIRED'),
                'paid_at'        => $payment['paid_at'] ?? null,
                'transaction_id' => $payment['transaction_id'] ?? null
            ], 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi tra cứu thanh toán: ' . $e->getMessage(), 'PAYMENT_FETCH_ERROR', 500);
        }
    }

    /**
     * POST /api/v1/payments/webhook
     * Webhook ngân hàng tự động cập nhật trạng thái thanh toán (BR06 & NFR02.05)
     * Yêu cầu kiểm tra chữ ký số HMAC-SHA256
     */
    public static function webhook(array $body): void {
        // 1. Thẩm định chữ ký số HMAC-SHA256 bí mật (BR06 & NFR02.05)
        $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] 
                  ?? $_SERVER['HTTP_X_SIGNATURE'] 
                  ?? '';

        if (!$signature) {
            Response::error('Thiếu chữ ký xác thực Webhook (Header X-Webhook-Signature)', 'MISSING_SIGNATURE', 401);
            return;
        }

        $secret = getenv('WEBHOOK_SECRET') ?: (getenv('JWT_SECRET') ?: 'techstore_webhook_secret_key_2026');
        $rawPayload = file_get_contents('php://input');

        $expectedSig1 = hash_hmac('sha256', $rawPayload, $secret);
        $expectedSig2 = hash_hmac('sha256', json_encode($body, JSON_UNESCAPED_UNICODE), $secret);
        $expectedSig3 = hash_hmac('sha256', json_encode($body), $secret);

        if (!hash_equals($expectedSig1, $signature) 
         && !hash_equals($expectedSig2, $signature) 
         && !hash_equals($expectedSig3, $signature)) {
            Response::error('Chữ ký số Webhook không hợp lệ (HMAC mismatch)', 'INVALID_WEBHOOK_SIGNATURE', 401);
            return;
        }

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
            Response::success([
                'order_code'     => $orderCode, 
                'status'         => 'paid',
                'transaction_id' => $transactionId
            ], 200, 'Xác nhận thanh toán thành công');
        } catch (Exception $e) {
            Response::error('Lỗi khi xử lý thanh toán: ' . $e->getMessage(), 'WEBHOOK_ERROR', 500);
        }
    }
}
