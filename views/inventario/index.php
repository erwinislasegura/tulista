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
