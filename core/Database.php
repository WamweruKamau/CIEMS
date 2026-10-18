<?php
/**
 * Database Singleton Wrapper using PHP PDO
 * Implements prepared statements for complete SQL Injection defense.
 */

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require dirname(__DIR__) . '/config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                die("Database connection failed: " . htmlspecialchars($e->getMessage()));
            }
        }
        return self::$instance;
    }
}
