<?php

class Database
{
    private static $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $dbName = getenv('DB_NAME') ?: 'tulista_app';
        $username = getenv('DB_USER') ?: 'tulista_app';
        $password = getenv('DB_PASS') ?: 'Eisla1245...';

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
