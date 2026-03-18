<?php

class Database
{
    private static $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: '3306';
        $dbName = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'tulista';
        $username = getenv('DB_USER') ?: getenv('DB_USERNAME') ?: getenv('MYSQL_USER') ?: $dbName;
        $password = getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);

        try {
            self::$instance = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Throwable $e) {
            $message = sprintf('No se pudo conectar a MySQL (%s:%s/%s).', $host, $port, $dbName);
            error_log('[Database] ' . $message . ' ' . $e->getMessage());
            throw new RuntimeException($message, 0, $e);
        }

        return self::$instance;
    }
}
