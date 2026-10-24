<?php
/**
 * TechStore - Payment Model
 * Quản lý giao dịch thanh toán trực tiếp qua CSDL MySQL
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;

class PaymentModel extends BaseModel {
    /**
     * Tạo hoặc cập nhật thông tin thanh toán cho đơn hàng
     */
    public static function createOrUpdatePayment(int $orderId, array $data): array {
        $pdo = Database::getConnection();
        $check = $pdo->prepare("SELECT id FROM payments WHERE order_id = ? LIMIT 1");
        $check->execute([$orderId]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        $now = date('Y-m-d H:i:s');
        if ($existing) {
            $stmt = $pdo->prepare("UPDATE payments SET 
                payment_method = :payment_method,
                amount = :amount,
                status = :status,
                qr_code_url = :qr_code_url,
                payment_note = :payment_note
                WHERE id = :id");
            $stmt->execute([
                ':id'             => $existing['id'],
                ':payment_method' => $data['payment_method'] ?? 'vietqr',
                ':amount'         => (int)($data['amount'] ?? 0),
                ':status'         => $data['status'] ?? 'pending',
                ':qr_code_url'    => $data['qr_code_url'] ?? null,
                ':payment_note'   => $data['payment_note'] ?? null
            ]);
            $paymentId = (int)$existing['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO payments (
                order_id, payment_code, payment_method, amount, status, qr_code_url, payment_note, created_at
            ) VALUES (
                :order_id, :payment_code, :payment_method, :amount, :status, :qr_code_url, :payment_note, :created_at
            )");
            $stmt->execute([
                ':order_id'       => $orderId,
                ':payment_code'   => $data['payment_code'] ?? ('PAY-' . $orderId . '-' . time()),
                ':payment_method' => $data['payment_method'] ?? 'vietqr',
                ':amount'         => (int)($data['amount'] ?? 0),
                ':status'         => $data['status'] ?? 'pending',
                ':qr_code_url'    => $data['qr_code_url'] ?? null,
                ':payment_note'   => $data['payment_note'] ?? null,
                ':created_at'     => $now
            ]);
            $paymentId = (int)$pdo->lastInsertId();
        }

        return self::getByOrderId($orderId);
    }

    /**
     * Lấy thông tin thanh toán theo order_id
     */
    public static function getByOrderId(int $orderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Cập nhật trạng thái thanh toán và đồng bộ sang đơn hàng
     */
    public static function markAsPaid(int $orderId, ?string $transactionId = null): bool {
        $pdo = Database::getConnection();
        $now = date('Y-m-d H:i:s');

        // Cập nhật bảng payments
        $stmtPay = $pdo->prepare("UPDATE payments SET status = 'completed', transaction_id = ?, paid_at = ? WHERE order_id = ?");
        $stmtPay->execute([$transactionId, $now, $orderId]);

        // Cập nhật bảng orders
        $stmtOrd = $pdo->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing' WHERE id = ?");
        return $stmtOrd->execute([$orderId]);
    }
}
