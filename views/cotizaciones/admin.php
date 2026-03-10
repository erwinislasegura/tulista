<?php foreach (($data['flash'] ?? []) as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Crear cotización</h5>
    <form method="post" class="row g-2">
        <input type="hidden" name="action" value="crear_admin">
        <div class="col-md-4">
            <label class="form-label">Cliente</label>
            <select name="cliente_id" class="form-select tl-compact-input" required>
                <option value="">Selecciona cliente...</option>
                <?php foreach ($data['clientes'] as $cliente): ?>
                    <option value="<?= (int) $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?> (<?= htmlspecialchars($cliente['rut']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Producto</th><th>Precio</th><th>Stock</th><th style="width:140px">Cantidad</th></tr></thead><tbody>
                <?php foreach ($data['productos'] as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td>$<?= number_format((float) $producto['precio_venta_total'], 0, ',', '.') ?></td>
                        <td><?= (int) $producto['existencia'] ?></td>
                        <td><input type="number" name="items[<?= (int) $producto['id'] ?>]" class="form-control tl-compact-input" min="0" value="0"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        </div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Guardar cotización</button></div>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Cotizaciones registradas</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Cliente</th><th>Vendedor</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
                <tr>
                    <td>#<?= (int) $cotizacion['id'] ?></td>
                    <td><?= htmlspecialchars($cotizacion['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($cotizacion['vendedor'] ?? 'Sin asignar') ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars($cotizacion['estado']) ?></td>
                    <td>$<?= number_format((float) $cotizacion['total'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" type="button">Acciones</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form method="post" class="px-2 py-1">
                                        <input type="hidden" name="action" value="cambiar_estado">
                                        <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                                        <label class="form-label small mb-1">Cambiar estado</label>
                                        <select name="estado" class="form-select form-select-sm tl-compact-input mb-2">
                                            <?php foreach (['borrador','enviada','aprobada','rechazada'] as $estado): ?>
                                                <option value="<?= $estado ?>" <?= $estado === $cotizacion['estado'] ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-primary btn-sm w-100" type="submit">Guardar</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form method="post" onsubmit="return confirm('¿Eliminar cotización?');"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>"><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
