<?php
/**
 * TechStore - Coupon Controller
 * Endpoint: POST /coupons/validate
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class CouponController extends BaseController {
    public function validate(Request $request): void {
        $body = $request->body();
        $code = strtoupper(trim($body['code'] ?? ''));
        $orderTotal = (float)($body['order_total'] ?? 0);

        if (!$code) {
            Response::error('Vui lòng nhập mã giảm giá', 'MISSING_CODE', 400);
        }

        $coupon = $this->model->queryOne("SELECT * FROM coupons WHERE code = :code", ['code' => $code]);

        if (!$coupon) {
            Response::error('Mã giảm giá không tồn tại', 'COUPON_NOT_FOUND', 404);
        }

        if ($coupon['status'] !== 'active') {
            Response::error('Mã giảm giá đã hết hạn hoặc bị vô hiệu hóa', 'COUPON_EXPIRED', 400);
        }

        if ($orderTotal < (float)$coupon['min_order']) {
            $formattedMin = number_format($coupon['min_order'], 0, ',', '.') . 'đ';
            Response::error("Mã chỉ áp dụng cho đơn hàng từ {$formattedMin}", 'MIN_ORDER_NOT_MET', 400);
        }

        $discount = 0;
        if ($coupon['discount_type'] === 'percent') {
            $discount = $orderTotal * ((float)$coupon['value'] / 100);
            if ((float)$coupon['max_discount'] > 0 && $discount > (float)$coupon['max_discount']) {
                $discount = (float)$coupon['max_discount'];
            }
        } else {
            $discount = (float)$coupon['value'];
        }

        Response::json([
            'valid' => true,
            'code' => $coupon['code'],
            'discount_amount' => $discount,
            'final_total' => max(0, $orderTotal - $discount),
            'description' => $coupon['description']
        ], 200, true, 'Áp dụng mã giảm giá thành công');
    }
}
