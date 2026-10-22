<?php
/**
 * TechStore - PDO Database Connection Manager
 * Implements Singleton pattern with secure PDO attributes
 */

namespace Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = Config::get('database.host', '127.0.0.1');
        $port = Config::get('database.port', '3306');
        $dbName = Config::get('database.name', 'techstore');
        $user = Config::get('database.user', 'root');
        $pass = Config::get('database.password', '');
        $charset = Config::get('database.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci"
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
            return self::$instance;
        } catch (PDOException $e) {
            // Attempt to create database if it doesn't exist
            if ($e->getCode() === 1049) {
                try {
                    $rootDsn = "mysql:host={$host};port={$port};charset={$charset}";
                    $rootPdo = new PDO($rootDsn, $user, $pass, $options);
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                    return self::$instance;
                } catch (PDOException $ex) {
                    throw $ex;
                }
            }
            throw $e;
        }
    }

    public static function hasConnection(): bool {
        try {
            self::getConnection();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
