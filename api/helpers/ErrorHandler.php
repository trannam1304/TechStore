<?php
/**
 * TechStore - Global Error & Exception Handler
 * Topic 1: RESTful API · PHP thuần
 * Nhiệm vụ T12: Validator, ErrorHandler chuẩn JSON, CORS config
 */

namespace Api\Helpers;

use Throwable;

class ErrorHandler {
    public static function register(): void {
        // Tắt hiển thị HTML error mặc định
        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        // Bắt mọi ngoại lệ (Exceptions & Errors)
        set_exception_handler([self::class, 'handleException']);

        // Bắt lỗi PHP thường
        set_error_handler([self::class, 'handleError']);

        // Bắt lỗi nghiêm trọng (Fatal Errors khi shutdown)
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(Throwable $e): void {
        error_log("[API Exception] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

        $statusCode = 500;
        $code = 'INTERNAL_SERVER_ERROR';

        $rawCode = $e->getCode();
        if (is_numeric($rawCode) && (int)$rawCode >= 400 && (int)$rawCode < 600) {
            $statusCode = (int)$rawCode;
        }

        $isDebug = getenv('APP_DEBUG') === 'true';

        $details = [];
        if ($isDebug) {
            $details = [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ];
        }

        Response::error(
            $e->getMessage() ?: 'Đã xảy ra lỗi máy chủ nội bộ',
            $code,
            $statusCode,
            $details
        );
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        error_log("[PHP Error] {$message} in {$file}:{$line}");
        return true;
    }

    public static function handleShutdown(): void {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            error_log("[Fatal Error] {$error['message']} in {$error['file']}:{$error['line']}");
            Response::error(
                'Lỗi hệ thống nghiêm trọng (Fatal Error)',
                'FATAL_ERROR',
                500,
                getenv('APP_DEBUG') === 'true' ? [$error['message'] . " in {$error['file']}:{$error['line']}"] : []
            );
        }
    }
}
