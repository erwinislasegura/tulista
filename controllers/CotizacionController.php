<?php

require_once __DIR__ . '/../models/Cotizacion.php';
require_once __DIR__ . '/../models/CotizacionDetalle.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../services/AuthService.php';

class CotizacionController
{
    private Cotizacion $cotizaciones;
    private CotizacionDetalle $detalles;
    private ProductModel $productos;

    public function __construct()
    {
        AuthService::startSession();
        $this->cotizaciones = new Cotizacion();
        $this->detalles = new CotizacionDetalle();
        $this->productos = new ProductModel();
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
        AuthService::requireRole(['admin', 'vendedor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cambiar_estado') {
            $id = (int) ($_POST['cotizacion_id'] ?? 0);
            $estado = $_POST['estado'] ?? 'pendiente';
            $allowed = ['pendiente', 'respondida', 'aprobada', 'rechazada'];
            if ($id > 0 && in_array($estado, $allowed, true)) {
                $this->cotizaciones->updateEstado($id, $estado);
            }
            header('Location: apps-cotizaciones.php');
            exit;
        }

        $clienteId = (int) ($_GET['cliente_id'] ?? 0);
        return ['cotizaciones' => $this->cotizaciones->all($clienteId > 0 ? $clienteId : null)];
    }

    private function crearCotizacion(int $clienteId): void
    {
        $items = $_POST['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            $this->flash('warning', 'Debes agregar al menos un producto.');
            return;
        }

        $cotizacionId = $this->cotizaciones->create($clienteId, 0);
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
        $this->flash('success', 'Cotización enviada exitosamente.');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['cotizaciones_flash'][] = ['type' => $type, 'message' => $message];
    }
}
