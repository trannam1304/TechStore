<?php
/**
 * TechStore - Router Service
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T03: Dựng khung dự án, Front Controller, Router
 */

namespace Api;

class Router {
    /**
     * Chuyển tiếp yêu cầu HTTP đến bộ định tuyến chính api/routes.php
     */
    public static function dispatch(): void {
        require_once __DIR__ . '/routes.php';
    }
}
