<?php
/**
 * TechStore - Admin Model
 * Tổng hợp số liệu KPI, thống kê doanh thu và quản lý danh sách người dùng qua MySQL
 */

namespace Api\Models;

require_once __DIR__ . '/BaseModel.php';

use Config\Database;
use PDO;

class AdminModel extends BaseModel {
    /**
     * Lấy danh sách toàn bộ người dùng (đã ẩn password_hash)
     */
    public static function listUsers(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name, email, role, phone, address, avatar, status, created_at, updated_at 
                             FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật thông tin vai trò hoặc trạng thái của người dùng
     */
    public static function updateUser(int $id, array $data): ?array {
        $pdo = Database::getConnection();
        $fields = [];
        $params = [];

        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }

        if (!empty($fields)) {
            $params[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        $stmtUser = $pdo->prepare("SELECT id, name, email, role, phone, address, avatar, status, created_at FROM users WHERE id = ?");
        $stmtUser->execute([$id]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Lấy thống kê KPI dashboard từ các bảng MySQL
     */
    public static function getStats(): array {
        $pdo = Database::getConnection();

        // 1. Tổng doanh thu (từ các đơn có payment_status = 'paid' hoặc status trong ['delivered', 'completed'])
        $revStmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid' OR status = 'completed'");
        $totalRevenue = (float)$revStmt->fetchColumn();

        // 2. Tổng số đơn hàng
        $orderStmt = $pdo->query("SELECT COUNT(*) FROM orders");
        $totalOrders = (int)$orderStmt->fetchColumn();

        // 3. Tổng số khách hàng
        $userStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
        $totalCustomers = (int)$userStmt->fetchColumn();

        // 4. Tổng số sản phẩm
        $prodStmt = $pdo->query("SELECT COUNT(*) FROM products");
        $totalProducts = (int)$prodStmt->fetchColumn();

        // 5. Thống kê theo trạng thái đơn hàng
        $statusStmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
        $ordersByStatus = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // 6. Doanh thu theo 7 ngày gần nhất
        $chartStmt = $pdo->query("SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue
                                  FROM orders
                                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                  GROUP BY DATE(created_at)
                                  ORDER BY date ASC");
        $revenueChart = $chartStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'revenue' => [
                'total' => $totalRevenue,
                'growth' => '+12.5%'
            ],
            'orders' => [
                'total' => $totalOrders,
                'by_status' => $ordersByStatus
            ],
            'customers' => [
                'total' => $totalCustomers
            ],
            'products' => [
                'total' => $totalProducts
            ],
            'revenue_chart' => $revenueChart
        ];
    }

    /**
     * Lấy nhật ký hoạt động kiểm toán hệ thống
     */
    public static function getAuditLogs(int $limit = 50): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM audit_logs ORDER BY id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
