<?php

class AuthService
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
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
