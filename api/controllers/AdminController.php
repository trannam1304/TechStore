<?php
/**
 * TechStore - Admin Controller
 * Quản trị hệ thống: thống kê KPI dashboard, nhật ký thao tác (Audit Logs), quản lý người dùng
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';

use Api\Helpers\Response;

class AdminController extends BaseController {
    /**
     * GET /api/v1/admin/users
     * Lấy danh sách người dùng trong hệ thống (đã bảo mật ẩn password_hash)
     */
    public static function listUsers(): void {
        $db = self::getDb();
        $users = array_map(function($u) {
            unset($u['password_hash']);
            return $u;
        }, $db['users'] ?? []);
        Response::success(array_values($users), 200);
    }

    /**
     * PATCH /api/v1/admin/users/{id}
     * Cập nhật trạng thái (active/blocked) hoặc phân quyền (admin/customer) người dùng
     */
    public static function patchUser(int $id, array $body): void {
        $db = self::getDb();
        foreach ($db['users'] as &$u) {
            if ((int)$u['id'] === $id) {
                if (isset($body['status'])) $u['status'] = $body['status'];
                if (isset($body['role'])) $u['role'] = $body['role'];
                self::saveDb($db);
                unset($u['password_hash']);
                Response::success($u, 200, 'Cập nhật người dùng thành công');
            }
        }
        Response::error('Không tìm thấy người dùng', 'NOT_FOUND', 404);
    }

    /**
     * GET /api/v1/admin/stats
     * Báo cáo tổng quan KPI doanh thu, đơn hàng, khách hàng và biểu đồ doanh thu tuần
     */
    public static function stats(): void {
        $db = self::getDb();
        $orders = $db['orders'] ?? [];
        $totalRevenue = array_reduce($orders, fn($s, $o) => $s + (($o['payment_status'] ?? '') === 'paid' ? ($o['total_amount'] ?? 0) : 0), 184500000);

        Response::success([
            'total_revenue' => $totalRevenue,
            'revenue_growth' => '+14.8%',
            'new_orders_count' => count($orders),
            'total_products' => count($db['products'] ?? []),
            'total_users' => count($db['users'] ?? []),
            'recent_orders' => array_slice($orders, 0, 5),
            'revenue_chart' => [
                ['day' => 'T2', 'amount' => 18500000],
                ['day' => 'T3', 'amount' => 22400000],
                ['day' => 'T4', 'amount' => 19800000],
                ['day' => 'T5', 'amount' => 31200000],
                ['day' => 'T6', 'amount' => 28900000],
                ['day' => 'T7', 'amount' => 45000000],
                ['day' => 'CN', 'amount' => 38700000]
            ]
        ], 200);
    }

    /**
     * GET /api/v1/admin/audit-logs
     * Danh sách lịch sử thao tác hệ thống
     */
    public static function auditLogs(): void {
        $db = self::getDb();
        Response::success($db['audit_logs'] ?? [], 200);
    }
}
