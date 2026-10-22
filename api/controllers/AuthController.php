<?php
/**
 * TechStore - Auth Controller
 * Tái cấu trúc từ logic monolith ban đầu
 */

namespace Api\Controllers;

use Api\Core\Request;
use Api\Helpers\Response;

class AuthController extends BaseController {
    public function register(Request $request): void {
        $body = $request->body();
        $email = trim($body['email'] ?? '');
        $name = trim($body['name'] ?? 'Khách hàng mới');
        $password = $body['password'] ?? '';

        if (!$email || !$password) {
            Response::error('Vui lòng cung cấp email và mật khẩu', 'VALIDATION_ERROR', 422);
        }

        $existing = $this->model->queryOne("SELECT id FROM users WHERE email = :email", ['email' => $email]);
        if ($existing) {
            Response::error('Email này đã được sử dụng', 'EMAIL_EXISTS', 409);
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $phone = $body['phone'] ?? '';
        $address = $body['address'] ?? '';

        $sql = "INSERT INTO users (name, email, password_hash, role, phone, address, status, created_at)
                VALUES (:name, :email, :pass, 'customer', :phone, :addr, 'active', NOW())";

        $this->model->execute($sql, [
            'name' => $name,
            'email' => $email,
            'pass' => $passwordHash,
            'phone' => $phone,
            'addr' => $address
        ]);

        $userId = $this->model->lastInsertId();
        $newUser = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => 'customer',
            'phone' => $phone,
            'address' => $address,
            'status' => 'active'
        ];

        Response::json([
            'user' => $newUser,
            'access_token' => 'jwt_access_' . bin2hex(random_bytes(16)),
            'refresh_token' => 'jwt_refresh_' . bin2hex(random_bytes(16))
        ], 201, true, 'Đăng ký tài khoản thành công');
    }

    public function login(Request $request): void {
        $body = $request->body();
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        if (!$email || !$password) {
            Response::error('Vui lòng cung cấp email và mật khẩu', 'MISSING_CREDENTIALS', 400);
        }

        $user = $this->model->queryOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);

        if (!$user) {
            Response::error('Email hoặc mật khẩu không chính xác', 'INVALID_CREDENTIALS', 401);
        }

        if ($user['status'] === 'banned') {
            Response::error('Tài khoản này đã bị tạm khóa', 'ACCOUNT_BANNED', 403);
        }

        if (!password_verify($password, $user['password_hash'])) {
            Response::error('Email hoặc mật khẩu không chính xác', 'INVALID_CREDENTIALS', 401);
        }

        unset($user['password_hash']);
        $isAdmin = ($user['role'] === 'admin');

        Response::json([
            'user' => $user,
            'access_token' => ($isAdmin ? 'jwt_admin_' : 'jwt_access_') . bin2hex(random_bytes(16)),
            'refresh_token' => 'jwt_refresh_' . bin2hex(random_bytes(16))
        ], 200, true, 'Đăng nhập thành công');
    }

    public function refresh(Request $request): void {
        $body = $request->body();
        $refreshToken = $body['refresh_token'] ?? '';

        if (!$refreshToken) {
            Response::error('Vui lòng cung cấp refresh token', 'MISSING_TOKEN', 400);
        }

        Response::json([
            'access_token' => 'jwt_access_' . bin2hex(random_bytes(16)),
            'refresh_token' => 'jwt_refresh_' . bin2hex(random_bytes(16))
        ], 200, true, 'Làm mới token thành công');
    }

    public function logout(Request $request): void {
        Response::json(null, 204, true, 'Đăng xuất thành công');
    }

    public function me(Request $request): void {
        $user = $this->model->queryOne("SELECT id, name, email, role, phone, address, status, created_at FROM users LIMIT 1");
        Response::json($user);
    }

    public function googleLogin(Request $request): void {
        $body = $request->body();
        $email = $body['email'] ?? 'demo.google@gmail.com';
        $name = $body['name'] ?? 'Google User';

        $user = $this->model->queryOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);
        if (!$user) {
            $this->model->execute(
                "INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (:name, :email, :pass, 'customer', 'active', NOW())",
                ['name' => $name, 'email' => $email, 'pass' => password_hash('google_temp', PASSWORD_BCRYPT)]
            );
            $userId = $this->model->lastInsertId();
            $user = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'customer', 'status' => 'active'];
        } else {
            unset($user['password_hash']);
        }

        Response::json([
            'user' => $user,
            'access_token' => 'jwt_access_' . bin2hex(random_bytes(16)),
            'refresh_token' => 'jwt_refresh_' . bin2hex(random_bytes(16))
        ], 200, true, 'Đăng nhập Google OAuth thành công');
    }
}
