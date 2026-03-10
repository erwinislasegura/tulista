<?php

require_once __DIR__ . '/../models/Cotizacion.php';
require_once __DIR__ . '/../models/CotizacionDetalle.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuditService.php';

class CotizacionController
{
    private Cotizacion $cotizaciones;
    private CotizacionDetalle $detalles;
    private ProductModel $productos;
    private Cliente $clientes;

    public function __construct()
    {
        AuthService::startSession();
        $this->cotizaciones = new Cotizacion();
        $this->detalles = new CotizacionDetalle();
        $this->productos = new ProductModel();
        $this->clientes = new Cliente();
        $_SESSION['cotizaciones_flash'] = $_SESSION['cotizaciones_flash'] ?? [];
    }

    public function handlePortalRequest(?int $forcedClienteId = null): array
    {
        $cliente = AuthService::cliente();
        $clienteId = $forcedClienteId ?: ($cliente['id'] ?? 0);
        if ($clienteId <= 0) {
            http_response_code(403);
            exit('Debes iniciar sesión como cliente.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear_cotizacion') {
            $this->crearCotizacion($clienteId);
            header('Location: cliente-portal.php');
            exit;
        }

        $flash = $_SESSION['cotizaciones_flash'];
        $_SESSION['cotizaciones_flash'] = [];

        return [
            'productos' => $this->productos->catalog(),
            'cotizaciones' => $this->cotizaciones->all($clienteId),
            'flash' => $flash,
        ];
    }

    public function handleAdminRequest(): array
    {
        AuthService::requireRole(['admin', 'supervisor', 'vendedor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'cambiar_estado') {
                $id = (int) ($_POST['cotizacion_id'] ?? 0);
                $estado = $_POST['estado'] ?? 'borrador';
                $allowed = ['borrador', 'enviada', 'aprobada', 'rechazada'];
                if ($id > 0 && in_array($estado, $allowed, true)) {
                    $this->cotizaciones->updateEstado($id, $estado);
                    AuditService::log('editar', 'cotizaciones', $id, "Cotización actualizada a estado {$estado}");
                }
            } elseif ($action === 'crear_admin') {
                $this->crearCotizacion((int) ($_POST['cliente_id'] ?? 0));
            } elseif ($action === 'eliminar') {
                $id = (int) ($_POST['cotizacion_id'] ?? 0);
                if ($id > 0) {
                    $this->cotizaciones->delete($id);
                    AuditService::log('eliminar', 'cotizaciones', $id, 'Cotización eliminada');
                }
            }
            header('Location: apps-cotizaciones.php');
            exit;
        }

        $flash = $_SESSION['cotizaciones_flash'];
        $_SESSION['cotizaciones_flash'] = [];
        return [
            'cotizaciones' => $this->cotizaciones->all(),
            'productos' => $this->productos->catalog(),
            'clientes' => $this->clientes->all(),
            'flash' => $flash,
        ];
    }

    private function crearCotizacion(int $clienteId): void
    {
        $items = $_POST['items'] ?? [];
        if ($clienteId <= 0) {
            $this->flash('warning', 'Debes seleccionar un cliente.');
            return;
        }
        if (!is_array($items) || empty($items)) {
            $this->flash('warning', 'Debes agregar al menos un producto.');
            return;
        }

        $usuario = AuthService::user();
        $cotizacionId = $this->cotizaciones->create($clienteId, (int) ($usuario['id'] ?? 0), 0);
        $productos = $this->productos->catalogByIds(array_map('intval', array_keys($items)));
        $total = 0;

        foreach ($productos as $producto) {
            $pid = (int) $producto['id'];
            $cantidad = (int) ($items[$pid] ?? 0);
            if ($cantidad <= 0) {
                continue;
            }

            $precio = (float) $producto['precio_venta_total'];
            $subtotal = $precio * $cantidad;
            $this->detalles->create($cotizacionId, $pid, $cantidad, $precio, $subtotal);
            $total += $subtotal;
        }

        $this->cotizaciones->updateTotal($cotizacionId, $total);
        AuditService::log('crear', 'cotizaciones', $cotizacionId, 'Cotización creada', null, ['cliente_id' => $clienteId, 'total' => $total]);
        $this->flash('success', 'Cotización creada exitosamente.');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['cotizaciones_flash'][] = ['type' => $type, 'message' => $message];
    }
}
