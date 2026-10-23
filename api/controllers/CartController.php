<?php
/**
 * TechStore - Cart Controller
 * Quản lý giỏ hàng: xem giỏ hàng, thêm sản phẩm, cập nhật số lượng, xóa sản phẩm
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;

class CartController extends BaseController {
    /**
     * GET /api/v1/cart
     * Lấy thông tin giỏ hàng hiện tại kèm tổng tiền
     */
    public static function getCart(): void {
        $db = self::getDb();
        $cart = $db['cart'] ?? ['items' => [], 'total' => 0];
        $cart['total'] = array_reduce($cart['items'] ?? [], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
        Response::success($cart, 200);
    }

    /**
     * POST /api/v1/cart/items
     * Thêm sản phẩm vào giỏ hàng
     */
    public static function addItem(array $body): void {
        $db = self::getDb();
        $prodId = (int)($body['product_id'] ?? 0);
        $qty = max(1, (int)($body['quantity'] ?? 1));
        $variant = $body['variant'] ?? 'Mặc định';

        $prod = null;
        foreach ($db['products'] as $p) {
            if ($p['id'] === $prodId) {
                $prod = $p;
                break;
            }
        }
        if (!$prod) {
            Response::error('Không tìm thấy sản phẩm để thêm vào giỏ', 'NOT_FOUND', 404);
        }

        if (!isset($db['cart']['items'])) {
            $db['cart']['items'] = [];
        }

        $existing = false;
        foreach ($db['cart']['items'] as &$item) {
            if ($item['product_id'] === $prodId && ($item['variant'] ?? '') === $variant) {
                $item['quantity'] += $qty;
                $existing = true;
                break;
            }
        }
        if (!$existing) {
            $db['cart']['items'][] = [
                'id' => time() + rand(1, 999),
                'product_id' => $prodId,
                'name' => $prod['name'],
                'price' => $prod['price'],
                'quantity' => $qty,
                'variant' => $variant,
                'image' => $prod['image']
            ];
        }

        $db['cart']['total'] = array_reduce($db['cart']['items'], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
        self::saveDb($db);
        Response::success($db['cart'], 201, 'Đã thêm vào giỏ hàng');
    }

    /**
     * PUT /api/v1/cart/items/{id}
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public static function updateItem(int $itemId, array $body): void {
        $db = self::getDb();
        $qty = max(1, (int)($body['quantity'] ?? 1));
        foreach ($db['cart']['items'] as &$item) {
            if ($item['id'] === $itemId || $item['product_id'] === $itemId) {
                $item['quantity'] = $qty;
                break;
            }
        }
        $db['cart']['total'] = array_reduce($db['cart']['items'], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
        self::saveDb($db);
        Response::success($db['cart'], 200, 'Đã cập nhật số lượng');
    }

    /**
     * DELETE /api/v1/cart/items/{id}
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public static function removeItem(int $itemId): void {
        $db = self::getDb();
        $db['cart']['items'] = array_values(array_filter($db['cart']['items'] ?? [], fn($i) => $i['id'] !== $itemId && $i['product_id'] !== $itemId));
        $db['cart']['total'] = array_reduce($db['cart']['items'], fn($s, $i) => $s + ($i['price'] * $i['quantity']), 0);
        self::saveDb($db);
        Response::success(null, 204);
    }
}
