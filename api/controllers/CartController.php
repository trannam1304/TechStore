<?php
/**
 * TechStore - Cart Controller (4 APIs)
 * Endpoints: GET /cart, POST /cart/items, PUT /cart/items/{id}, DELETE /cart/items/{id}
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class CartController extends BaseController {
    public function index(Request $request): void {
        $user = $this->requireAuthUser($request);
        $userId = (int)$user['sub'];

        $sql = "SELECT c.id, c.product_id, c.quantity, c.variant,
                       p.name, p.price, p.image, p.stock
                FROM cart c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = :uid";

        $items = $this->model->query($sql, ['uid' => $userId]);

        $subtotal = 0;
        foreach ($items as &$item) {
            $item['total'] = (float)$item['price'] * (int)$item['quantity'];
            $subtotal += $item['total'];
        }

        Response::json([
            'items' => $items,
            'total_items' => count($items),
            'subtotal' => $subtotal
        ]);
    }

    public function store(Request $request): void {
        $user = $this->requireAuthUser($request);
        $userId = (int)$user['sub'];
        $body = $request->body();

        $productId = (int)($body['product_id'] ?? 0);
        $quantity = max(1, (int)($body['quantity'] ?? 1));
        $variant = $body['variant'] ?? 'Mặc định';

        if (!$productId) {
            Response::error('Thiếu mã sản phẩm', 'VALIDATION_ERROR', 422);
        }

        $product = $this->model->queryOne("SELECT id, name, price, stock FROM products WHERE id = :id", ['id' => $productId]);
        if (!$product) {
            Response::error('Sản phẩm không tồn tại', 'NOT_FOUND', 404);
        }

        // Check if item already exists in cart
        $existing = $this->model->queryOne(
            "SELECT id, quantity FROM cart WHERE user_id = :uid AND product_id = :pid AND variant = :var",
            ['uid' => $userId, 'pid' => $productId, 'var' => $variant]
        );

        if ($existing) {
            $newQty = (int)$existing['quantity'] + $quantity;
            $this->model->execute(
                "UPDATE cart SET quantity = :qty WHERE id = :id",
                ['qty' => $newQty, 'id' => $existing['id']]
            );
        } else {
            $this->model->execute(
                "INSERT INTO cart (user_id, product_id, quantity, variant) VALUES (:uid, :pid, :qty, :var)",
                ['uid' => $userId, 'pid' => $productId, 'qty' => $quantity, 'var' => $variant]
            );
        }

        Response::json(['message' => 'Đã thêm sản phẩm vào giỏ hàng'], 201, true, 'Thêm vào giỏ thành công');
    }

    public function update(Request $request): void {
        $user = $this->requireAuthUser($request);
        $itemId = (int)$request->param('id');
        $body = $request->body();
        $quantity = max(1, (int)($body['quantity'] ?? 1));

        $this->model->execute(
            "UPDATE cart SET quantity = :qty WHERE id = :id AND user_id = :uid",
            ['qty' => $quantity, 'id' => $itemId, 'uid' => $user['sub']]
        );

        Response::json(['message' => 'Đã cập nhật số lượng'], 200, true, 'Cập nhật thành công');
    }

    public function destroy(Request $request): void {
        $user = $this->requireAuthUser($request);
        $itemId = (int)$request->param('id');

        $this->model->execute(
            "DELETE FROM cart WHERE id = :id AND user_id = :uid",
            ['id' => $itemId, 'uid' => $user['sub']]
        );

        Response::json(null, 204, true, 'Đã xóa sản phẩm khỏi giỏ hàng');
    }
}
