<?php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/AuditService.php';

class PedidoController
{
    private Pedido $pedidos;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('pedidos.view');
        $this->pedidos = new Pedido();
        $_SESSION['pedidos_flash'] = $_SESSION['pedidos_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $this->create();
            } elseif ($action === 'update') {
                $this->update();
            } elseif ($action === 'estado') {
                $this->updateEstado();
            } elseif ($action === 'delete') {
                $this->delete();
            }
            header('Location: apps-pedidos.php');
            exit;
        }

        $flash = $_SESSION['pedidos_flash'];
        $_SESSION['pedidos_flash'] = [];
        return [
            'pedidos' => $this->pedidos->all(),
            'clientes' => $this->pedidos->clientesSource(),
            'vendedores' => $this->pedidos->vendedoresSource(),
            'cotizaciones' => $this->pedidos->cotizacionesSource(),
            'cotizaciones_aprobadas' => $this->pedidos->cotizacionesAceptadasPendientesBodega(),
            'estados_operacion' => Pedido::ESTADOS_OPERACION,
            'flash' => $flash,
        ];
    }

    private function create(): void
    {
        $payload = $this->payload();
        if (!$payload) {
            return;
        }

        $pedidoId = $this->pedidos->create($payload);
        AuditService::log('crear', 'pedidos', $pedidoId, 'Pedido creado', null, $payload);
        $this->flash('success', 'Pedido creado correctamente.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('warning', 'Pedido inválido.');
            return;
        }

        $payload = $this->payload();
        if (!$payload) {
            return;
        }

        $ok = $this->pedidos->update($id, $payload);
        if ($ok) {
            AuditService::log('editar', 'pedidos', $id, 'Pedido actualizado', null, $payload);
            $this->flash('success', 'Pedido actualizado.');
            return;
        }

        $this->flash('danger', 'No fue posible actualizar el pedido.');
    }

    private function updateEstado(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? 'pendiente';
        $allowed = Pedido::ESTADOS_OPERACION;

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

    private function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('warning', 'Pedido inválido.');
            return;
        }
        $this->pedidos->delete($id);
        AuditService::log('eliminar', 'pedidos', $id, 'Pedido eliminado');
        $this->flash('success', 'Pedido eliminado.');
    }

    private function payload(): ?array
    {
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);
        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $estado = $_POST['estado'] ?? 'pendiente';
        $total = (float) ($_POST['total'] ?? 0);
        $fecha = trim($_POST['fecha'] ?? '');
        $contactoNombre = trim($_POST['contacto_nombre'] ?? '');
        $contactoEmail = trim($_POST['contacto_email'] ?? '');
        $contactoTelefono = trim($_POST['contacto_telefono'] ?? '');
        $direccionEntrega = trim($_POST['direccion_entrega'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        $allowed = Pedido::ESTADOS_OPERACION;
        if ($clienteId <= 0 || $total < 0 || !in_array($estado, $allowed, true)) {
            $this->flash('warning', 'Completa cliente, estado y total válido.');
            return null;
        }

        if ($fecha === '') {
            $fecha = date('Y-m-d H:i:s');
        } elseif (strlen($fecha) === 16) {
            $fecha .= ':00';
        }

        return [
            'cliente_id' => $clienteId,
            'cotizacion_id' => $cotizacionId > 0 ? $cotizacionId : null,
            'usuario_id' => $usuarioId > 0 ? $usuarioId : null,
            'estado' => $estado,
            'total' => $total,
            'fecha' => $fecha,
            'contacto_nombre' => $contactoNombre,
            'contacto_email' => $contactoEmail,
            'contacto_telefono' => $contactoTelefono,
            'direccion_entrega' => $direccionEntrega,
            'observaciones' => $observaciones,
        ];
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['pedidos_flash'][] = ['type' => $type, 'message' => $message];
    }
}
