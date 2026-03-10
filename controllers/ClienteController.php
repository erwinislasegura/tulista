<?php

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuditService.php';

class ClienteController
{
    private Cliente $clientes;

    public function __construct()
    {
        AuthService::startSession();
        $this->clientes = new Cliente();
        $_SESSION['clientes_flash'] = $_SESSION['clientes_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        AuthService::requireRole(['admin', 'supervisor', 'vendedor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $this->create();
            } elseif ($action === 'update') {
                $this->update();
            } elseif ($action === 'delete') {
                $this->delete();
            }
            header('Location: apps-clientes.php');
            exit;
        }

        $clienteSeleccionado = null;
        $cotizaciones = [];
        $pedidos = [];
        $clienteId = (int) ($_GET['cliente_id'] ?? 0);
        if ($clienteId > 0) {
            $clienteSeleccionado = $this->clientes->find($clienteId);
            if ($clienteSeleccionado) {
                $cotizaciones = $this->clientes->cotizacionesByCliente($clienteId);
                $pedidos = $this->clientes->pedidosByCliente($clienteId);
            }
        }

        $flash = $_SESSION['clientes_flash'];
        $_SESSION['clientes_flash'] = [];

        return [
            'clientes' => $this->clientes->all(),
            'flash' => $flash,
            'cliente_seleccionado' => $clienteSeleccionado,
            'cotizaciones_cliente' => $cotizaciones,
            'pedidos_cliente' => $pedidos,
        ];
    }

    private function create(): void
    {
        $payload = $this->payload(true);
        if (!$payload) {
            return;
        }

        $this->clientes->create($payload);
        AuditService::log('crear', 'clientes', null, 'Cliente creado', null, $payload);
        $this->flash('success', 'Cliente creado.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger', 'Cliente inválido.');
            return;
        }

        $payload = $this->payload(false);
        if (!$payload) {
            return;
        }

        $this->clientes->update($id, $payload);
        AuditService::log('editar', 'clientes', $id, 'Cliente actualizado', null, $payload);
        $this->flash('success', 'Cliente actualizado.');
    }

    private function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger', 'Cliente inválido.');
            return;
        }

        $this->clientes->delete($id);
        AuditService::log('eliminar', 'clientes', $id, 'Cliente eliminado');
        $this->flash('success', 'Cliente eliminado.');
    }

    private function payload(bool $passwordRequired): ?array
    {
        $rut = trim($_POST['rut'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $password = $_POST['password'] ?? '';
        $token = trim($_POST['token'] ?? bin2hex(random_bytes(5)));
        $estado = !empty($_POST['estado']) ? 1 : 0;

        if ($rut === '' || $nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('warning', 'RUT, nombre y email válido son obligatorios.');
            return null;
        }

        if ($passwordRequired && strlen($password) < 6) {
            $this->flash('warning', 'La contraseña del cliente debe tener al menos 6 caracteres.');
            return null;
        }

        return [
            'rut' => $rut,
            'nombre' => $nombre,
            'empresa' => $empresa,
            'email' => $email,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'password' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '',
            'token' => $token,
            'estado' => $estado,
        ];
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['clientes_flash'][] = ['type' => $type, 'message' => $message];
    }
}
