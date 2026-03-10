<?php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuditService.php';

class PedidoController
{
    private Pedido $pedidos;

    public function __construct()
    {
        AuthService::startSession();
        AuthService::requireRole(['admin', 'supervisor', 'vendedor', 'bodega']);
        $this->pedidos = new Pedido();
        $_SESSION['pedidos_flash'] = $_SESSION['pedidos_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'estado') {
            $this->updateEstado();
            header('Location: apps-pedidos.php');
            exit;
        }

        $flash = $_SESSION['pedidos_flash'];
        $_SESSION['pedidos_flash'] = [];
        return ['pedidos' => $this->pedidos->all(), 'flash' => $flash];
    }

    private function updateEstado(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? 'pendiente';
        $allowed = ['pendiente', 'preparacion', 'enviado', 'entregado', 'cancelado'];

        if ($id <= 0 || !in_array($estado, $allowed, true)) {
            $this->flash('warning', 'Datos de actualización inválidos.');
            return;
        }

        $ok = $this->pedidos->updateEstado($id, $estado);
        if ($ok) {
            AuditService::log('actualizar_estado', 'pedidos', $id, "Estado de pedido actualizado a {$estado}");
            $this->flash('success', 'Estado actualizado correctamente.');
            return;
        }

        $this->flash('danger', 'No fue posible actualizar el estado del pedido.');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['pedidos_flash'][] = ['type' => $type, 'message' => $message];
    }
}
