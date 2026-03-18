<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Seguimiento de pedidos</h5>
        <p class="text-muted mb-3">Revisa tus pedidos activos: en proceso, despachados y en tránsito hasta su entrega.</p>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Pedido</th><th>Cotización</th><th>Estado operación</th><th>Pago</th><th>Total</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php
                $pedidosSeguimiento = array_values(array_filter($data['pedidos'], static function ($pedido) {
                    return !in_array((string) ($pedido['estado'] ?? ''), ['entregado', 'cancelado'], true);
                }));
                ?>
                <?php foreach ($pedidosSeguimiento as $pedido): ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($pedido['estado_pago'] ?? 'pendiente') ?></td>
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
