<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Historial de pedidos</h5>
        <p class="text-muted mb-3">Aquí verás pedidos cerrados (entregados/cancelados) o pagados para consulta histórica.</p>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Pedido</th><th>Cotización</th><th>Estado operación</th><th>Estado pago</th><th>Pagado el</th><th>Total</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach (($data['pedidos_historial'] ?? []) as $pedido): ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars((string) ($pedido['estado'] ?? '-')) ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars((string) ($pedido['estado_pago'] ?? 'pendiente')) ?></td>
                        <td><?= !empty($pedido['pagado_at']) ? htmlspecialchars((string) $pedido['pagado_at']) : '-' ?></td>
                        <td><?= htmlspecialchars($formatCurrency((float) ($pedido['total'] ?? 0))) ?></td>
                        <td><?= htmlspecialchars((string) ($pedido['fecha'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['pedidos_historial'])): ?>
                    <tr><td colspan="7" class="text-center text-muted py-3">Aún no tienes pedidos en historial.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
