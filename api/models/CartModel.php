<?php
/**
 * TechStore - Cart Model (Clean MVC)
 * Quản lý giỏ hàng phía máy chủ và kiểm soát tồn kho thời gian thực (FR04 / BR04)
 * Tương tác trực tiếp với bảng vật lý `cart` và kiểm tra tồn kho từ `products`
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/ProductModel.php';

use Config\Database;
use PDO;
use Exception;

class CartModel extends BaseModel {
    private static ?string $tableName = null;

    /**
     * Tự động nhận diện tên bảng giỏ hàng (ưu tiên chuẩn số nhiều `carts`, fallback số ít `cart`)
     */
    public static function getTableName(): string {
        if (self::$tableName === null) {
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->query("SHOW TABLES LIKE 'carts'");
                self::$tableName = ($stmt && $stmt->fetch()) ? 'carts' : 'cart';
            } catch (\Throwable $e) {
                self::$tableName = 'carts';
            }
        }
        return self::$tableName;
    }

    /**
     * Lấy thông tin giỏ hàng của người dùng kèm chi tiết sản phẩm, đối chiếu giá và tồn kho
     * FR04.01: Trả về { items, total_items, subtotal, total }
     */
    public static function getCart(int $userId): array {
        try {
            $pdo = Database::getConnection();
        } catch (\Throwable $e) {
            $pdo = null;
        }

        if (!$pdo) {
            return [
                'items' => [],
                'total_items' => 0,
                'subtotal' => 0,
                'total' => 0
            ];
        }

        try {
            $table = self::getTableName();
            $sql = "SELECT 
                        c.id,
                        c.user_id,
                        c.product_id,
                        c.quantity,
                        c.variant,
                        c.created_at,
                        p.name AS product_name,
                        p.name,
                        p.price,
                        p.stock,
                        p.image
                    FROM {$table} c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.user_id = ?
                    ORDER BY c.created_at DESC, c.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $items = [];
            $totalItems = 0;
            $subtotal = 0;

            foreach ($rows as $row) {
                $qty = (int)$row['quantity'];
                $price = (float)$row['price'];
                $stock = (int)($row['stock'] ?? 0);
                $lineTotal = $price * $qty;

                $items[] = [
                    'id' => (int)$row['id'],
                    'user_id' => (int)$row['user_id'],
                    'product_id' => (int)$row['product_id'],
                    'product_name' => $row['product_name'] ?? $row['name'],
                    'name' => $row['name'] ?? $row['product_name'],
                    'price' => $price,
                    'quantity' => $qty,
                    'variant' => !empty($row['variant']) ? $row['variant'] : 'Mặc định',
                    'image' => $row['image'] ?? '',
                    'stock' => $stock,
                    'status' => ($stock > 0 ? 'active' : 'out_of_stock'),
                    'line_total' => $lineTotal
                ];

                $totalItems += $qty;
                $subtotal += $lineTotal;
            }

            return [
                'items'          => $items,
                'total_items'    => $totalItems,
                'total_quantity' => $totalItems,
                'subtotal'       => $subtotal,
                'total_price'    => $subtotal,
                'total'          => $subtotal
            ];
        } catch (\Throwable $e) {
            return [
                'items'          => [],
                'total_items'    => 0,
                'total_quantity' => 0,
                'subtotal'       => 0,
                'total_price'    => 0,
                'total'          => 0
            ];
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     * FR04.02 / BR04: Kiểm tra nghiêm ngặt không cho phép tổng số lượng vượt quá tồn kho `stock`
     */
    public static function addItem(int $userId, int $productId, int $quantity = 1, ?string $variant = null): array {
        $pdo = Database::getConnection();

        // 1. Kiểm tra sản phẩm tồn tại
        $stmt = $pdo->prepare("SELECT id, name, price, stock, image FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prod) {
            throw new Exception("Sản phẩm không tồn tại (ID: {$productId})", 404);
        }

        // 2. Kiểm tra trạng thái tồn kho
        $stock = (int)$prod['stock'];
        if ($stock <= 0) {
            throw new Exception("Sản phẩm '{$prod['name']}' hiện đã hết hàng (tồn kho: 0)", 400);
        }

        // 3. Chuẩn hóa biến thể
        $cleanVariant = trim((string)$variant);
        if ($cleanVariant === '') {
            $cleanVariant = 'Mặc định';
        }

        // 4. Tìm kiếm mặt hàng đã tồn tại trong giỏ của user
        $table = self::getTableName();
        $findStmt = $pdo->prepare("SELECT id, quantity FROM {$table} WHERE user_id = ? AND product_id = ? AND (variant = ? OR (variant IS NULL AND ? = 'Mặc định')) LIMIT 1");
        $findStmt->execute([$userId, $productId, $cleanVariant, $cleanVariant]);
        $existing = $findStmt->fetch(PDO::FETCH_ASSOC);

        $targetQty = $existing ? ((int)$existing['quantity'] + $quantity) : $quantity;

        // 5. Kiểm tra ràng buộc tồn kho nghiêm ngặt BR04
        if ($targetQty > $stock) {
            throw new Exception(
                "Số lượng yêu cầu ({$targetQty}) vượt quá số lượng tồn kho khả dụng ({$stock}) của sản phẩm '{$prod['name']}'.",
                400
            );
        }

        // 6. Cập nhật hoặc thêm mới bản ghi vào bảng cart/carts
        if ($existing) {
            $upStmt = $pdo->prepare("UPDATE {$table} SET quantity = ?, created_at = NOW() WHERE id = ?");
            $upStmt->execute([$targetQty, $existing['id']]);
        } else {
            $insStmt = $pdo->prepare("INSERT INTO {$table} (user_id, product_id, quantity, variant, created_at) VALUES (?, ?, ?, ?, NOW())");
            $insStmt->execute([$userId, $productId, $quantity, $cleanVariant]);
        }

        return self::getCart($userId);
    }

    /**
     * Cập nhật số lượng món đồ trong giỏ
     * FR04.03 / BR04:
     * - Nếu quantity <= 0: tự động xóa sản phẩm khỏi giỏ hàng
     * - Nếu quantity > stock: từ chối 400 Bad Request
     */
    public static function updateItem(int $userId, int $itemId, int $quantity): array {
        $pdo = Database::getConnection();
        $table = self::getTableName();

        // 1. Tìm mục trong giỏ
        $findStmt = $pdo->prepare("SELECT c.id, c.user_id, c.product_id, c.quantity, c.variant, p.name AS product_name, p.stock 
                                   FROM {$table} c 
                                   JOIN products p ON c.product_id = p.id 
                                   WHERE (c.id = ? OR c.product_id = ?) AND c.user_id = ? 
                                   LIMIT 1");
        $findStmt->execute([$itemId, $itemId, $userId]);
        $item = $findStmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception("Không tìm thấy mặt hàng trong giỏ hàng", 404);
        }

        // 2. Nếu số lượng <= 0: Tự động xóa khỏi giỏ
        if ($quantity <= 0) {
            $delStmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
            $delStmt->execute([$item['id']]);
            return self::getCart($userId);
        }

        // 3. Kiểm tra tồn kho
        $stock = (int)$item['stock'];
        if ($quantity > $stock) {
            throw new Exception(
                "Số lượng cập nhật ({$quantity}) vượt quá tồn kho khả dụng ({$stock}) của sản phẩm '{$item['product_name']}'.",
                400
            );
        }

        // 4. Cập nhật số lượng
        $upStmt = $pdo->prepare("UPDATE {$table} SET quantity = ? WHERE id = ?");
        $upStmt->execute([$quantity, $item['id']]);

        return self::getCart($userId);
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng
     * FR04.04
     */
    public static function removeItem(int $userId, int $itemId): array {
        $pdo = Database::getConnection();
        $table = self::getTableName();
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE (id = ? OR product_id = ?) AND user_id = ?");
        $stmt->execute([$itemId, $itemId, $userId]);

        return self::getCart($userId);
    }

    /**
     * Làm trống toàn bộ giỏ hàng của người dùng
     */
    public static function clear(int $userId): bool {
        try {
            $pdo = Database::getConnection();
            $table = self::getTableName();
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
