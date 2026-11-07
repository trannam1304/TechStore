<?php
/**
 * TechStore - CORS Middleware
 * Topic 1: RESTful API - PHP thuan
 * Nhiem vu T12: Validator, ErrorHandler chuan JSON, CORS config
 */

namespace Api\Middleware;

class CorsMiddleware {
    public static function handle(): void {
        $allowedOrigins = getenv('CORS_ALLOWED_ORIGINS') ?: '*';
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($allowedOrigins === '*') {
            if ($origin) {
                header("Access-Control-Allow-Origin: {$origin}");
                header('Access-Control-Allow-Credentials: true');
            } else {
                header('Access-Control-Allow-Origin: *');
            }
        } else {
            $origins = array_map('trim', explode(',', $allowedOrigins));
            if (in_array($origin, $origins, true)) {
                header("Access-Control-Allow-Origin: {$origin}");
                header('Access-Control-Allow-Credentials: true');
            } else {
                header('Access-Control-Allow-Origin: *');
            }
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-HTTP-Method-Override, X-Signature');
        header('Access-Control-Max-Age: 86400'); // 24 hours preflight cache

        // Security Headers: Chống tấn công XSS, Sniffing, Clickjacking (Mục 3.4 Báo cáo kỹ thuật)
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:; frame-ancestors 'self';");

        // Tra ve ngay lap tuc neu la OPTIONS request
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}