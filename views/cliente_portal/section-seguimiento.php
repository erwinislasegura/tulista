<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Seguimiento de pedidos</h5>
        <p class="text-muted mb-3">Revisa estado, total y fecha de tus pedidos generados.</p>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Pedido</th><th>Cotización</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach ($data['pedidos'] as $pedido): ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td>
                        <td><?= htmlspecialchars($formatCurrency((float) $pedido['total'])) ?></td>
                        <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
