<?php
/**
 * TechStore - CORS Middleware
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T12: Validator, ErrorHandler chuẩn JSON, CORS config
 */

namespace Api\Middleware;

class CorsMiddleware {
    public static function handle(): void {
        $allowedOrigins = getenv('CORS_ALLOWED_ORIGINS') ?: '*';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        if ($allowedOrigins === '*') {
            header('Access-Control-Allow-Origin: *');
        } else {
            $origins = array_map('trim', explode(',', $allowedOrigins));
            if (in_array($origin, $origins, true)) {
                header("Access-Control-Allow-Origin: {$origin}");
            } else {
                header('Access-Control-Allow-Origin: *');
            }
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-HTTP-Method-Override, X-Signature');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 hours preflight cache

        // Trả về ngay lập tức nếu là OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
