<?php
/**
 * TechStore - Admin Dashboard & Management Controller
 * Endpoints: GET /admin/stats, GET /admin/audit-logs, GET/PATCH /admin/users, CRUD /admin/coupons
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class AdminController extends BaseController {
    /**
     * GET /admin/stats
     * Thống kê KPI doanh thu, đơn hàng, khách hàng và biểu đồ 7 ngày
     */
    public function stats(Request $request): void {
        $revenueRow = $this->model->queryOne("SELECT COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE status != 'cancelled'");
        $totalRevenue = (float)($revenueRow['total_revenue'] ?? 0);

        $orderCountRow = $this->model->queryOne("SELECT COUNT(*) as total_orders FROM orders");
        $totalOrders = (int)($orderCountRow['total_orders'] ?? 0);

        $productCountRow = $this->model->queryOne("SELECT COUNT(*) as total_products FROM products");
        $totalProducts = (int)($productCountRow['total_products'] ?? 0);

        $userCountRow = $this->model->queryOne("SELECT COUNT(*) as total_users FROM users WHERE role = 'customer'");
        $totalUsers = (int)($userCountRow['total_users'] ?? 0);

        // Biểu đồ doanh thu 7 ngày
        $sevenDays = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dayRevenue = $this->model->queryOne(
                "SELECT COALESCE(SUM(total_amount), 0) as rev FROM orders WHERE DATE(created_at) = :d AND status != 'cancelled'",
                ['d' => $day]
            );
            $sevenDays[] = [
                'date' => date('d/m', strtotime($day)),
                'revenue' => (float)($dayRevenue['rev'] ?? 0)
            ];
        }

        $recentOrders = $this->model->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");

        Response::json([
            'kpi' => [
                'total_revenue' => $totalRevenue,
                'growth_rate' => '+14.2%',
                'total_orders' => $totalOrders,
                'total_products' => $totalProducts,
                'total_users' => $totalUsers
            ],
            'revenue_7days' => $sevenDays,
            'recent_orders' => $recentOrders
        ]);
    }

    /**
     * GET /admin/audit-logs
     */
    public function auditLogs(Request $request): void {
        $logs = $this->model->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 50");
        Response::json($logs);
    }

    /**
     * GET /admin/users
     */
    public function listUsers(Request $request): void {
        $users = $this->model->query("SELECT id, name, email, role, phone, address, status, created_at FROM users ORDER BY id ASC");
        Response::json($users);
    }

    /**
     * PATCH /admin/users/{id}
     */
    public function patchUser(Request $request): void {
        $id = (int)$request->param('id');
        $body = $request->body();

        $user = $this->model->queryOne("SELECT id FROM users WHERE id = :id", ['id' => $id]);
        if (!$user) {
            Response::error('Không tìm thấy người dùng', 'NOT_FOUND', 404);
        }

        $fields = [];
        $params = ['id' => $id];

        if (isset($body['status'])) {
            $fields[] = "status = :st";
            $params['st'] = $body['status'];
        }

        if (isset($body['role'])) {
            $fields[] = "role = :rl";
            $params['rl'] = $body['role'];
        }

        if (!empty($fields)) {
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $this->model->execute($sql, $params);
        }

        $updated = $this->model->queryOne("SELECT id, name, email, role, phone, address, status FROM users WHERE id = :id", ['id' => $id]);
        Response::json($updated, 200, true, 'Cập nhật tài khoản thành công');
    }

    /**
     * GET /admin/coupons
     */
    public function listCoupons(Request $request): void {
        $coupons = $this->model->query("SELECT * FROM coupons ORDER BY id DESC");
        Response::json($coupons);
    }

    /**
     * POST /admin/coupons
     */
    public function storeCoupon(Request $request): void {
        $body = $request->body();
        $code = strtoupper(trim($body['code'] ?? ''));

        if (!$code) {
            Response::error('Mã giảm giá không được để trống', 'VALIDATION_ERROR', 422);
        }

        $this->model->execute(
            "INSERT INTO coupons (code, discount_type, value, min_order, max_discount, description, status, created_at)
             VALUES (:code, :type, :val, :min, :max, :desc, 'active', NOW())",
            [
                'code' => $code,
                'type' => $body['discount_type'] ?? 'percent',
                'val' => (float)($body['value'] ?? 10),
                'min' => (float)($body['min_order'] ?? 0),
                'max' => (float)($body['max_discount'] ?? 0),
                'desc' => $body['description'] ?? ''
            ]
        );

        $newId = $this->model->lastInsertId();
        $coupon = $this->model->queryOne("SELECT * FROM coupons WHERE id = :id", ['id' => $newId]);
        Response::json($coupon, 201, true, 'Thêm mã giảm giá thành công');
    }

    /**
     * DELETE /admin/coupons/{id}
     */
    public function deleteCoupon(Request $request): void {
        $id = (int)$request->param('id');
        $this->model->execute("DELETE FROM coupons WHERE id = :id", ['id' => $id]);
        Response::json(null, 204, true, 'Đã xóa mã giảm giá');
    }
}
