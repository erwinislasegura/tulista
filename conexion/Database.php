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

        $credentials = [[
            'user' => getenv('DB_USER') ?: getenv('DB_USERNAME') ?: getenv('MYSQL_USER') ?: $dbName,
            'pass' => getenv('DB_PASS') ?: getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '',
            'source' => 'env/default',
        ]];

        // Compatibilidad con despliegues antiguos.
        $credentials[] = ['user' => 'tulista_app', 'pass' => 'Eisla1245...', 'source' => 'legacy'];

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);
        $lastError = null;

        foreach ($credentials as $candidate) {
            try {
                self::$instance = new PDO($dsn, $candidate['user'], $candidate['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                return self::$instance;
            } catch (Throwable $e) {
                $lastError = $e;
                error_log(sprintf('[Database] Falló credencial "%s" para %s:%s/%s: %s', $candidate['source'], $host, $port, $dbName, $e->getMessage()));
            }
        }

        $message = sprintf('No se pudo conectar a MySQL (%s:%s/%s).', $host, $port, $dbName);
        throw new RuntimeException($message, 0, $lastError);

        return self::$instance;
    }
}
