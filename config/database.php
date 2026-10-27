<?php
declare(strict_types=1);

/**
 * Classe Database — Singleton de Connexion PDO
 * Centralise l'accès à la base de données MySQL 8.4 via l'extension PDO en UTF-8 strict.
 */
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $port = getenv('DB_PORT') ?: '3306';
            $db   = getenv('DB_NAME') ?: 'gestion_pedagogique';
            $user = getenv('DB_USER') ?: 'app_user';
            $pass = getenv('DB_PASS') ?: 'app_password';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw $e;
            }
        }

        return self::$instance;
    }
}
