<?php
/**
 * TechStore - Order Model (Clean Architecture)
 * Quản lý đơn hàng và chi tiết đơn hàng trực tiếp qua CSDL MySQL với ACID Transaction
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/ProductModel.php';

use Config\Database;
use PDO;
use Exception;

class OrderModel extends BaseModel {
    /**
     * Tạo đơn hàng mới với Transaction bảo đảm toàn vẹn dữ liệu
     */
    public static function createOrder(array $orderData, array $items): array {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $orderCode = $orderData['order_code'] ?? ('TS-' . rand(100000, 999999));
            $now = date('Y-m-d H:i:s');

            $sqlOrder = "INSERT INTO orders (
                order_code, user_id, customer_name, customer_phone, customer_email,
                shipping_address, payment_method, payment_status, status,
                subtotal, discount_amount, shipping_fee, total_amount, coupon_code, note, created_at
            ) VALUES (
                :order_code, :user_id, :customer_name, :customer_phone, :customer_email,
                :shipping_address, :payment_method, :payment_status, :status,
                :subtotal, :discount_amount, :shipping_fee, :total_amount, :coupon_code, :note, :created_at
            )";

            $stmtOrder = $pdo->prepare($sqlOrder);
            $stmtOrder->execute([
                ':order_code'       => $orderCode,
                ':user_id'          => $orderData['user_id'] ?? null,
                ':customer_name'    => $orderData['customer_name'] ?? 'Khách hàng',
                ':customer_phone'   => $orderData['customer_phone'] ?? '',
                ':customer_email'   => $orderData['customer_email'] ?? '',
                ':shipping_address' => $orderData['shipping_address'] ?? 'TP.HCM',
                ':payment_method'   => $orderData['payment_method'] ?? 'vietqr',
                ':payment_status'   => $orderData['payment_status'] ?? 'pending',
                ':status'           => $orderData['status'] ?? 'pending',
                ':subtotal'         => (int)($orderData['subtotal'] ?? $orderData['total_amount'] ?? 0),
                ':discount_amount'  => (int)($orderData['discount_amount'] ?? 0),
                ':shipping_fee'     => (int)($orderData['shipping_fee'] ?? 0),
                ':total_amount'     => (int)($orderData['total_amount'] ?? 0),
                ':coupon_code'      => $orderData['coupon_code'] ?? null,
                ':note'             => $orderData['note'] ?? '',
                ':created_at'       => $now
            ]);

            $orderId = (int)$pdo->lastInsertId();

            // Insert Order Items và trừ tồn kho
            $sqlItem = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, variant, image, created_at)
                        VALUES (:order_id, :product_id, :product_name, :price, :quantity, :variant, :image, :created_at)";
            $stmtItem = $pdo->prepare($sqlItem);

            $sqlStock = "UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?";
            $stmtStock = $pdo->prepare($sqlStock);

            foreach ($items as $item) {
                $prodId = (int)($item['product_id'] ?? $item['id'] ?? 0);
                $qty = max(1, (int)($item['quantity'] ?? 1));
                $price = (int)($item['price'] ?? 0);
                $name = $item['name'] ?? $item['product_name'] ?? 'Sản phẩm #' . $prodId;
                $variant = $item['variant'] ?? null;
                $image = $item['image'] ?? null;

                $stmtItem->execute([
                    ':order_id'     => $orderId,
                    ':product_id'   => $prodId,
                    ':product_name' => $name,
                    ':price'        => $price,
                    ':quantity'     => $qty,
                    ':variant'      => $variant,
                    ':image'        => $image,
                    ':created_at'   => $now
                ]);

                if ($prodId > 0) {
                    $stmtStock->execute([$qty, $prodId]);
                }
            }

            // Xóa giỏ hàng nếu user đăng nhập
            if (!empty($orderData['user_id'])) {
                $stmtClearCart = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
                $stmtClearCart->execute([(int)$orderData['user_id']]);
            }

            $pdo->commit();

            return self::getOrderById($orderId);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Lấy danh sách toàn bộ đơn hàng (hoặc theo user)
     */
    public static function getAll(?int $userId = null): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM orders";
        $params = [];
        if ($userId !== null) {
            $sql .= " WHERE user_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kèm danh sách items cho mỗi đơn
        foreach ($orders as &$order) {
            $order['items'] = self::getOrderItems((int)$order['id']);
        }

        return $orders;
    }

    /**
     * Lấy thông tin chi tiết một đơn hàng theo ID
     */
    public static function getOrderById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) return null;

        $order['items'] = self::getOrderItems($id);
        return $order;
    }

    /**
     * Lấy chi tiết đơn hàng theo order_code
     */
    public static function getOrderByCode(string $code): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
        $stmt->execute([$code]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) return null;

        $order['items'] = self::getOrderItems((int)$order['id']);
        return $order;
    }

    /**
     * Lấy danh sách order_items của đơn hàng
     */
    public static function getOrderItems(int $orderId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hủy đơn hàng và hoàn lại tồn kho
     */
    public static function cancelOrder(int $id): bool {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                $pdo->rollBack();
                return false;
            }

            if ($order['status'] !== 'pending') {
                $pdo->rollBack();
                return false;
            }

            // Hoàn tồn kho
            $items = self::getOrderItems($id);
            $stmtRestock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $stmtRestock->execute([(int)$item['quantity'], (int)$item['product_id']]);
                }
            }

            $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
            $stmtUpdate->execute([$id]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng và thanh toán
     */
    public static function updateStatus(int $id, string $status, ?string $paymentStatus = null): bool {
        $pdo = Database::getConnection();
        $fields = ["status = ?"];
        $params = [$status];

        if ($paymentStatus !== null) {
            $fields[] = "payment_status = ?";
            $params[] = $paymentStatus;
        }

        $params[] = $id;
        $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
