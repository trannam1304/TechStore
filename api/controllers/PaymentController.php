<?php
/**
 * TechStore - Payment Controller
 * Quản lý cổng thanh toán: tạo mã VietQR động, tra cứu trạng thái thanh toán và webhook xác nhận
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;

class PaymentController extends BaseController {
    /**
     * POST /api/v1/payments/qr
     * Sinh mã VietQR động kèm thông tin chuyển khoản chính xác
     */
    public static function generateQr(array $body): void {
        $orderId = (int)($body['order_id'] ?? 1001);
        $amount = (int)($body['amount'] ?? 1000000);
        $orderCode = $body['order_code'] ?? "TS-{$orderId}";

        $bank = 'MB';
        $account = '0987654321';
        $accountName = 'TECHSTORE VIETNAM';
        $qrUrl = "https://img.vietqr.io/image/{$bank}-{$account}-compact2.png?amount={$amount}&addInfo=" . urlencode($orderCode) . "&accountName=" . urlencode($accountName);

        Response::success([
            'qr_url' => $qrUrl,
            'bank_name' => 'Ngân hàng Quân Đội (MB Bank)',
            'account_number' => $account,
            'account_name' => $accountName,
            'amount' => $amount,
            'order_code' => $orderCode,
            'expires_in' => 900
        ], 201, 'Khởi tạo mã VietQR thành công');
    }

    /**
     * GET /api/v1/payments/{orderId}/status
     * Kiểm tra trạng thái thanh toán của một đơn hàng
     */
    public static function getStatus(int $orderId): void {
        $db = self::getDb();
        $found = null;
        foreach ($db['orders'] as $ord) {
            if ($ord['id'] === $orderId) {
                $found = $ord;
                break;
            }
        }
        $isPaid = $found && ($found['payment_status'] ?? '') === 'paid';
        Response::success([
            'order_id' => $orderId,
            'payment_status' => $isPaid ? 'paid' : 'pending',
            'is_paid' => $isPaid
        ], 200);
    }

    /**
     * POST /api/v1/payments/webhook
     * Webhook nhận tín hiệu thanh toán tự động (từ ngân hàng/cổng thanh toán)
     */
    public static function webhook(array $body): void {
        $orderCode = $body['order_code'] ?? $body['content'] ?? '';
        if ($orderCode) {
            $db = self::getDb();
            foreach ($db['orders'] as &$ord) {
                if ($ord['order_code'] === $orderCode || str_contains($orderCode, (string)$ord['id'])) {
                    $ord['payment_status'] = 'paid';
                    $ord['status'] = 'confirmed';
                    self::saveDb($db);
                    break;
                }
            }
        }
        Response::success(['acknowledged' => true], 200, 'Webhook processed');
    }
}
