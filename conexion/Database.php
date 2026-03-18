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

        $myCnfCredentials = self::readMyCnfCredentials();
        if ($myCnfCredentials !== null) {
            $credentials[] = [
                'user' => $myCnfCredentials['user'],
                'pass' => $myCnfCredentials['pass'],
                'source' => '.my.cnf',
            ];
        }

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

    private static function readMyCnfCredentials(): ?array
    {
        $home = (string) (getenv('HOME') ?: '');
        if ($home === '') {
            return null;
        }

        $path = rtrim($home, '/\\') . '/.my.cnf';
        if (!is_readable($path)) {
            return null;
        }

        $content = (string) @file_get_contents($path);
        if ($content === '') {
            return null;
        }

        $user = null;
        $pass = null;

        foreach (preg_split('/\r\n|\r|\n/', $content) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';' || $line[0] === '[') {
                continue;
            }

            if (stripos($line, 'user=') === 0) {
                $user = trim(substr($line, 5));
            } elseif (stripos($line, 'password=') === 0) {
                $pass = trim(substr($line, 9));
            }
        }

        if ($user === null || $pass === null) {
            return null;
        }

        return ['user' => $user, 'pass' => $pass];
    }
}
