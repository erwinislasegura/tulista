<?php

class AuthService
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $httpsHeader = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
            $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
            $serverPort = (string) ($_SERVER['SERVER_PORT'] ?? '');
            $isSecure = ($httpsHeader !== '' && $httpsHeader !== 'off') || strpos($forwardedProto, 'https') !== false || $serverPort === '443';

            $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
            $cookieDomain = self::cookieDomainFromHost($host);

            $cookieParams = [
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ];

            if ($cookieDomain !== null) {
                $cookieParams['domain'] = $cookieDomain;
            }

            session_set_cookie_params($cookieParams);
            session_start();
        }
    }

    private static function cookieDomainFromHost(string $host): ?string
    {
        $host = strtolower(trim(explode(':', $host)[0] ?? ''));

        if ($host === '' || filter_var($host, FILTER_VALIDATE_IP) || $host === 'localhost') {
            return null;
        }

        // Host-only cookie by defecto para evitar rechazos por dominio inválido
        // en proxys con host no esperado (p.ej. encabezados alterados).
        return null;
    }

    public static function loginUser(array $user): void
    {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'rol' => $user['rol'],
        ];
        $_SESSION['user'] = true;
    }

    public static function logoutUser(): void
    {
        self::startSession();
        unset($_SESSION['auth_user']);
        $_SESSION['user'] = false;
        session_regenerate_id(true);
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['auth_user'] ?? null;
    }

    public static function requireRole(array $allowedRoles): void
    {
        $user = self::user();
        if (!$user || !in_array($user['rol'], $allowedRoles, true)) {
            http_response_code(403);
            exit('Acceso no autorizado.');
        }
    }

    public static function loginCliente(array $cliente): void
    {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['auth_cliente'] = [
            'id' => (int) ($cliente['id'] ?? 0),
            'rut' => $cliente['rut'] ?? '',
            'nombre' => $cliente['nombre'] ?? '',
            'email' => $cliente['email'] ?? '',
            'empresa' => $cliente['empresa'] ?? '',
            'telefono' => $cliente['telefono'] ?? '',
        ];
    }

    public static function cliente(): ?array
    {
        self::startSession();
        return $_SESSION['auth_cliente'] ?? null;
    }

    public static function logoutCliente(): void
    {
        self::startSession();
        unset($_SESSION['auth_cliente']);
        session_regenerate_id(true);
    }
}
