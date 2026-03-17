<?php

class AuthService
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $httpsHeader = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
            $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
            $serverPort = (string) ($_SERVER['SERVER_PORT'] ?? '');
            $isSecure = ($httpsHeader !== '' && $httpsHeader !== 'off') || str_contains($forwardedProto, 'https') || $serverPort === '443';

            $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
            $cookieDomain = $host !== '' ? explode(':', $host)[0] : '';

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => $cookieDomain,
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
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
