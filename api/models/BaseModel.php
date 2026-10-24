<?php
/**
 * TechStore - Base Model
 * Wraps PDO connection and provides secure prepared statement execution
 */

namespace Api\Models;

require_once __DIR__ . '/../../config/database.php';

use Config\Database;
use PDO;

class BaseModel {
    protected ?PDO $db = null;

    public function __construct() {
        try {
            $this->db = Database::getConnection();
        } catch (\Throwable $e) {
            $this->db = null;
        }
    }

    public function query(string $sql, array $params = []): array {
        if (!$this->db) return [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function queryOne(string $sql, array $params = []): ?array {
        if (!$this->db) return null;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function execute(string $sql, array $params = []): bool {
        if (!$this->db) return false;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId(): int {
        return $this->db ? (int)$this->db->lastInsertId() : 0;
    }
}
