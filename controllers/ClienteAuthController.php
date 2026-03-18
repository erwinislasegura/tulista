<?php

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../services/AuthService.php';

class ClienteAuthController
{
    private $clientes;

    public function __construct()
    {
        $this->clientes = new Cliente();
        AuthService::startSession();
    }

    public function processPublicAccess(): array
    {
        $response = [
            'login_error' => null,
            'register_error' => null,
            'register_success' => null,
            'reset_error' => null,
            'reset_success' => null,
            'old' => [
                'login_rut' => '',
                'register_rut' => '',
                'register_nombre' => '',
                'register_email' => '',
                'register_telefono' => '',
                'reset_rut' => '',
                'reset_email' => '',
            ],
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $response;
        }

        $action = (string) ($_POST['action'] ?? 'login_cliente');

        if ($action === 'register_cliente') {
            return array_merge($response, $this->register());
        }

        if ($action === 'reset_password_cliente') {
            return array_merge($response, $this->resetPasswordByRutAndEmail());
        }

        return array_merge($response, $this->loginResponse('cliente-portal.php'));
    }

    public function login(): ?string
    {
        return $this->attemptLogin('cliente-portal.php');
    }

    public function attemptLogin(string $redirectTo = 'cliente-portal.php'): ?string
    {
        $result = $this->loginResponse($redirectTo, true);
        return $result['login_error'];
    }

    private function loginResponse(string $redirectTo = 'cliente-portal.php', bool $legacyMode = false): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['login_error' => null];
        }

        $action = (string) ($_POST['action'] ?? 'login_cliente');
        if (!$legacyMode && $action !== 'login_cliente') {
            return ['login_error' => null];
        }

        $rut = $this->normalizeRut((string) ($_POST['rut'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($rut === '' || $password === '') {
            return ['login_error' => 'Debes ingresar RUT y contraseña.'];
        }

        $cliente = $this->clientes->findByRut($rut);
        if (!$cliente || (int) $cliente['estado'] !== 1 || !password_verify($password, (string) $cliente['password'])) {
            return [
                'login_error' => 'RUT o contraseña inválidos.',
                'old' => ['login_rut' => $rut],
            ];
        }

        AuthService::loginCliente($cliente);
        header('Location: ' . $redirectTo);
        exit;
    }

    private function register(): array
    {
        $rut = $this->normalizeRut((string) ($_POST['register_rut'] ?? ''));
        $nombre = trim((string) ($_POST['register_nombre'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['register_email'] ?? '')));
        $telefono = trim((string) ($_POST['register_telefono'] ?? ''));
        $password = (string) ($_POST['register_password'] ?? '');
        $passwordConfirm = (string) ($_POST['register_password_confirm'] ?? '');

        $old = [
            'register_rut' => $rut,
            'register_nombre' => $nombre,
            'register_email' => $email,
            'register_telefono' => $telefono,
        ];

        if ($rut === '' || $nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return ['register_error' => 'Completa RUT, nombre, email y contraseña.', 'old' => $old];
        }

        if (strlen($password) < 6) {
            return ['register_error' => 'La contraseña debe tener al menos 6 caracteres.', 'old' => $old];
        }

        if ($password !== $passwordConfirm) {
            return ['register_error' => 'La confirmación de contraseña no coincide.', 'old' => $old];
        }

        if ($this->clientes->findByRut($rut)) {
            return ['register_error' => 'El RUT ya está registrado.', 'old' => $old];
        }

        $payload = [
            'rut' => $rut,
            'nombre' => $nombre,
            'empresa' => '',
            'giro' => '',
            'comuna' => '',
            'email' => $email,
            'telefono' => $telefono,
            'direccion' => '',
            'tipo_cliente' => 'minorista',
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'token' => bin2hex(random_bytes(16)),
            'estado' => 1,
        ];

        try {
            $this->clientes->create($payload);
        } catch (Throwable $e) {
            error_log('[cliente-register] ' . $e->getMessage());
            return ['register_error' => 'No fue posible crear la cuenta. Intenta nuevamente.', 'old' => $old];
        }

        return [
            'register_success' => 'Cuenta creada correctamente. Ya puedes iniciar sesión.',
            'old' => [],
        ];
    }

    private function resetPasswordByRutAndEmail(): array
    {
        $rut = $this->normalizeRut((string) ($_POST['reset_rut'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['reset_email'] ?? '')));
        $password = (string) ($_POST['reset_password'] ?? '');
        $passwordConfirm = (string) ($_POST['reset_password_confirm'] ?? '');

        $old = [
            'reset_rut' => $rut,
            'reset_email' => $email,
        ];

        if ($rut === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return ['reset_error' => 'Completa RUT, email y nueva contraseña.', 'old' => $old];
        }

        if (strlen($password) < 6) {
            return ['reset_error' => 'La nueva contraseña debe tener al menos 6 caracteres.', 'old' => $old];
        }

        if ($password !== $passwordConfirm) {
            return ['reset_error' => 'La confirmación de contraseña no coincide.', 'old' => $old];
        }

        $cliente = $this->clientes->findByRut($rut);
        if (!$cliente || mb_strtolower((string) ($cliente['email'] ?? '')) !== $email) {
            return ['reset_error' => 'No encontramos un cliente activo con ese RUT y email.', 'old' => $old];
        }

        $updated = $this->clientes->updatePassword((int) $cliente['id'], password_hash($password, PASSWORD_DEFAULT));
        if (!$updated) {
            return ['reset_error' => 'No pudimos actualizar la contraseña.', 'old' => $old];
        }

        return ['reset_success' => 'Contraseña actualizada. Ya puedes iniciar sesión.'];
    }

    private function normalizeRut(string $rut): string
    {
        $rut = strtoupper(trim($rut));
        $rut = preg_replace('/\s+/', '', $rut) ?? '';
        return preg_replace('/[^0-9K\-.]/', '', $rut) ?? '';
    }

    public function loginByToken(string $token): ?array
    {
        return $this->clientes->findByToken($token);
    }

    public function logoutTo(string $redirectTo = 'cliente-login.php'): void
    {
        AuthService::logoutCliente();
        header('Location: ' . $redirectTo);
        exit;
    }

    public function logout(): void
    {
        $this->logoutTo('cliente-login.php');
    }
}
