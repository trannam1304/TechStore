<?php
/**
 * TechStore - Authentication Middleware (JWT Bearer Token)
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T10: JwtService (HS256 ký/xác thực, exp), AuthMiddleware, RoleMiddleware
 */

namespace Api\Middleware;

use Api\Services\JwtService;
use Api\Helpers\Response;

class AuthMiddleware {
    /**
     * Lấy chuỗi Bearer token từ Request Header
     */
    public static function extractToken(): ?string {
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        $authHeader = $headers['Authorization'] 
            ?? $headers['authorization'] 
            ?? $_SERVER['HTTP_AUTHORIZATION'] 
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
            ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', trim($authHeader), $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Bắt buộc phải đăng nhập (Xác thực JWT token)
     * Trả về thông tin người dùng được giải mã từ token nếu hợp lệ, dừng request 401 nếu sai
     *
     * @return array Payload chứa sub (user_id), email, role, name
     */
    public static function handle(): array {
        $token = self::extractToken();

        if (!$token) {
            Response::error(
                'Yêu cầu xác thực: Vui lòng cung cấp Bearer token trong Header Authorization',
                'UNAUTHORIZED',
                401
            );
        }

        $payload = JwtService::verifyToken($token);

        if (!$payload) {
            Response::error(
                'Token không hợp lệ hoặc đã hết hạn. Vui lòng đăng nhập lại.',
                'INVALID_OR_EXPIRED_TOKEN',
                401
            );
        }

        if (($payload['type'] ?? '') === 'refresh') {
            Response::error(
                'Không thể sử dụng Refresh Token để truy cập tài nguyên bảo mật. Vui lòng dùng Access Token.',
                'INVALID_TOKEN_TYPE',
                401
            );
        }

        return $payload;
    }

    /**
     * Xác thực tùy chọn (Optional Auth)
     * Trả về payload nếu có token hợp lệ, trả về null nếu không có token
     */
    public static function optional(): ?array {
        $token = self::extractToken();
        if (!$token) {
            return null;
        }

        $payload = JwtService::verifyToken($token);
        if (!$payload || ($payload['type'] ?? '') === 'refresh') {
            return null;
        }

        return $payload;
    }
}
