<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/AuthService.php';

class UsuarioController
{
    private Usuario $usuarios;

    public function __construct()
    {
        AuthService::startSession();
        $this->usuarios = new Usuario();
        $_SESSION['usuarios_flash'] = $_SESSION['usuarios_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $this->create();
            } elseif ($action === 'update') {
                $this->update();
            } elseif ($action === 'delete') {
                $this->delete();
            }
            header('Location: apps-usuarios.php');
            exit;
        }

        $flash = $_SESSION['usuarios_flash'];
        $_SESSION['usuarios_flash'] = [];

        return ['usuarios' => $this->usuarios->all(), 'flash' => $flash, 'auth' => AuthService::user()];
    }

    public function login(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $this->usuarios->findByEmail($email);
        if (!$user || (int) $user['estado'] !== 1 || !password_verify($password, $user['password'])) {
            return 'Credenciales inválidas.';
        }

        AuthService::loginUser($user);
        header('Location: apps-usuarios.php');
        exit;
    }

    public function logout(): void
    {
        AuthService::logoutUser();
        header('Location: auth-signin.php');
        exit;
    }

    private function create(): void
    {
        $payload = $this->payloadFromPost(true);
        if (!$payload) {
            return;
        }

        if ($this->usuarios->findByEmail($payload['email'])) {
            $this->flash('warning', 'Email ya registrado.');
            return;
        }

        $this->usuarios->create($payload);
        $this->flash('success', 'Usuario creado correctamente.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger', 'Usuario inválido.');
            return;
        }

        $payload = $this->payloadFromPost(false);
        if (!$payload) {
            return;
        }

        $this->usuarios->update($id, $payload);
        $this->flash('success', 'Usuario actualizado.');
    }

    private function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger', 'Usuario inválido.');
            return;
        }

        $this->usuarios->delete($id);
        $this->flash('success', 'Usuario eliminado.');
    }

    private function payloadFromPost(bool $requiredPassword): ?array
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rol = ($_POST['rol'] ?? 'vendedor') === 'admin' ? 'admin' : 'vendedor';
        $estado = !empty($_POST['estado']) ? 1 : 0;

        if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('warning', 'Nombre y email válido son obligatorios.');
            return null;
        }

        if ($requiredPassword && strlen($password) < 6) {
            $this->flash('warning', 'La contraseña debe tener al menos 6 caracteres.');
            return null;
        }

        return [
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '',
            'rol' => $rol,
            'estado' => $estado,
        ];
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['usuarios_flash'][] = ['type' => $type, 'message' => $message];
    }
}
