<?php
$historialPedidos = $data['pedidos_historial'] ?? [];
if (empty($historialPedidos) && !empty($data['pedidos'])) {
    $historialPedidos = $data['pedidos'];
}
?>

<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Historial de pedidos</h5>
        <p class="text-muted mb-3">Aquí verás pedidos cerrados (entregados/cancelados) o pagados para consulta histórica.</p>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Pedido</th><th>Cotización</th><th>Estado operación</th><th>Estado pago</th><th>Pagado el</th><th>Total</th><th>Fecha</th><th>Detalle</th></tr></thead>
                <tbody>
                <?php foreach ($historialPedidos as $pedido): ?>
                    <?php
                    $cotizacionId = (int) ($pedido['cotizacion_id'] ?? 0);
                    $detallesPedido = $cotizacionId > 0 ? ($data['detalles_por_cotizacion'][$cotizacionId] ?? []) : [];
                    $detallesJson = htmlspecialchars(json_encode($detallesPedido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                        <td><span class="badge <?= htmlspecialchars($pedidoEstadoBadge((string) ($pedido['estado'] ?? ''))) ?> text-capitalize"><?= htmlspecialchars($pedidoEstadoLabel((string) ($pedido['estado'] ?? '-'))) ?></span></td>
                        <td><span class="badge <?= htmlspecialchars($pagoEstadoBadge((string) ($pedido['estado_pago'] ?? 'pendiente'))) ?> text-capitalize"><?= htmlspecialchars((string) ($pedido['estado_pago'] ?? 'pendiente')) ?></span></td>
                        <td><?= !empty($pedido['pagado_at']) ? htmlspecialchars((string) $pedido['pagado_at']) : '-' ?></td>
                        <td><?= htmlspecialchars($formatCurrency((float) ($pedido['total'] ?? 0))) ?></td>
                        <td><?= htmlspecialchars((string) ($pedido['fecha'] ?? '-')) ?></td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary js-detalle-pedido"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-detalle-pedido-historial"
                                data-id="<?= (int) $pedido['id'] ?>"
                                data-cotizacion="<?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?>"
                                data-estado-operacion="<?= htmlspecialchars($pedidoEstadoLabel((string) ($pedido['estado'] ?? '-'))) ?>"
                                data-estado-pago="<?= htmlspecialchars((string) ($pedido['estado_pago'] ?? 'pendiente')) ?>"
                                data-pagado-at="<?= !empty($pedido['pagado_at']) ? htmlspecialchars((string) $pedido['pagado_at']) : '-' ?>"
                                data-total="<?= htmlspecialchars($formatCurrency((float) ($pedido['total'] ?? 0))) ?>"
                                data-fecha="<?= htmlspecialchars((string) ($pedido['fecha'] ?? '-')) ?>"
                                data-detalles="<?= $detallesJson ?>"
                            >
                                Ver detalle
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($historialPedidos)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-3">Aún no tienes pedidos en historial.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detalle-pedido-historial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 small">
                    <div class="col-6"><strong>Pedido:</strong> <span data-detalle-id>-</span></div>
                    <div class="col-6"><strong>Cotización:</strong> <span data-detalle-cotizacion>-</span></div>
                    <div class="col-6"><strong>Estado operación:</strong> <span data-detalle-estado-operacion>-</span></div>
                    <div class="col-6"><strong>Estado pago:</strong> <span data-detalle-estado-pago>-</span></div>
                    <div class="col-6"><strong>Pagado el:</strong> <span data-detalle-pagado-at>-</span></div>
                    <div class="col-6"><strong>Fecha pedido:</strong> <span data-detalle-fecha>-</span></div>
                    <div class="col-12"><strong>Total:</strong> <span data-detalle-total>-</span></div>
                </div>
                <hr class="my-3">
                <h6 class="mb-2">Productos comprados</h6>
                <div class="table-responsive border rounded-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody data-detalle-items>
                            <tr><td colspan="4" class="text-center text-muted py-2">Sin productos para mostrar.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const botones = document.querySelectorAll('.js-detalle-pedido');
    if (!botones.length) return;

    const targets = {
        id: document.querySelector('[data-detalle-id]'),
        cotizacion: document.querySelector('[data-detalle-cotizacion]'),
        estadoOperacion: document.querySelector('[data-detalle-estado-operacion]'),
        estadoPago: document.querySelector('[data-detalle-estado-pago]'),
        pagadoAt: document.querySelector('[data-detalle-pagado-at]'),
        total: document.querySelector('[data-detalle-total]'),
        fecha: document.querySelector('[data-detalle-fecha]'),
        items: document.querySelector('[data-detalle-items]'),
    };

    const formatCurrency = (value) => {
        const number = Number(value || 0);
        return `$${new Intl.NumberFormat('es-CL').format(number)}`;
    };

    botones.forEach((btn) => {
        btn.addEventListener('click', () => {
            if (targets.id) targets.id.textContent = `#${btn.dataset.id || '-'}`;
            if (targets.cotizacion) targets.cotizacion.textContent = btn.dataset.cotizacion || '-';
            if (targets.estadoOperacion) targets.estadoOperacion.textContent = btn.dataset.estadoOperacion || '-';
            if (targets.estadoPago) targets.estadoPago.textContent = btn.dataset.estadoPago || '-';
            if (targets.pagadoAt) targets.pagadoAt.textContent = btn.dataset.pagadoAt || '-';
            if (targets.total) targets.total.textContent = btn.dataset.total || '-';
            if (targets.fecha) targets.fecha.textContent = btn.dataset.fecha || '-';

            if (targets.items) {
                let detalles = [];
                try {
                    detalles = JSON.parse(btn.dataset.detalles || '[]');
                } catch (error) {
                    detalles = [];
                }

                if (!Array.isArray(detalles) || detalles.length === 0) {
                    targets.items.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-2">Sin productos para mostrar.</td></tr>';
                    return;
                }

                targets.items.innerHTML = detalles.map((item) => {
                    const nombre = item.producto_nombre || item.nombre || `Producto #${item.producto_id || '-'}`;
                    const cantidad = Number(item.cantidad || 0);
                    const precio = Number(item.precio || 0);
                    const subtotal = Number(item.subtotal || (cantidad * precio));
                    return `
                        <tr>
                            <td>${nombre}</td>
                            <td class="text-end">${cantidad}</td>
                            <td class="text-end">${formatCurrency(precio)}</td>
                            <td class="text-end">${formatCurrency(subtotal)}</td>
                        </tr>
                    `;
                }).join('');
            }
        });
    });
})();
</script>
