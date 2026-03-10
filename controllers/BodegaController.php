<?php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/BodegaEmpaque.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';

class BodegaController
{
    private Pedido $pedidos;
    private ProductModel $productos;
    private BodegaEmpaque $empaque;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('bodega.view');
        $this->pedidos = new Pedido();
        $this->productos = new ProductModel();
        $this->empaque = new BodegaEmpaque();
        $_SESSION['bodega_flash'] = $_SESSION['bodega_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'preparar_pedido') {
                $this->prepararPedido();
            } elseif ($action === 'resolver_item') {
                $this->resolverItem();
            } elseif ($action === 'cambiar_estado') {
                $this->cambiarEstadoPedido();
            }

            header('Location: ' . $this->buildRedirectUrl());
            exit;
        }

        $flash = $_SESSION['bodega_flash'];
        $_SESSION['bodega_flash'] = [];

        $stockQuery = trim($_GET['stock_q'] ?? '');
        $reviewPedidoId = (int) ($_GET['review_pedido'] ?? 0);
        $itemsRevision = $reviewPedidoId > 0 ? $this->empaque->itemsPorPedido($reviewPedidoId) : [];

        return [
            'menu' => $_GET['menu'] ?? 'resumen',
            'flash' => $flash,
            'review_pedido_id' => $reviewPedidoId,
            'review_items' => $itemsRevision,
            'review_summary' => $reviewPedidoId > 0 ? $this->empaque->resumenPorPedido($reviewPedidoId) : ['pendiente' => 0, 'confirmado' => 0, 'reemplazado' => 0, 'omitido' => 0],
            'cotizaciones_aprobadas' => $this->pedidos->cotizacionesAceptadasPendientesBodega(),
            'pedidos' => $this->pedidos->all(),
            'estados_operacion' => Pedido::ESTADOS_OPERACION,
            'productos' => $this->productos->catalog(),
            'stock_query' => $stockQuery,
            'stock_resultados' => $this->productos->stockLookup($stockQuery),
            'historial' => $this->empaque->historialReciente(),
        ];
    }

    private function prepararPedido(): void
    {
        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        if ($pedidoId <= 0) {
            $this->flash('warning', 'Pedido inválido para revisión de empaque.');
            return;
        }

        $pedido = $this->pedidos->findById($pedidoId);
        if (!$pedido || empty($pedido['cotizacion_id'])) {
            $this->flash('warning', 'El pedido no tiene cotización asociada para revisar faltantes.');
            return;
        }

        $insertados = $this->empaque->sincronizarDesdePedido($pedidoId);
        AuditService::log('preparar_pedido', 'bodega', $pedidoId, 'Preparación de pedido en bodega', null, ['items_insertados' => $insertados]);
        $this->flash('success', "Pedido #{$pedidoId} listo para revisión de stock. Items agregados: {$insertados}.");
    }

    private function resolverItem(): void
    {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $decision = $_POST['decision'] ?? 'pendiente';
        $allowed = ['confirmado', 'reemplazado', 'omitido'];
        if ($itemId <= 0 || !in_array($decision, $allowed, true)) {
            $this->flash('warning', 'Datos inválidos para resolver ítem de empaque.');
            return;
        }

        $item = $this->empaque->findItem($itemId);
        if (!$item) {
            $this->flash('danger', 'Ítem de empaque no encontrado.');
            return;
        }

        $cantidadEmpaquetada = max(0, (int) ($_POST['cantidad_empaquetada'] ?? 0));
        $reemplazoId = (int) ($_POST['producto_reemplazo_id'] ?? 0);
        $notas = trim($_POST['notas'] ?? '');

        if ($decision === 'confirmado') {
            $producto = $this->productos->findBasic((int) $item['producto_id']);
            if (!$producto || (int) $producto['existencia'] < $cantidadEmpaquetada || $cantidadEmpaquetada <= 0) {
                $this->flash('warning', 'No hay stock suficiente para confirmar el empaquetado solicitado.');
                return;
            }
        }

        if ($decision === 'reemplazado') {
            if ($reemplazoId <= 0 || $cantidadEmpaquetada <= 0) {
                $this->flash('warning', 'Debes seleccionar producto reemplazo y cantidad empaquetada.');
                return;
            }

            $reemplazo = $this->productos->findBasic($reemplazoId);
            if (!$reemplazo || (int) $reemplazo['existencia'] < $cantidadEmpaquetada) {
                $this->flash('warning', 'El producto de reemplazo no tiene stock suficiente.');
                return;
            }
        }

        if ($decision === 'omitido') {
            $cantidadEmpaquetada = 0;
            $reemplazoId = 0;
            if ($notas === '') {
                $notas = 'Producto omitido del empaquetado por falta de stock o decisión comercial.';
            }
        }

        $ok = $this->empaque->resolverItem($itemId, [
            'accion' => $decision,
            'producto_reemplazo_id' => $reemplazoId > 0 ? $reemplazoId : null,
            'cantidad_empaquetada' => $cantidadEmpaquetada,
            'notas' => $notas,
            'updated_by' => (int) (AuthService::user()['id'] ?? 0) ?: null,
        ]);

        if ($ok) {
            AuditService::log('resolver_item_empaque', 'bodega', $item['pedido_id'], 'Ítem de empaque resuelto', null, [
                'item_id' => $itemId,
                'decision' => $decision,
                'producto_reemplazo_id' => $reemplazoId > 0 ? $reemplazoId : null,
                'cantidad_empaquetada' => $cantidadEmpaquetada,
            ]);
            $this->flash('success', 'Ítem de empaque actualizado correctamente.');
            return;
        }

        $this->flash('danger', 'No se pudo actualizar el ítem de empaque.');
    }

    private function cambiarEstadoPedido(): void
    {
        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        $estado = $_POST['estado'] ?? 'pendiente';
        if ($pedidoId <= 0 || !in_array($estado, Pedido::ESTADOS_OPERACION, true)) {
            $this->flash('warning', 'No fue posible actualizar el estado logístico.');
            return;
        }

        $ok = $this->pedidos->updateEstado($pedidoId, $estado);
        if ($ok) {
            AuditService::log('cambiar_estado_bodega', 'pedidos', $pedidoId, "Estado logístico actualizado a {$estado}");
            $this->flash('success', "Pedido #{$pedidoId} actualizado a estado {$estado}.");
            return;
        }

        $this->flash('danger', 'Error al actualizar el estado del pedido.');
    }

    private function buildRedirectUrl(): string
    {
        $menu = trim($_POST['menu'] ?? 'revision');
        $pedido = (int) ($_POST['review_pedido'] ?? 0);
        $url = 'apps-bodega.php?menu=' . urlencode($menu);
        if ($pedido > 0) {
            $url .= '&review_pedido=' . $pedido;
        }
        return $url;
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['bodega_flash'][] = ['type' => $type, 'message' => $message];
    }
}
