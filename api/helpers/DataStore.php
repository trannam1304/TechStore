<?php
/**
 * TechStore - Dual-Mode Data Access Store
 * Tự động ưu tiên kết nối MySQL qua PDO, nếu CSDL MySQL chưa sẵn sàng thì fallback mượt mà qua database/data.json
 */

namespace Api\Helpers;

use Config\Database;
use PDO;
use Exception;

class DataStore {
    private static ?PDO $pdo = null;
    private static ?bool $hasPdo = null;
    private static string $jsonPath = __DIR__ . '/../../database/data.json';

    /**
     * Kiểm tra và lấy PDO nếu khả dụng
     */
    public static function getPdo(): ?PDO {
        if (self::$hasPdo === false) {
            return null;
        }

        if (self::$pdo === null) {
            try {
                self::$pdo = Database::getConnection();
                self::$hasPdo = true;
            } catch (Exception $e) {
                self::$hasPdo = false;
                self::$pdo = null;
            }
        }

        return self::$pdo;
    }

    /**
     * Đọc toàn bộ data từ data.json
     */
    public static function getJsonData(): array {
        if (file_exists(self::$jsonPath)) {
            $json = file_get_contents(self::$jsonPath);
            $data = json_decode($json, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    /**
     * Ghi cập nhật lại data.json
     */
    public static function saveJsonData(array $data): void {
        file_put_contents(self::$jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    /**
     * Tìm user theo email
     */
    public static function findUserByEmail(string $email): ?array {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                return $user ?: null;
            } catch (Exception $e) {
                // fallback json
            }
        }

        $data = self::getJsonData();
        foreach ($data['users'] ?? [] as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                return $u;
            }
        }
        return null;
    }

    /**
     * Tìm user theo id
     */
    public static function findUserById(int $id): ?array {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                return $user ?: null;
            } catch (Exception $e) {
                // fallback json
            }
        }

        $data = self::getJsonData();
        foreach ($data['users'] ?? [] as $u) {
            if ((int)$u['id'] === $id) {
                return $u;
            }
        }
        return null;
    }

    /**
     * Tạo user mới
     */
    public static function createUser(array $userData): array {
        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $sql = "INSERT INTO users (name, email, password_hash, role, phone, address, status, created_at)
                        VALUES (:name, :email, :password_hash, :role, :phone, :address, :status, :created_at)";
                $stmt = $pdo->prepare($sql);
                $now = date('Y-m-d H:i:s');
                $stmt->execute([
                    ':name'          => $userData['name'],
                    ':email'         => $userData['email'],
                    ':password_hash' => $userData['password_hash'],
                    ':role'          => $userData['role'] ?? 'customer',
                    ':phone'         => $userData['phone'] ?? null,
                    ':address'       => $userData['address'] ?? null,
                    ':status'        => $userData['status'] ?? 'active',
                    ':created_at'    => $now
                ]);
                $userData['id'] = (int)$pdo->lastInsertId();
                $userData['created_at'] = $now;
                return $userData;
            } catch (Exception $e) {
                // fallback json
            }
        }

        $data = self::getJsonData();
        $nextId = 1;
        foreach ($data['users'] ?? [] as $u) {
            if ($u['id'] >= $nextId) $nextId = $u['id'] + 1;
        }
        $userData['id'] = $nextId;
        $userData['created_at'] = date('Y-m-d H:i:s');
        $data['users'][] = $userData;
        self::saveJsonData($data);
        return $userData;
    }

    /**
     * Lưu hash của refresh token vào CSDL
     */
    public static function saveRefreshToken(int $userId, string $token, int $expiresIn): void {
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);

        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token_hash, expires_at, revoked, created_at)
                                       VALUES (?, ?, ?, 0, NOW())");
                $stmt->execute([$userId, $tokenHash, $expiresAt]);
                return;
            } catch (Exception $e) {
                // fallback json
            }
        }

        $data = self::getJsonData();
        $data['refresh_tokens'][] = [
            'id' => count($data['refresh_tokens'] ?? []) + 1,
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'revoked' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        self::saveJsonData($data);
    }

    /**
     * Thu hồi refresh token khi đăng xuất
     */
    public static function revokeRefreshToken(string $token): void {
        $tokenHash = hash('sha256', $token);

        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE refresh_tokens SET revoked = 1 WHERE token_hash = ?");
                $stmt->execute([$tokenHash]);
                return;
            } catch (Exception $e) {
                // fallback json
            }
        }

        $data = self::getJsonData();
        if (isset($data['refresh_tokens'])) {
            foreach ($data['refresh_tokens'] as &$rf) {
                if ($rf['token_hash'] === $tokenHash) {
                    $rf['revoked'] = 1;
                }
            }
            self::saveJsonData($data);
        }
    }

    /**
     * Kiểm tra refresh token có hợp lệ trong DB không
     */
    public static function isRefreshTokenValid(string $token): bool {
        $tokenHash = hash('sha256', $token);

        $pdo = self::getPdo();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM refresh_tokens WHERE token_hash = ? AND revoked = 0 AND expires_at > NOW() LIMIT 1");
                $stmt->execute([$tokenHash]);
                return (bool)$stmt->fetch();
            } catch (Exception $e) {
                // fallback json
            }
        }

        $data = self::getJsonData();
        $now = date('Y-m-d H:i:s');
        foreach ($data['refresh_tokens'] ?? [] as $rf) {
            if ($rf['token_hash'] === $tokenHash && (int)$rf['revoked'] === 0 && $rf['expires_at'] > $now) {
                return true;
            }
        }
        return false;
    }
}
