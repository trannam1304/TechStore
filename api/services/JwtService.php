<?php
/**
 * TechStore - JWT Service (JSON Web Token HS256)
 * Topic 1: RESTful API · PHP thuần không framework
 * Nhiệm vụ T10: JwtService (HS256 ký/xác thực, exp), AuthMiddleware, RoleMiddleware
 */

namespace Api\Services;

use Exception;

class JwtService {
    /**
     * Lấy JWT Secret Key từ biến môi trường hoặc khóa mặc định an toàn
     */
    private static function getSecret(): string {
        $secret = getenv('JWT_SECRET');
        if (!$secret || trim($secret) === '' || $secret === 'your_jwt_secret_key_here') {
            return 'techstore_rest_api_jwt_secret_key_2026_super_secure_hcmus';
        }
        return $secret;
    }

    /**
     * Base64URL Encode (Chuẩn RFC 7515)
     */
    public static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64URL Decode (Chuẩn RFC 7515)
     */
    public static function base64UrlDecode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Tạo JWT Token ký bằng thuật toán HMAC-SHA256 (HS256)
     *
     * @param array $payload Dữ liệu claims
     * @param int $expiresIn Thời gian sống tính bằng giây (Mặc định 3600s = 1 giờ)
     * @return string Chuỗi JWT Token chuẩn header.payload.signature
     */
    public static function generateToken(array $payload, int $expiresIn = 3600): string {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $expiresIn;

        $encodedHeader = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE));
        $encodedPayload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $signature = hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", self::getSecret(), true);
        $encodedSignature = self::base64UrlEncode($signature);

        return "{$encodedHeader}.{$encodedPayload}.{$encodedSignature}";
    }

    /**
     * Tạo Access Token (Hạn ngắn: 1 giờ / 3600s)
     */
    public static function generateAccessToken(array $user): string {
        $payload = [
            'sub'   => (int)$user['id'],
            'email' => $user['email'],
            'name'  => $user['name'] ?? '',
            'role'  => $user['role'] ?? 'customer',
            'type'  => 'access'
        ];
        $expiry = (int)(getenv('JWT_ACCESS_EXPIRY') ?: 3600);
        return self::generateToken($payload, $expiry);
    }

    /**
     * Tạo Refresh Token (Hạn dài: 7 ngày / 604800s)
     */
    public static function generateRefreshToken(array $user): string {
        $payload = [
            'sub'   => (int)$user['id'],
            'email' => $user['email'],
            'type'  => 'refresh',
            'jti'   => bin2hex(random_bytes(16)) // Unique token ID
        ];
        $expiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        return self::generateToken($payload, $expiry);
    }

    /**
     * Xác thực và giải mã JWT Token
     *
     * @param string $token
     * @return array|null Trả về mảng payload nếu hợp lệ, null nếu không hợp lệ hoặc hết hạn
     */
    public static function verifyToken(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        // 1. Kiểm tra chữ ký HMAC-SHA256
        $expectedSignature = hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", self::getSecret(), true);
        $actualSignature = self::base64UrlDecode($encodedSignature);

        if (!hash_equals($expectedSignature, $actualSignature)) {
            return null; // Chữ ký không khớp, token đã bị chỉnh sửa
        }

        // 2. Giải mã Header & Payload
        $headerJson = self::base64UrlDecode($encodedHeader);
        $payloadJson = self::base64UrlDecode($encodedPayload);

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) {
            return null;
        }

        if (($header['alg'] ?? '') !== 'HS256') {
            return null;
        }

        // 3. Kiểm tra thời hạn hết hiệu lực (Expiration Time)
        $now = time();
        if (isset($payload['exp']) && $payload['exp'] < $now) {
            return null; // Token đã hết hạn
        }

        // 4. Kiểm tra thời điểm phát hành (Issued At)
        if (isset($payload['iat']) && $payload['iat'] > $now + 60) {
            return null; // Token đến từ tương lai
        }

        return $payload;
    }
}
