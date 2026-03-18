<?php

require_once __DIR__ . '/../models/MovimientoStock.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/AuditService.php';

class InventarioController
{
    private $movimientos ;
    private $productos ;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('inventario.view');
        $this->movimientos = new MovimientoStock();
        $this->productos = new ProductModel();
        $_SESSION['inventario_flash'] = $_SESSION['inventario_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'movimiento') {
            $this->registrarMovimiento();
            header('Location: apps-inventario.php');
            exit;
        }

        $flash = $_SESSION['inventario_flash'];
        $_SESSION['inventario_flash'] = [];

        $stockQuery = trim($_GET['stock_q'] ?? '');

        return [
            'productos' => $this->productos->catalog(),
            'movimientos' => $this->movimientos->recent(),
            'stock_query' => $stockQuery,
            'stock_resultados' => $this->productos->stockLookup($stockQuery),
            'flash' => $flash,
        ];
    }

    private function registrarMovimiento(): void
    {
        $payload = [
            'producto_id' => (int) ($_POST['producto_id'] ?? 0),
            'tipo_movimiento' => $_POST['tipo_movimiento'] ?? 'entrada',
            'cantidad' => (int) ($_POST['cantidad'] ?? 0),
            'usuario_id' => AuthService::user()['id'] ?? null,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        if ($payload['producto_id'] <= 0 || $payload['cantidad'] <= 0 || !in_array($payload['tipo_movimiento'], ['entrada', 'salida', 'ajuste'], true)) {
            $this->flash('warning', 'Completa los campos requeridos para registrar el movimiento.');
            return;
        }

        $ok = $this->movimientos->create($payload);
        if ($ok) {
            AuditService::log('movimiento_stock', 'inventario', $payload['producto_id'], 'Movimiento de inventario registrado', null, $payload);
            $this->flash('success', 'Movimiento registrado correctamente.');
            return;
        }

        $this->flash('danger', 'No fue posible registrar el movimiento.');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['inventario_flash'][] = ['type' => $type, 'message' => $message];
    }
}
