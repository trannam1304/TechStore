<?php
/**
 * TechStore - Role-based Access Control Middleware (RBAC)
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T10: JwtService (HS256 ký/xác thực, exp), AuthMiddleware, RoleMiddleware
 */

namespace Api\Middleware;

use Api\Helpers\Response;

class RoleMiddleware {
    /**
     * Kiểm tra vai trò của người dùng (customer hoặc admin)
     *
     * @param array $user Thông tin người dùng từ AuthMiddleware
     * @param string|array $requiredRole Vai trò yêu cầu (ví dụ: 'admin' hoặc ['admin'])
     */
    public static function check(array $user, string|array $requiredRole): void {
        $allowed = is_array($requiredRole) ? $requiredRole : [$requiredRole];
        $userRole = $user['role'] ?? 'customer';

        if (!in_array($userRole, $allowed, true)) {
            Response::error(
                'Truy cập bị từ chối: Tài khoản của bạn không có đủ quyền thực hiện thao tác này.',
                'FORBIDDEN',
                403
            );
        }
    }

    /**
     * Helper yêu cầu quyền Admin
     */
    public static function requireAdmin(array $user): void {
        self::check($user, 'admin');
    }
}
