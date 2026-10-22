<?php
/**
 * TechStore - Role-based Access Control Middleware
 */

namespace Api\Middleware;

use Api\Core\Request;
use Api\Helpers\Response;

class RoleMiddleware {
    private string $requiredRole;

    public function __construct(string $role = 'admin') {
        $this->requiredRole = $role;
    }

    public function handle(Request $request): void {
        $user = $request->getUser();

        if (!$user) {
            Response::error('Yêu cầu xác thực tài khoản', 'UNAUTHORIZED', 401);
        }

        if (($user['role'] ?? '') !== $this->requiredRole) {
            Response::error("Từ chối truy cập: Yêu cầu quyền [{$this->requiredRole}]", 'FORBIDDEN', 403);
        }
    }

    public static function admin(): string {
        return self::class;
    }
}
