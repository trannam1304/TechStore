<?php
/**
 * TechStore - Admin Controller
 * Quản trị hệ thống: thống kê KPI dashboard, quản lý người dùng và audit logs thuần MySQL
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/AdminModel.php';

use Api\Helpers\Response;
use Api\Models\AdminModel;
use Exception;

class AdminController extends BaseController {
    /**
     * GET /api/v1/admin/users
     * Lấy danh sách người dùng trong hệ thống (đã bảo mật ẩn password_hash)
     */
    public static function listUsers(): void {
        try {
            $users = AdminModel::listUsers();
            Response::success($users, 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi lấy danh sách người dùng: ' . $e->getMessage(), 'USER_FETCH_ERROR', 500);
        }
    }

    /**
     * PATCH /api/v1/admin/users/{id}
     * Cập nhật trạng thái (active/banned) hoặc phân quyền (admin/customer) người dùng
     */
    public static function patchUser(int $id, array $body): void {
        try {
            $updated = AdminModel::updateUser($id, $body);
            if (!$updated) {
                Response::error('Không tìm thấy người dùng', 'NOT_FOUND', 404);
                return;
            }
            Response::success($updated, 200, 'Cập nhật người dùng thành công');
        } catch (Exception $e) {
            Response::error('Lỗi khi cập nhật người dùng: ' . $e->getMessage(), 'USER_UPDATE_ERROR', 500);
        }
    }

    /**
     * GET /api/v1/admin/stats
     * Báo cáo tổng quan KPI doanh thu, đơn hàng, khách hàng và biểu đồ doanh thu thuần MySQL
     */
    public static function stats(): void {
        try {
            $stats = AdminModel::getStats();
            Response::success($stats, 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi tải thống kê: ' . $e->getMessage(), 'STATS_ERROR', 500);
        }
    }

    /**
     * GET /api/v1/admin/audit-logs
     * Lấy nhật ký hoạt động kiểm toán hệ thống
     */
    public static function auditLogs(): void {
        try {
            $logs = AdminModel::getAuditLogs();
            Response::success($logs, 200);
        } catch (Exception $e) {
            Response::error('Lỗi khi tải nhật ký hoạt động: ' . $e->getMessage(), 'AUDIT_LOG_ERROR', 500);
        }
    }
}
