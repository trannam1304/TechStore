<?php
/**
 * TechStore - Base Model
 * Wraps PDO connection and provides secure prepared statement execution
 */

namespace Api\Models;

use Config\Database;
use PDO;
use PDOStatement;

class BaseModel {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->ensureSchema();
    }

    protected function ensureSchema(): void {
        static $checked = false;
        if ($checked) return;

        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'products'");
            if ($stmt->rowCount() === 0) {
                // Table doesn't exist, auto-import schema.sql
                $schemaFile = dirname(__DIR__, 2) . '/database/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $this->db->exec($sql);
                }
            }
            $checked = true;
        } catch (\Throwable $e) {
            // Ignore if check fails
        }
    }

    public function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function execute(string $sql, array $params = []): bool {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId(): int {
        return (int)$this->db->lastInsertId();
    }

    public function beginTransaction(): bool {
        return $this->db->beginTransaction();
    }

    public function commit(): bool {
        return $this->db->commit();
    }

    public function rollBack(): bool {
        return $this->db->rollBack();
    }
}
