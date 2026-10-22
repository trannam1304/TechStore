<?php
/**
 * TechStore - Coupon Controller
 * Quản lý mã giảm giá: kiểm tra tính hợp lệ và danh sách mã khuyến mãi
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;

class CouponController extends BaseController {
    /**
     * POST /api/v1/coupons/validate
     * Xác thực và tính toán số tiền giảm giá của coupon
     */
    public static function validateCoupon(array $body): void {
        $code = strtoupper(trim($body['code'] ?? ''));
        $total = (float)($body['total'] ?? 0);

        $db = self::getDb();
        $found = null;
        foreach ($db['coupons'] as $c) {
            if (strtoupper($c['code']) === $code) {
                $found = $c;
                break;
            }
        }
        if (!$found) {
            Response::error('Mã giảm giá không hợp lệ hoặc đã hết hạn', 'COUPON_NOT_FOUND', 404);
        }
        if ($total < $found['min_order']) {
            Response::error("Đơn hàng tối thiểu " . number_format($found['min_order']) . "đ để áp dụng mã này", 'MIN_ORDER_NOT_MET', 400);
        }

        $discount = $found['discount_type'] === 'percent'
            ? min($found['max_discount'], ($total * $found['value']) / 100)
            : $found['value'];

        Response::success([
            'code' => $found['code'],
            'discount_amount' => $discount,
            'final_total' => max(0, $total - $discount),
            'description' => $found['description']
        ], 200, 'Áp dụng mã giảm giá thành công');
    }

    /**
     * GET /api/v1/coupons
     * Lấy danh sách các mã giảm giá
     */
    public static function getCoupons(): void {
        $db = self::getDb();
        Response::success($db['coupons'] ?? [], 200);
    }
}
