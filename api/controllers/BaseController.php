<?php
/**
 * TechStore - Base Controller
 * Cung cấp các hàm dùng chung: format JSON response, Auth Middleware helper
 * Loại bỏ hoàn toàn cơ chế lưu trữ tạm data.json
 */

namespace Api\Controllers;

require_once __DIR__ . '/../helpers/DataStore.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/JwtService.php';

use Api\Helpers\DataStore;
use Api\Helpers\Response;
use Api\Middleware\AuthMiddleware;
use Api\Services\JwtService;

abstract class BaseController {
    /**
     * Trả về phản hồi thành công chuẩn JSON
     */
    public static function jsonRes($data = null, int $status = 200, bool $success = true, string $message = ''): void {
        Response::success($data, $status, $message);
    }

    /**
     * Trả về phản hồi lỗi chuẩn JSON
     */
    public static function jsonErr(string $message, string $code = 'ERROR', int $status = 400): void {
        Response::error($message, $code, $status);
    }

    /**
     * Lấy thông tin user hiện tại qua JWT access token từ CSDL MySQL
     */
    public static function getCurrentAuthUser(?array $db = null): ?array {
        $token = AuthMiddleware::extractToken();
        if (!$token) {
            return null;
        }

        $payload = JwtService::verifyToken($token);
        if (!$payload || !isset($payload['sub']) || ($payload['type'] ?? '') !== 'access') {
            return null;
        }

        $user = DataStore::findUserById((int)$payload['sub']);
        if (!$user || ($user['status'] ?? 'active') === 'banned') {
            return null;
        }

        unset($user['password_hash']);
        return $user;
    }
}
