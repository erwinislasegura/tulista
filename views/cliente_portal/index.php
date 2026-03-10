<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<?php $portalLink = 'cotizar.php?token=' . urlencode($data['cliente']['token'] ?? ''); ?>

<div class="card mb-3">
    <div class="card-body py-3">
        <h5 class="tl-section-title mb-2">Portal cliente</h5>
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <label class="form-label">Link para compartir (cotizar y seguimiento)</label>
                <input class="form-control" readonly value="<?= htmlspecialchars($portalLink) ?>">
            </div>
            <div class="col-md-4">
                <a href="<?= htmlspecialchars($portalLink) ?>" target="_blank" class="btn btn-outline-primary mt-4 w-100">Abrir portal</a>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <h5 class="tl-section-title">Nueva cotización</h5>
    <form method="post" class="tl-minimal-form" id="cotizacion-form">
        <input type="hidden" name="action" value="crear_cotizacion">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'cliente-portal.php') ?>">

        <div class="row g-3 mb-3">
            <div class="col-lg-7">
                <label for="buscar-producto" class="form-label">Buscar productos</label>
                <input type="search" id="buscar-producto" class="form-control" placeholder="Escribe nombre del producto...">
            </div>
            <div class="col-lg-5 d-flex align-items-end">
                <div class="alert alert-light border w-100 mb-0 py-2">
                    <strong id="resumen-productos">0 productos</strong>
                    <span class="text-muted"> seleccionados</span>
                    <span class="d-block small mt-1">Total estimado: <strong id="resumen-total">$0</strong></span>
                </div>
            </div>
        </div>

        <div class="table-responsive border rounded">
            <table class="table align-middle mb-0" id="tabla-productos">
                <thead class="table-light"><tr><th>Producto</th><th>Precio</th><th style="min-width: 210px;">Cantidad</th></tr></thead>
                <tbody>
                    <?php foreach ($data['productos'] as $producto): ?>
                        <?php $precio = (float) $producto['precio_venta_total']; ?>
                        <tr data-product-row data-name="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                            <td>
                                <strong><?= htmlspecialchars($producto['nombre']) ?></strong>
                            </td>
                            <td data-precio="$precio">$<?= number_format($precio, 0, ',', '.') ?></td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary" type="button" data-minus>-</button>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="form-control text-center"
                                        data-cantidad
                                        name="items[<?= (int) $producto['id'] ?>]"
                                        value="0"
                                    >
                                    <button class="btn btn-outline-secondary" type="button" data-plus>+</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 gap-2 flex-wrap">
            <small class="text-muted">Tip: usa el buscador y los botones +/- para armar tu cotización más rápido.</small>
            <button class="btn btn-primary" type="submit">Enviar cotización</button>
        </div>
    </form>
</div>

<div class="row g-3">
    <div class="col-lg-7" id="aprobar-cotizacion">
        <div class="card h-100">
            <h5 class="tl-section-title">Aprobar cotización (transforma en pedido)</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Acción</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
                    <tr>
                        <td>#<?= (int) $cotizacion['id'] ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($cotizacion['estado']) ?></td>
                        <td>$<?= number_format((float) $cotizacion['total'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                        <td>
                            <?php if (in_array($cotizacion['estado'], ['aprobada', 'enviada'], true)): ?>
                                <form method="post" class="m-0">
                                    <input type="hidden" name="action" value="crear_pedido">
                                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'cliente-portal.php') ?>">
                                    <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                                    <button class="btn btn-sm btn-success" type="submit">Generar pedido</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Esperando aprobación</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5" id="seguimiento-pedido">
        <div class="card h-100">
            <h5 class="tl-section-title">Seguimiento de pedido</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>ID</th><th>Cotización</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['pedidos'] as $pedido): ?>
                        <tr>
                            <td>#<?= (int) $pedido['id'] ?></td>
                            <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td>
                            <td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('buscar-producto');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    const productCount = document.getElementById('resumen-productos');
    const totalText = document.getElementById('resumen-total');

    const formatCurrency = (value) => new Intl.NumberFormat('es-CL').format(value);

    const updateSummary = () => {
        let selected = 0;
        let total = 0;

        rows.forEach((row) => {
            const qtyInput = row.querySelector('[data-cantidad]');
            const qty = Math.max(0, parseInt(qtyInput.value || '0', 10));
            const price = parseFloat(row.querySelector('[data-precio]').dataset.precio || '0');
            if (qty > 0) {
                selected += 1;
                total += qty * price;
            }
            qtyInput.value = qty;
        });

        productCount.textContent = `${selected} producto${selected === 1 ? '' : 's'}`;
        totalText.textContent = `$${formatCurrency(total)}`;
    };

    searchInput?.addEventListener('input', () => {
        const term = searchInput.value.trim().toLowerCase();
        rows.forEach((row) => {
            const matches = row.dataset.name.includes(term);
            row.classList.toggle('d-none', !matches);
        });
    });

    rows.forEach((row) => {
        const input = row.querySelector('[data-cantidad]');
        row.querySelector('[data-plus]')?.addEventListener('click', () => {
            input.value = Math.max(0, parseInt(input.value || '0', 10)) + 1;
            updateSummary();
        });
        row.querySelector('[data-minus]')?.addEventListener('click', () => {
            input.value = Math.max(0, parseInt(input.value || '0', 10) - 1);
            updateSummary();
        });
        input.addEventListener('input', updateSummary);
    });

    updateSummary();
})();
</script>
