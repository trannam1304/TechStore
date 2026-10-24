<?php
/**
 * TechStore - Auth Controller (Authentication & Authorization)
 * Quản lý đăng ký, đăng nhập (Bcrypt), cấp phát & làm mới cặp JWT Access/Refresh Token
 * Triển khai chuẩn bảo mật cao cấp: Refresh Token Rotation (RTR), Token Reuse Detection & HttpOnly Cookie
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/DataStore.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../services/JwtService.php';

use Api\Helpers\Validator;
use Api\Helpers\DataStore;
use Api\Helpers\Response;
use Api\Services\JwtService;

class AuthController extends BaseController {

    /**
     * Thiết lập Refresh Token vào HttpOnly Cookie an toàn
     */
    public static function setRefreshTokenCookie(string $refreshToken, int $expiresIn): void {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        setcookie('refresh_token', $refreshToken, [
            'expires'  => time() + $expiresIn,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Xóa Refresh Token Cookie khi đăng xuất hoặc bị revoke
     */
    public static function clearRefreshTokenCookie(): void {
        setcookie('refresh_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * POST /api/v1/auth/register
     * Đăng ký tài khoản người dùng mới
     */
    public static function register(array $body): void {
        $validator = Validator::make($body, [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email|max:150',
            'password' => 'required|min:6|max:64'
        ]);

        if ($validator->fails()) {
            Response::error(
                $validator->getFirstError(),
                'VALIDATION_ERROR',
                400,
                $validator->getFlattenedErrors()
            );
        }

        $email = trim($body['email']);
        $existing = DataStore::findUserByEmail($email);
        if ($existing) {
            Response::error(
                'Địa chỉ email này đã được sử dụng. Vui lòng đăng nhập hoặc dùng email khác.',
                'EMAIL_ALREADY_EXISTS',
                409
            );
        }

        $newUser = DataStore::createUser([
            'name'          => trim($body['name']),
            'email'         => $email,
            'password_hash' => password_hash($body['password'], PASSWORD_BCRYPT),
            'role'          => 'customer',
            'phone'         => $body['phone'] ?? null,
            'address'       => $body['address'] ?? null,
            'status'        => 'active'
        ]);

        // Tạo cặp JWT Tokens
        $accessToken = JwtService::generateAccessToken($newUser);
        $refreshToken = JwtService::generateRefreshToken($newUser);

        // Lưu refresh token vào CSDL (bảo mật lưu hash SHA-256)
        $rfExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        DataStore::saveRefreshToken((int)$newUser['id'], $refreshToken, $rfExpiry);

        // Đặt cookie HttpOnly
        self::setRefreshTokenCookie($refreshToken, $rfExpiry);

        unset($newUser['password_hash']);

        Response::success([
            'user'          => $newUser,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900)
        ], 201, 'Đăng ký tài khoản thành công');
    }

    /**
     * POST /api/v1/auth/login
     * Đăng nhập hệ thống, cấp Access Token & Refresh Token
     * Chỉ xác thực chuẩn Bcrypt - Không có bất kỳ backdoor fallback nào
     */
    public static function login(array $body): void {
        $validator = Validator::make($body, [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            Response::error(
                $validator->getFirstError(),
                'VALIDATION_ERROR',
                400,
                $validator->getFlattenedErrors()
            );
        }

        $email = trim($body['email']);
        $password = $body['password'];

        $user = DataStore::findUserByEmail($email);

        if (!$user) {
            Response::error(
                'Tài khoản email hoặc mật khẩu không chính xác.',
                'INVALID_CREDENTIALS',
                401
            );
        }

        // Kiểm tra trạng thái tài khoản
        if (($user['status'] ?? 'active') === 'banned') {
            Response::error(
                'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa. Vui lòng liên hệ ban quản trị.',
                'ACCOUNT_BANNED',
                403
            );
        }

        // Kiểm tra mật khẩu chuẩn Bcrypt
        $passwordValid = false;
        if (!empty($user['password_hash'])) {
            $passwordValid = password_verify($password, $user['password_hash']);
        }

        if (!$passwordValid) {
            Response::error(
                'Tài khoản email hoặc mật khẩu không chính xác.',
                'INVALID_CREDENTIALS',
                401
            );
        }

        // Tạo JWT Access & Refresh Token
        $accessToken = JwtService::generateAccessToken($user);
        $refreshToken = JwtService::generateRefreshToken($user);

        // Lưu refresh token vào DB
        $rfExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        DataStore::saveRefreshToken((int)$user['id'], $refreshToken, $rfExpiry);

        // Đặt cookie HttpOnly
        self::setRefreshTokenCookie($refreshToken, $rfExpiry);

        unset($user['password_hash']);

        Response::success([
            'user'          => $user,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900)
        ], 200, 'Đăng nhập thành công');
    }

    /**
     * POST /api/v1/auth/refresh
     * Cấp lại cặp Token mới qua Refresh Token Rotation (RTR)
     * Phát hiện tái sử dụng token (Token Reuse Detection) và vô hiệu hóa nếu có xâm nhập
     */
    public static function refresh(array $body): void {
        $refreshToken = trim($body['refresh_token'] ?? $_COOKIE['refresh_token'] ?? '');
        if (!$refreshToken) {
            Response::error('Thiếu thông tin refresh_token', 'VALIDATION_ERROR', 400);
        }

        // 1. Kiểm tra tính hợp lệ về mặt mã hóa và hạn sử dụng của JWT
        $payload = JwtService::verifyToken($refreshToken);
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            Response::error(
                'Refresh token không hợp lệ hoặc đã hết hạn.',
                'INVALID_REFRESH_TOKEN',
                401
            );
        }

        $userId = (int)($payload['sub'] ?? 0);

        // 2. Tìm bản ghi token trong CSDL
        $tokenRecord = DataStore::findRefreshTokenRecord($refreshToken);
        if (!$tokenRecord) {
            Response::error(
                'Refresh token không tồn tại trong hệ thống.',
                'REFRESH_TOKEN_NOT_FOUND',
                401
            );
        }

        // 3. Phát hiện Token Reuse (Token đã bị thu hồi trước đó nhưng bị gửi lại)
        if ((int)$tokenRecord['revoked'] === 1) {
            // Nguy cơ bảo mật: Refresh token bị đánh cắp và tái sử dụng
            // Thu hồi toàn bộ refresh tokens của user để bảo vệ tài khoản
            DataStore::revokeAllUserRefreshTokens($userId);
            self::clearRefreshTokenCookie();
            Response::error(
                'Cảnh báo bảo mật: Phát hiện Refresh Token đã bị thu hồi trước đó (Token Reuse). ' .
                'Toàn bộ phiên đăng nhập đã bị vô hiệu hóa vì lý do an toàn. Vui lòng đăng nhập lại.',
                'TOKEN_REUSE_DETECTED',
                401
            );
        }

        // 4. Kiểm tra hạn sống của token trong DB
        if (strtotime($tokenRecord['expires_at']) <= time()) {
            Response::error(
                'Refresh token đã hết hạn sử dụng. Vui lòng đăng nhập lại.',
                'REFRESH_TOKEN_EXPIRED',
                401
            );
        }

        // 5. Lấy thông tin người dùng tương ứng
        $user = DataStore::findUserById($userId);
        if (!$user || ($user['status'] ?? 'active') === 'banned') {
            Response::error('Người dùng không còn tồn tại hoặc đã bị khóa.', 'USER_INACTIVE', 401);
        }

        // 6. THỰC HIỆN REFRESH TOKEN ROTATION:
        // a) Thu hồi refresh token cũ
        DataStore::revokeRefreshToken($refreshToken);

        // b) Sinh cặp token mới hoàn toàn
        $newAccessToken = JwtService::generateAccessToken($user);
        $newRefreshToken = JwtService::generateRefreshToken($user);

        // c) Lưu refresh token mới vào CSDL
        $rfExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        DataStore::saveRefreshToken($userId, $newRefreshToken, $rfExpiry);

        // d) Cập nhật cookie HttpOnly
        self::setRefreshTokenCookie($newRefreshToken, $rfExpiry);

        unset($user['password_hash']);

        Response::success([
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900),
            'user'          => $user
        ], 200, 'Làm mới Access Token & Refresh Token thành công (Rotation)');
    }

    /**
     * POST /api/v1/auth/logout
     * Đăng xuất và thu hồi Refresh Token
     */
    public static function logout(array $user, array $body): void {
        $refreshToken = trim($body['refresh_token'] ?? $_COOKIE['refresh_token'] ?? '');
        if ($refreshToken) {
            DataStore::revokeRefreshToken($refreshToken);
        }

        self::clearRefreshTokenCookie();

        // Trả về HTTP 204 No Content theo chuẩn RESTful
        Response::success(null, 204);
    }

    /**
     * GET /api/v1/auth/me
     * Lấy thông tin tài khoản hiện tại
     */
    public static function me(array $authUser): void {
        $userId = (int)($authUser['sub'] ?? 0);
        $user = DataStore::findUserById($userId);

        if (!$user) {
            Response::error('Không tìm thấy tài khoản người dùng', 'USER_NOT_FOUND', 404);
        }

        unset($user['password_hash']);
        Response::success($user, 200);
    }

    /**
     * POST /api/v1/auth/google
     * Đăng nhập demo OAuth 2.0 Google
     */
    public static function google(array $body): void {
        $token = $body['token'] ?? '';
        $email = $body['email'] ?? 'google_user@gmail.com';
        $name = $body['name'] ?? 'Google Customer';

        $user = DataStore::findUserByEmail($email);
        if (!$user) {
            $user = DataStore::createUser([
                'name'          => $name,
                'email'         => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                'role'          => 'customer',
                'avatar'        => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=200',
                'status'        => 'active'
            ]);
        }

        $accessToken = JwtService::generateAccessToken($user);
        $refreshToken = JwtService::generateRefreshToken($user);

        $rfExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        DataStore::saveRefreshToken((int)$user['id'], $refreshToken, $rfExpiry);
        self::setRefreshTokenCookie($refreshToken, $rfExpiry);

        unset($user['password_hash']);

        Response::success([
            'user'          => $user,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900)
        ], 200, 'Đăng nhập Google OAuth thành công');
    }
}