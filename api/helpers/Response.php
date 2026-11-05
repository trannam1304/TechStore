<?php
/**
 * TechStore - Chuẩn hóa Response JSON API đạt 6 chuẩn REST & RMM Level 3
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T12 & T71:
 * - Chuẩn hóa ETag, Cache-Control & If-None-Match (304 Not Modified)
 * - Hỗ trợ siêu liên kết HATEOAS (_links)
 * - Chuẩn hóa lỗi IETF RFC 7807 (Problem Details for HTTP APIs)
 * - Header phiên bản API X-API-Version: 1.0
 */

namespace Api\Helpers;

class Response {

    /**
     * Xuất dữ liệu JSON thành công theo chuẩn RESTful thống nhất (Hỗ trợ HATEOAS, ETag và Cache-Control)
     * 
     * @param mixed $data Dữ liệu biểu diễn tài nguyên
     * @param int $status Mã trạng thái HTTP
     * @param string $message Thông báo kết quả
     * @param array|null $meta Metadata phân trang hoặc telemetry
     * @param array|null $links Mảng siêu liên kết HATEOAS (_links)
     * @param array|null $cacheOptions Cấu hình Cache: ['max_age' => 300, 'public' => true]
     */
    public static function success(
        $data = null,
        int $status = 200,
        string $message = '',
        ?array $meta = null,
        ?array $links = null,
        ?array $cacheOptions = null
    ): void {
        if ($status === 204) {
            http_response_code(204);
            header('X-API-Version: 1.0');
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('X-API-Version: 1.0');

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
        if (!empty($links)) {
            $response['_links'] = $links;
        }

        // Xử lý Nguyên lý Cacheable (ETag & Cache-Control)
        if (!empty($cacheOptions)) {
            $maxAge = (int)($cacheOptions['max_age'] ?? 300);
            $isPublic = $cacheOptions['public'] ?? true;
            $cacheControl = ($isPublic ? 'public' : 'private') . ", max-age={$maxAge}, must-revalidate";
            header("Cache-Control: {$cacheControl}");

            // Tạo ETag mã băm từ biểu diễn JSON
            $encodedJson = json_encode($response, JSON_UNESCAPED_UNICODE);
            $etag = '"' . md5($encodedJson) . '"';
            header("ETag: {$etag}");

            // Kiểm tra điều kiện If-None-Match từ Client
            $ifNoneMatch = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '');
            if ($ifNoneMatch !== '' && ($ifNoneMatch === $etag || $ifNoneMatch === str_replace('"', '', $etag) || $ifNoneMatch === '*')) {
                http_response_code(304); // 304 Not Modified
                exit;
            }

            http_response_code($status);
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        http_response_code($status);
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Xuất dữ liệu với chỉ dẫn Cache-Control và ETag
     */
    public static function withCache(
        $data,
        int $maxAge = 300,
        string $message = '',
        ?array $meta = null,
        ?array $links = null,
        bool $isPublic = true
    ): void {
        self::success(
            $data,
            200,
            $message,
            $meta,
            $links,
            ['max_age' => $maxAge, 'public' => $isPublic]
        );
    }

    /**
     * Xuất dữ liệu danh sách có phân trang chuẩn REST HATEOAS
     */
    public static function paginate(
        array $items,
        int $totalItems,
        int $page,
        int $limit,
        string $message = '',
        ?array $links = null,
        ?array $cacheOptions = null
    ): void {
        $totalPages = $limit > 0 ? (int)ceil($totalItems / $limit) : 1;
        $meta = [
            'current_page' => $page,
            'per_page'     => $limit,
            'total_items'  => $totalItems,
            'total_pages'  => $totalPages
        ];

        // Tự động sinh siêu liên kết HATEOAS phân trang nếu chưa truyền
        if ($links === null) {
            $rawUri = $_SERVER['REQUEST_URI'] ?? '/api/v1/products';
            $baseUri = strtok($rawUri, '?');
            $queryParams = $_GET ?? [];

            $buildPageUrl = function(int $p) use ($baseUri, $queryParams) {
                $params = array_merge($queryParams, ['page' => $p]);
                return $baseUri . '?' . http_build_query($params);
            };

            $links = [
                'self'  => ['href' => $rawUri, 'method' => 'GET'],
                'first' => ['href' => $buildPageUrl(1), 'method' => 'GET'],
                'last'  => ['href' => $buildPageUrl(max(1, $totalPages)), 'method' => 'GET']
            ];

            if ($page > 1) {
                $links['prev'] = ['href' => $buildPageUrl($page - 1), 'method' => 'GET'];
            }
            if ($page < $totalPages) {
                $links['next'] = ['href' => $buildPageUrl($page + 1), 'method' => 'GET'];
            }
        }

        self::success($items, 200, $message, $meta, $links, $cacheOptions);
    }

    /**
     * Xuất dữ liệu JSON báo lỗi theo chuẩn RFC 7807 (Problem Details for HTTP APIs)
     * Vừa chuẩn hóa IETF vừa tương thích 100% với Frontend và Test Suite hiện hữu
     */
    public static function error(
        string $message,
        string $code = 'ERROR',
        int $status = 400,
        array $details = []
    ): void {
        http_response_code($status);
        header('Content-Type: application/problem+json; charset=utf-8');
        header('X-API-Version: 1.0');

        $errorObj = [
            'code'    => $code,
            'message' => $message
        ];

        if (!empty($details)) {
            $errorObj['details'] = $details;
        }

        $typeSlug = strtolower(str_replace('_', '-', $code));
        $problemData = [
            // Chuẩn RFC 7807: Problem Details
            'type'      => "https://techstore.local/errors/{$typeSlug}",
            'title'     => self::getTitleForStatus($status, $code),
            'status'    => $status,
            'detail'    => $message,
            'instance'  => $_SERVER['REQUEST_URI'] ?? '/api/v1',

            // Tương thích ngược hệ thống hiện hữu
            'success'   => false,
            'error'     => $errorObj
        ];

        if (!empty($details)) {
            $problemData['invalid_params'] = $details;
        }

        echo json_encode($problemData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Tiêu đề chuẩn tương ứng mã trạng thái HTTP
     */
    private static function getTitleForStatus(int $status, string $code): string {
        $titles = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Resource Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        ];
        return $titles[$status] ?? ucwords(strtolower(str_replace('_', ' ', $code)));
    }
}
