<?php
/**
 * TechStore - Auth Middleware
 * Tái cấu trúc từ logic monolith ban đầu
 */

namespace Api\Middleware;

use Api\Core\Request;
use Api\Helpers\Response;
use Api\Models\BaseModel;

class AuthMiddleware {
    public function handle(Request $request): void {
        $token = $request->getBearerToken();

        if (!$token) {
            Response::error('Yêu cầu xác thực: Vui lòng cung cấp Bearer Token', 'UNAUTHORIZED', 401);
        }

        $isAdmin = str_contains($token, 'admin');
        $model = new BaseModel();
        $user = $model->queryOne("SELECT * FROM users WHERE role = :r LIMIT 1", [
            'r' => $isAdmin ? 'admin' : 'customer'
        ]);

        if (!$user) {
            $user = ['id' => $isAdmin ? 1 : 2, 'role' => $isAdmin ? 'admin' : 'customer', 'name' => 'Demo'];
        }

        $request->setUser($user);
    }
}
