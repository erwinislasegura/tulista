<?php foreach ($data['flash'] as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Registrar movimiento</h5>
    <form method="post" class="row g-2 tl-minimal-form">
        <input type="hidden" name="action" value="movimiento">
        <div class="col-md-4">
            <label class="form-label">Producto</label>
            <select name="producto_id" class="form-select tl-compact-input" required>
                <option value="">Selecciona...</option>
                <?php foreach ($data['productos'] as $producto): ?>
                    <option value="<?= (int) $producto['id'] ?>"><?= htmlspecialchars($producto['nombre']) ?> (Stock: <?= (int) $producto['existencia'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Tipo</label><select name="tipo_movimiento" class="form-select tl-compact-input"><option value="entrada">Entrada</option><option value="salida">Salida</option><option value="ajuste">Ajuste</option></select></div>
        <div class="col-md-2"><label class="form-label">Cantidad</label><input type="number" name="cantidad" class="form-control tl-compact-input" min="1" required></div>
        <div class="col-md-4"><label class="form-label">Descripción</label><input name="descripcion" class="form-control tl-compact-input" placeholder="Motivo breve"></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Guardar movimiento</button></div>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Últimos movimientos</h5>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Usuario</th><th>Descripción</th><th>Fecha</th></tr></thead><tbody>
    <?php foreach ($data['movimientos'] as $row): ?>
        <tr>
            <td><?= (int) $row['id'] ?></td>
            <td><?= htmlspecialchars($row['producto_nombre']) ?></td>
            <td class="text-capitalize"><?= htmlspecialchars($row['tipo_movimiento']) ?></td>
            <td><?= (int) $row['cantidad'] ?></td>
            <td><?= htmlspecialchars($row['usuario_nombre'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['descripcion'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['fecha']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div>


<div class="card mt-4">
    <h5 class="tl-section-title">Consulta de stock</h5>
    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-8">
            <label class="form-label">Buscar por nombre o SKU</label>
            <input type="text" name="stock_q" class="form-control tl-compact-input" value="<?= htmlspecialchars($data['stock_query'] ?? '') ?>" placeholder="Ej: detergente o SKU-001">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Consultar</button>
            <a href="apps-inventario.php" class="btn btn-light">Limpiar</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Producto</th><th>SKU</th><th>Existencia</th><th>Stock mínimo</th><th>Precio</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach (($data['stock_resultados'] ?? []) as $item): ?>
                <?php $bajo = (int) $item['existencia'] <= (int) $item['stock_minimo']; ?>
                <tr>
                    <td><?= htmlspecialchars($item['nombre']) ?></td>
                    <td><?= htmlspecialchars($item['sku']) ?></td>
                    <td><?= (int) $item['existencia'] ?></td>
                    <td><?= (int) $item['stock_minimo'] ?></td>
                    <td>$<?= number_format((float) $item['precio_venta_total'], 0, ',', '.') ?></td>
                    <td><span class="badge <?= $bajo ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?>"><?= $bajo ? 'Bajo mínimo' : 'Disponible' ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['stock_resultados'])): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">Sin resultados para la consulta de stock.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
