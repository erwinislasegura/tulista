<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Cotizaciones aceptadas</small><h4 class="mb-0"><?= count($data['cotizaciones_aprobadas']) ?></h4></div></div>
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Pedidos empaquetados</small><h4 class="mb-0"><?= count(array_filter($data['pedidos'], fn($p) => ($p['estado'] ?? '') === 'empaquetado')) ?></h4></div></div>
    <div class="col-md-4"><div class="card p-3"><small class="text-muted">Pedidos en tránsito</small><h4 class="mb-0"><?= count(array_filter($data['pedidos'], fn($p) => ($p['estado'] ?? '') === 'transito')) ?></h4></div></div>
</div>

<div class="card mb-4">
    <h5 class="tl-section-title">Cotizaciones aceptadas por clientes</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Cotización</th><th>Cliente</th><th>Total</th><th>Pedido</th><th>Estado pedido</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php foreach ($data['cotizaciones_aprobadas'] as $cot): ?>
                <tr>
                    <td>#<?= (int) $cot['id'] ?></td>
                    <td><?= htmlspecialchars($cot['cliente_nombre']) ?></td>
                    <td>$<?= number_format((float) $cot['total'], 0, ',', '.') ?></td>
                    <td><?= $cot['pedido_id'] ? ('#' . (int) $cot['pedido_id']) : 'Sin pedido' ?></td>
                    <td><?= htmlspecialchars($cot['pedido_estado'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($cot['pedido_fecha'] ?: $cot['fecha']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <h5 class="tl-section-title">Estados logísticos (pedido empaquetado, despachado y en tránsito)</h5>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Pedido</th><th>Cliente</th><th>Cotización</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php foreach ($data['pedidos'] as $pedido): ?>
                <tr>
                    <td>#<?= (int) $pedido['id'] ?></td>
                    <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                    <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                    <td><span class="badge bg-light text-dark text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></span></td>
                    <td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h5 class="tl-section-title">Consulta de stock (usuarios/clientes)</h5>
    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-8"><label class="form-label">Producto o SKU</label><input type="text" name="stock_q" class="form-control tl-compact-input" value="<?= htmlspecialchars($data['stock_query']) ?>"></div>
        <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary" type="submit">Buscar stock</button><a href="apps-bodega.php" class="btn btn-light">Limpiar</a></div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Producto</th><th>SKU</th><th>Existencia</th><th>Mínimo</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($data['stock_resultados'] as $item): ?>
                <?php $ok = (int) $item['existencia'] > (int) $item['stock_minimo']; ?>
                <tr>
                    <td><?= htmlspecialchars($item['nombre']) ?></td>
                    <td><?= htmlspecialchars($item['sku']) ?></td>
                    <td><?= (int) $item['existencia'] ?></td>
                    <td><?= (int) $item['stock_minimo'] ?></td>
                    <td><span class="badge <?= $ok ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>"><?= $ok ? 'Disponible' : 'Stock crítico' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
