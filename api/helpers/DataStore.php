<?php
/**
 * TechStore - MySQL Data Access Store
 * Kết nối MySQL thuần qua PDO Singleton, loại bỏ hoàn toàn lưu trữ tạm vào data.json
 */

namespace Api\Helpers;

require_once __DIR__ . '/../../config/database.php';

use Config\Database;
use PDO;
use Exception;

class DataStore {
    /**
     * Lấy kết nối PDO
     */
    public static function getPdo(): PDO {
        return Database::getConnection();
    }

    /**
     * Tìm user theo email
     */
    public static function findUserByEmail(string $email): ?array {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Tìm user theo id
     */
    public static function findUserById(int $id): ?array {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Tạo user mới
     */
    public static function createUser(array $userData): array {
        $pdo = self::getPdo();
        $sql = "INSERT INTO users (name, email, password_hash, role, phone, address, avatar, status, created_at)
                VALUES (:name, :email, :password_hash, :role, :phone, :address, :avatar, :status, :created_at)";
        $stmt = $pdo->prepare($sql);
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            ':name'          => $userData['name'],
            ':email'         => $userData['email'],
            ':password_hash' => $userData['password_hash'],
            ':role'          => $userData['role'] ?? 'customer',
            ':phone'         => $userData['phone'] ?? null,
            ':address'       => $userData['address'] ?? null,
            ':avatar'        => $userData['avatar'] ?? null,
            ':status'        => $userData['status'] ?? 'active',
            ':created_at'    => $now
        ]);
        $userData['id'] = (int)$pdo->lastInsertId();
        $userData['created_at'] = $now;
        return $userData;
    }

    /**
     * Cập nhật thông tin user
     */
    public static function updateUser(int $id, array $fields): bool {
        $pdo = self::getPdo();
        $allowed = ['name', 'phone', 'address', 'avatar', 'status', 'role'];
        $updates = [];
        $params = [];
        foreach ($fields as $k => $v) {
            if (in_array($k, $allowed)) {
                $updates[] = "`{$k}` = ?";
                $params[] = $v;
            }
        }
        if (empty($updates)) return false;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Lưu hash của refresh token vào CSDL
     */
    public static function saveRefreshToken(int $userId, string $token, int $expiresIn): void {
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

        $pdo = self::getPdo();
        $stmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token_hash, expires_at, revoked, created_at)
                               VALUES (?, ?, ?, 0, NOW())");
        $stmt->execute([$userId, $tokenHash, $expiresAt]);
    }

    /**
     * Thu hồi một refresh token (dùng khi logout hoặc rotation)
     */
    public static function revokeRefreshToken(string $token): void {
        $tokenHash = hash('sha256', $token);
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("UPDATE refresh_tokens SET revoked = 1 WHERE token_hash = ?");
        $stmt->execute([$tokenHash]);
    }

    /**
     * Tìm bản ghi refresh token trong CSDL
     */
    public static function findRefreshTokenRecord(string $token): ?array {
        $tokenHash = hash('sha256', $token);
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM refresh_tokens WHERE token_hash = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$tokenHash]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Vô hiệu hóa toàn bộ refresh token của một user
     */
    public static function revokeAllUserRefreshTokens(int $userId): void {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("UPDATE refresh_tokens SET revoked = 1 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    /**
     * Kiểm tra refresh token có hợp lệ trong DB không
     */
    public static function isRefreshTokenValid(string $token): bool {
        $record = self::findRefreshTokenRecord($token);
        if (!$record) {
            return false;
        }

        // Đã bị thu hồi
        if ((int)$record['revoked'] === 1) {
            return false;
        }

        // Đã hết hạn
        if (strtotime($record['expires_at']) <= time()) {
            return false;
        }

        return true;
    }
}
