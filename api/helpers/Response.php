<?php
/**
 * TechStore - Standardized JSON Response Helper
 * Follows RESTful best practices and matches front-end expectations
 */

namespace Api\Helpers;

class Response {
    public static function json($data = null, int $status = 200, bool $success = true, string $message = '', array $meta = []): void {
        http_response_code($status);

        if ($status === 204) {
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $res = ['success' => $success];

        if ($message !== '') {
            $res['message'] = $message;
        }

        if ($data !== null) {
            $res['data'] = $data;
        }

        if (!empty($meta)) {
            $res['meta'] = $meta;
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $message, string $code = 'ERROR', int $status = 400, array $details = []): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        $errorPayload = [
            'code' => $code,
            'message' => $message
        ];

        if (!empty($details)) {
            $errorPayload['details'] = $details;
        }

        echo json_encode([
            'success' => false,
            'error' => $errorPayload
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function paginated(array $items, int $total, int $page, int $limit, string $message = ''): void {
        $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;

        $meta = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages
        ];

        self::json($items, 200, true, $message, $meta);
    }
}
