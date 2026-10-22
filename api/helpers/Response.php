<?php
/**
 * TechStore - Chuẩn hóa Response JSON API
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T12: Validator, ErrorHandler chuẩn JSON, CORS config
 */

namespace Api\Helpers;

class Response {
    /**
     * Xuất dữ liệu JSON thành công theo chuẩn RESTful thống nhất
     * Định dạng: { "success": true, "data": ..., "message": "...", "meta": ... }
     */
    public static function success($data = null, int $status = 200, string $message = '', ?array $meta = null): void {
        http_response_code($status);
        if ($status === 204) {
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $response = ['success' => true];
        if ($message !== '') {
            $response['message'] = $message;
        }
        if ($data !== null) {
            $response['data'] = $data;
        }
        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Xuất dữ liệu JSON báo lỗi theo chuẩn RESTful thống nhất
     * Định dạng: { "success": false, "error": { "code": "...", "message": "...", "details": [...] } }
     */
    public static function error(string $message, string $code = 'ERROR', int $status = 400, array $details = []): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        $errorObj = [
            'code'    => $code,
            'message' => $message
        ];

        if (!empty($details)) {
            $errorObj['details'] = $details;
        }

        echo json_encode([
            'success' => false,
            'error'   => $errorObj
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Xuất dữ liệu danh sách có phân trang
     */
    public static function paginate(array $items, int $totalItems, int $page, int $limit, string $message = ''): void {
        $totalPages = $limit > 0 ? (int)ceil($totalItems / $limit) : 1;
        $meta = [
            'current_page' => $page,
            'per_page'     => $limit,
            'total_items'  => $totalItems,
            'total_pages'  => $totalPages
        ];

        self::success($items, 200, $message, $meta);
    }
}
