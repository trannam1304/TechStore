<?php
/**
 * TechStore - Coupon Controller
 * Quản lý mã giảm giá: kiểm tra tính hợp lệ và danh sách mã khuyến mãi thuần MySQL
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/CouponModel.php';

use Api\Helpers\Response;
use Api\Models\CouponModel;

class CouponController extends BaseController {
    /**
     * POST /api/v1/coupons/validate
     * Xác thực và tính toán số tiền giảm giá của coupon
     */
    public static function validateCoupon(array $body): void {
        $code = strtoupper(trim($body['code'] ?? ''));
        $total = (float)($body['total'] ?? 0);

        if (!$code) {
            Response::error('Vui lòng nhập mã giảm giá', 'EMPTY_CODE', 422);
            return;
        }

        $found = CouponModel::findByCode($code);
        if (!$found || ($found['status'] ?? '') !== 'active') {
            Response::error('Mã giảm giá không hợp lệ hoặc đã hết hạn', 'COUPON_NOT_FOUND', 404);
            return;
        }

        if ($found['usage_limit'] > 0 && $found['used_count'] >= $found['usage_limit']) {
            Response::error('Mã giảm giá đã hết lượt sử dụng', 'COUPON_LIMIT_REACHED', 400);
            return;
        }

        $minOrder = (float)($found['min_order'] ?? 0);
        if ($total < $minOrder) {
            Response::error("Đơn hàng tối thiểu " . number_format($minOrder) . "đ để áp dụng mã này", 'MIN_ORDER_NOT_MET', 400);
            return;
        }

        $val = (float)$found['value'];
        $maxDiscount = (float)($found['max_discount'] ?? 0);

        if ($found['discount_type'] === 'percent') {
            $discount = ($total * $val) / 100;
            if ($maxDiscount > 0) {
                $discount = min($maxDiscount, $discount);
            }
        } else {
            $discount = $val;
        }

        $discount = min($discount, $total);

        Response::success([
            'code'            => $found['code'],
            'discount'        => round($discount),
            'discount_type'   => $found['discount_type'],
            'value'           => $val,
            'description'     => $found['description'] ?? '',
            'final_total'     => max(0, round($total - $discount))
        ], 200, 'Áp dụng mã giảm giá thành công');
    }

    /**
     * GET /api/v1/coupons
     * Lấy danh sách mã giảm giá đang kích hoạt
     */
    public static function listCoupons(): void {
        $all = CouponModel::getAll();
        $active = array_values(array_filter($all, fn($c) => ($c['status'] ?? '') === 'active'));
        Response::success($active, 200);
    }
}
