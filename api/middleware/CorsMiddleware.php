<?php
/**
 * TechStore - CORS Middleware
 * Sets Cross-Origin Resource Sharing headers and handles OPTIONS preflight
 */

namespace Api\Middleware;

use Api\Core\Request;

class CorsMiddleware {
    public function handle(Request $request): void {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Signature, X-HTTP-Method-Override');
        header('Access-Control-Max-Age: 86400');

        if ($request->getMethod() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
