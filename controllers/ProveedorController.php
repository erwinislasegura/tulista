<?php

require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/AuditService.php';

class ProveedorController
{
    private $proveedores ;

    public function __construct()
    {
        AuthService::startSession();
        $this->proveedores = new Proveedor();
        $_SESSION['proveedores_flash'] = $_SESSION['proveedores_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        AuthorizationService::requirePermission('proveedores.view');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            AuthorizationService::requirePermission('proveedores.manage');

            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $this->create();
            } elseif ($action === 'update') {
                $this->update();
            } elseif ($action === 'delete') {
                $this->delete();
            }

            header('Location: apps-proveedores.php');
            exit;
        }

        $flash = $_SESSION['proveedores_flash'];
        $_SESSION['proveedores_flash'] = [];

        return [
            'can_manage' => AuthorizationService::can('proveedores.manage'),
            'proveedores' => $this->proveedores->all(),
            'flash' => $flash,
        ];
    }

    private function create(): void
    {
        $payload = $this->payload();
        if ($payload === null) {
            return;
        }

        if ($this->proveedores->existsByRut($payload['rut'])) {
            $this->flash('warning', 'Ya existe un proveedor con ese RUT.');
            return;
        }

        try {
            $ok = $this->proveedores->create($payload);
            if (!$ok) {
                $this->flash('danger', 'No fue posible crear el proveedor. Intenta nuevamente.');
                return;
            }
            AuditService::log('crear', 'proveedores', null, 'Proveedor creado', null, $payload);
            $this->flash('success', 'Proveedor creado correctamente.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Error al crear proveedor. Verifica RUT único y datos ingresados.');
        }
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger', 'Proveedor inválido.');
            return;
        }

        $payload = $this->payload();
        if ($payload === null) {
            return;
        }

        if ($this->proveedores->existsByRut($payload['rut'], $id)) {
            $this->flash('warning', 'El RUT está asignado a otro proveedor.');
            return;
        }

        try {
            $ok = $this->proveedores->update($id, $payload);
            if (!$ok) {
                $this->flash('danger', 'No fue posible actualizar el proveedor.');
                return;
            }
            AuditService::log('editar', 'proveedores', $id, 'Proveedor actualizado', null, $payload);
            $this->flash('success', 'Proveedor actualizado.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Error al actualizar proveedor.');
        }
    }

    private function delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('danger', 'Proveedor inválido.');
            return;
        }

        try {
            $ok = $this->proveedores->delete($id);
            if (!$ok) {
                $this->flash('danger', 'No fue posible eliminar el proveedor.');
                return;
            }
            AuditService::log('eliminar', 'proveedores', $id, 'Proveedor eliminado');
            $this->flash('success', 'Proveedor eliminado.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Error al eliminar proveedor.');
        }
    }

    private function payload(): ?array
    {
        $rut = $this->normalizeRut((string) ($_POST['rut'] ?? ''));
        $razonSocial = trim((string) ($_POST['razon_social'] ?? ''));
        $nombreContacto = trim((string) ($_POST['nombre_contacto'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $direccion = trim((string) ($_POST['direccion'] ?? ''));
        $comuna = trim((string) ($_POST['comuna'] ?? ''));
        $plazoPagoDias = (int) ($_POST['plazo_pago_dias'] ?? 0);
        $observaciones = trim((string) ($_POST['observaciones'] ?? ''));
        $estado = !empty($_POST['estado']) ? 1 : 0;

        if ($rut === '' || $razonSocial === '') {
            $this->flash('warning', 'RUT y razón social son obligatorios.');
            return null;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('warning', 'El email de proveedor no es válido.');
            return null;
        }

        if ($plazoPagoDias < 0 || $plazoPagoDias > 365) {
            $this->flash('warning', 'El plazo de pago debe estar entre 0 y 365 días.');
            return null;
        }

        return [
            'rut' => $rut,
            'razon_social' => $razonSocial,
            'nombre_contacto' => $nombreContacto,
            'email' => $email,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'comuna' => $comuna,
            'plazo_pago_dias' => $plazoPagoDias,
            'observaciones' => $observaciones,
            'estado' => $estado,
        ];
    }

    private function normalizeRut(string $rut): string
    {
        $rut = strtoupper(trim($rut));
        $rut = preg_replace('/\s+/', '', $rut) ?? '';
        return preg_replace('/[^0-9K\-.]/', '', $rut) ?? '';
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['proveedores_flash'][] = ['type' => $type, 'message' => $message];
    }
}
