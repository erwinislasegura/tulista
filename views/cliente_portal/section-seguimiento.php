<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Seguimiento de pedidos</h5>
        <p class="text-muted mb-3">Revisa tus pedidos activos: en proceso, despachados y en tránsito hasta su entrega.</p>

        <?php
        $pedidosSeguimiento = array_values(array_filter($data['pedidos'], static function ($pedido) {
            return !in_array((string) ($pedido['estado'] ?? ''), ['entregado', 'cancelado'], true);
        }));
        $totalActivos = count($pedidosSeguimiento);
        $transitoActivos = count(array_filter($pedidosSeguimiento, static function ($pedido) {
            return in_array(strtolower((string) ($pedido['estado'] ?? '')), ['despachado', 'transito', 'en_transito', 'en tránsito'], true);
        }));
        ?>

        <div class="row g-2 mb-3">
            <div class="col-sm-6">
                <div class="alert alert-light border mb-0 py-2">
                    <strong><?= (int) $totalActivos ?></strong> pedidos activos
                </div>
            </div>
            <div class="col-sm-6">
                <div class="alert alert-warning-subtle border mb-0 py-2">
                    <strong><?= (int) $transitoActivos ?></strong> en tránsito/despacho
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Pedido</th><th>Cotización</th><th>Estado operación</th><th>Pago</th><th>Total</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach ($pedidosSeguimiento as $pedido): ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                        <td><span class="badge <?= htmlspecialchars($pedidoEstadoBadge((string) ($pedido['estado'] ?? ''))) ?> text-capitalize"><?= htmlspecialchars($pedidoEstadoLabel((string) ($pedido['estado'] ?? ''))) ?></span></td>
                        <td><span class="badge <?= htmlspecialchars($pagoEstadoBadge((string) ($pedido['estado_pago'] ?? 'pendiente'))) ?> text-capitalize"><?= htmlspecialchars((string) ($pedido['estado_pago'] ?? 'pendiente')) ?></span></td>
                        <td><?= htmlspecialchars($formatCurrency((float) $pedido['total'])) ?></td>
                        <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($pedidosSeguimiento)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No tienes pedidos activos en seguimiento.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
