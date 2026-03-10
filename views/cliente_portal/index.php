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

<div class="card mb-4" id="hacer-cotizacion">
    <h5 class="tl-section-title">Hacer cotización</h5>
    <form method="post" class="tl-minimal-form">
        <input type="hidden" name="action" value="crear_cotizacion">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'cliente-portal.php') ?>">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Desc. %</th></tr></thead>
                <tbody>
                    <?php foreach ($data['productos'] as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td>$<?= number_format((float) $producto['precio_venta_total'], 0, ',', '.') ?></td>
                            <td><input type="number" min="0" class="form-control tl-compact-input" name="items[<?= (int) $producto['id'] ?>][cantidad]" value="0"></td>
                            <td><input type="number" step="0.01" min="0" max="100" class="form-control tl-compact-input" name="items[<?= (int) $producto['id'] ?>][descuento]" value="0"></td>
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

<div class="card mt-3" id="estado-pagos">
    <h5 class="tl-section-title">Pedidos pagados o por pagar</h5>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>ID</th><th>Estado pedido</th><th>Total</th><th>Estado pago</th></tr></thead>
            <tbody>
            <?php foreach ($data['pedidos'] as $pedido): ?>
                <?php $pagado = in_array($pedido['estado'], ['entregado'], true); ?>
                <tr>
                    <td>#<?= (int) $pedido['id'] ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td>
                    <td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                    <td><span class="badge <?= $pagado ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>"><?= $pagado ? 'Pagado' : 'Por pagar' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
