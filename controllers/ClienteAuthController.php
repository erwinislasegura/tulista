<?php

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../services/AuthService.php';

class ClienteAuthController
{
    private Cliente $clientes;

    public function __construct()
    {
        $this->clientes = new Cliente();
        AuthService::startSession();
    }

    public function login(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $rut = trim($_POST['rut'] ?? '');
        $password = $_POST['password'] ?? '';
        $cliente = $this->clientes->findByRut($rut);

        if (!$cliente || (int) $cliente['estado'] !== 1 || !password_verify($password, $cliente['password'])) {
            return 'RUT o contraseña inválidos.';
        }

        AuthService::loginCliente($cliente);
        header('Location: cliente-portal.php');
        exit;
    }

    public function loginByToken(string $token): ?array
    {
        return $this->clientes->findByToken($token);
    }

    public function logout(): void
    {
        AuthService::logoutCliente();
        header('Location: cliente-login.php');
        exit;
    }
}
