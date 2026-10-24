<?php
/**
 * TechStore - Auth Controller (Authentication & Authorization)
 * Qu?n lý dang ký, dang nh?p (Bcrypt), c?p phát & làm m?i c?p JWT Access/Refresh Token
 * Tri?n khai chu?n b?o m?t cao c?p: Refresh Token Rotation (RTR), Token Reuse Detection & HttpOnly Cookie
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
     * Thi?t l?p Refresh Token vào HttpOnly Cookie an toàn
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
     * Xóa Refresh Token Cookie khi dang xu?t ho?c b? revoke
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
     * Ðang ký tài kho?n ngu?i dùng m?i
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
                'Ð?a ch? email này dã du?c s? d?ng. Vui lòng dang nh?p ho?c dùng email khác.',
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

        // T?o c?p JWT Tokens
        $accessToken = JwtService::generateAccessToken($newUser);
        $refreshToken = JwtService::generateRefreshToken($newUser);

        // Luu refresh token vào CSDL (b?o m?t luu hash SHA-256)
        $rfExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        DataStore::saveRefreshToken((int)$newUser['id'], $refreshToken, $rfExpiry);

        // Ð?t cookie HttpOnly
        self::setRefreshTokenCookie($refreshToken, $rfExpiry);

        unset($newUser['password_hash']);

        Response::success([
            'user'          => $newUser,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900)
        ], 201, 'Ðang ký tài kho?n thành công');
    }

    /**
     * POST /api/v1/auth/login
     * Ðang nh?p h? th?ng, c?p Access Token & Refresh Token
     * Ch? xác th?c chu?n Bcrypt - Không có b?t k? backdoor fallback nào
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
                'Tài kho?n email ho?c m?t kh?u không chính xác.',
                'INVALID_CREDENTIALS',
                401
            );
        }

        // Ki?m tra tr?ng thái tài kho?n
        if (($user['status'] ?? 'active') === 'banned') {
            Response::error(
                'Tài kho?n c?a b?n dã b? khóa ho?c vô hi?u hóa. Vui lòng liên h? ban qu?n tr?.',
                'ACCOUNT_BANNED',
                403
            );
        }

        // Ki?m tra m?t kh?u chu?n Bcrypt
        $passwordValid = false;
        if (!empty($user['password_hash'])) {
            $passwordValid = password_verify($password, $user['password_hash']);
        }

        if (!$passwordValid) {
            Response::error(
                'Tài kho?n email ho?c m?t kh?u không chính xác.',
                'INVALID_CREDENTIALS',
                401
            );
        }

        // T?o JWT Access & Refresh Token
        $accessToken = JwtService::generateAccessToken($user);
        $refreshToken = JwtService::generateRefreshToken($user);

        // Luu refresh token vào DB
        $rfExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        DataStore::saveRefreshToken((int)$user['id'], $refreshToken, $rfExpiry);

        // Ð?t cookie HttpOnly
        self::setRefreshTokenCookie($refreshToken, $rfExpiry);

        unset($user['password_hash']);

        Response::success([
            'user'          => $user,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900)
        ], 200, 'Ðang nh?p thành công');
    }

    /**
     * POST /api/v1/auth/refresh
     * C?p l?i c?p Token m?i qua Refresh Token Rotation (RTR)
     * Phát hi?n tái s? d?ng token (Token Reuse Detection) và vô hi?u hóa n?u có xâm nh?p
     */
    public static function refresh(array $body): void {
        $refreshToken = trim($body['refresh_token'] ?? $_COOKIE['refresh_token'] ?? '');
        if (!$refreshToken) {
            Response::error('Thi?u thông tin refresh_token', 'VALIDATION_ERROR', 400);
        }

        // 1. Ki?m tra tính h?p l? v? m?t mã hóa và h?n s? d?ng c?a JWT
        $payload = JwtService::verifyToken($refreshToken);
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            Response::error(
                'Refresh token không h?p l? ho?c dã h?t h?n.',
                'INVALID_REFRESH_TOKEN',
                401
            );
        }

        $userId = (int)($payload['sub'] ?? 0);

        // 2. Tìm b?n ghi token trong CSDL
        $tokenRecord = DataStore::findRefreshTokenRecord($refreshToken);
        if (!$tokenRecord) {
            Response::error(
                'Refresh token không t?n t?i trong h? th?ng.',
                'REFRESH_TOKEN_NOT_FOUND',
                401
            );
        }

        // 3. Phát hi?n Token Reuse (Token dã b? thu h?i tru?c dó nhung b? g?i l?i)
        if ((int)$tokenRecord['revoked'] === 1) {
            // Nguy co b?o m?t: Refresh token b? dánh c?p và tái s? d?ng
            // Thu h?i toàn b? refresh tokens c?a user d? b?o v? tài kho?n
            DataStore::revokeAllUserRefreshTokens($userId);
            self::clearRefreshTokenCookie();
            Response::error(
                'C?nh báo b?o m?t: Phát hi?n Refresh Token dã b? thu h?i tru?c dó (Token Reuse). ' .
                'Toàn b? phiên dang nh?p dã b? vô hi?u hóa vì lý do an toàn. Vui lòng dang nh?p l?i.',
                'TOKEN_REUSE_DETECTED',
                401
            );
        }

        // 4. Ki?m tra h?n s?ng c?a token trong DB
        if (strtotime($tokenRecord['expires_at']) <= time()) {
            Response::error(
                'Refresh token dã h?t h?n s? d?ng. Vui lòng dang nh?p l?i.',
                'REFRESH_TOKEN_EXPIRED',
                401
            );
        }

        // 5. L?y thông tin ngu?i dùng tuong ?ng
        $user = DataStore::findUserById($userId);
        if (!$user || ($user['status'] ?? 'active') === 'banned') {
            Response::error('Ngu?i dùng không còn t?n t?i ho?c dã b? khóa.', 'USER_INACTIVE', 401);
        }

        // 6. TH?C HI?N REFRESH TOKEN ROTATION:
        // a) Thu h?i refresh token cu
        DataStore::revokeRefreshToken($refreshToken);

        // b) Sinh c?p token m?i hoàn toàn
        $newAccessToken = JwtService::generateAccessToken($user);
        $newRefreshToken = JwtService::generateRefreshToken($user);

        // c) Luu refresh token m?i vào CSDL
        $rfExpiry = (int)(getenv('JWT_REFRESH_EXPIRY') ?: 604800);
        DataStore::saveRefreshToken($userId, $newRefreshToken, $rfExpiry);

        // d) C?p nh?t cookie HttpOnly
        self::setRefreshTokenCookie($newRefreshToken, $rfExpiry);

        unset($user['password_hash']);

        Response::success([
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int)(getenv('JWT_ACCESS_EXPIRY') ?: 900),
            'user'          => $user
        ], 200, 'Làm m?i Access Token & Refresh Token thành công (Rotation)');
    }

    /**
     * POST /api/v1/auth/logout
     * Ðang xu?t và thu h?i Refresh Token
     */
    public static function logout(array $user, array $body): void {
        $refreshToken = trim($body['refresh_token'] ?? $_COOKIE['refresh_token'] ?? '');
        if ($refreshToken) {
            DataStore::revokeRefreshToken($refreshToken);
        }

        self::clearRefreshTokenCookie();

        // Tr? v? HTTP 204 No Content theo chu?n RESTful
        Response::success(null, 204);
    }

    /**
     * GET /api/v1/auth/me
     * L?y thông tin tài kho?n hi?n t?i
     */
    public static function me(array $authUser): void {
        $userId = (int)($authUser['sub'] ?? 0);
        $user = DataStore::findUserById($userId);

        if (!$user) {
            Response::error('Không tìm th?y tài kho?n ngu?i dùng', 'USER_NOT_FOUND', 404);
        }

        unset($user['password_hash']);
        Response::success($user, 200);
    }

    /**
     * POST /api/v1/auth/google
     * Ðang nh?p demo OAuth 2.0 Google
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
        ], 200, 'Ðang nh?p Google OAuth thành công');
    }
}