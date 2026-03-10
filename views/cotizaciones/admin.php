<?php foreach (($data['flash'] ?? []) as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="tl-section-title mb-3">Nueva cotización ERP</h5>
        <form method="post" class="row g-3">
            <input type="hidden" name="action" value="crear_admin">

            <div class="col-lg-4">
                <label class="form-label">Seleccionar cliente</label>
                <select id="clienteSelect" name="cliente_id" class="form-select tl-compact-input" required>
                    <option value="">Selecciona cliente...</option>
                    <?php foreach ($data['clientes'] as $cliente): ?>
                        <option value="<?= (int) $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?> (<?= htmlspecialchars($cliente['rut']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-lg-8">
                <div class="row g-2 border rounded p-2 bg-light-subtle">
                    <div class="col-md-4"><label class="form-label mb-1 small">RUT</label><input id="c_rut" class="form-control tl-compact-input" readonly></div>
                    <div class="col-md-4"><label class="form-label mb-1 small">Empresa</label><input id="c_empresa" class="form-control tl-compact-input" readonly></div>
                    <div class="col-md-4"><label class="form-label mb-1 small">Tipo cliente</label><input id="c_tipo" class="form-control tl-compact-input" readonly></div>
                    <div class="col-md-4"><label class="form-label mb-1 small">Email</label><input id="c_email" class="form-control tl-compact-input" readonly></div>
                    <div class="col-md-4"><label class="form-label mb-1 small">Teléfono</label><input id="c_telefono" class="form-control tl-compact-input" readonly></div>
                    <div class="col-md-4"><label class="form-label mb-1 small">Dirección</label><input id="c_direccion" class="form-control tl-compact-input" readonly></div>
                </div>
            </div>

            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px">Sel.</th>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th style="width:120px">Cantidad</th>
                            <th style="width:130px">Desc. %</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($data['productos'] as $producto): ?>
                            <tr>
                                <td><input type="checkbox" class="form-check-input js-item-check" data-id="<?= (int) $producto['id'] ?>"></td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <td><?= htmlspecialchars($producto['sku']) ?></td>
                                <td><?= (int) $producto['existencia'] ?></td>
                                <td>$<?= number_format((float) $producto['precio_venta_total'], 0, ',', '.') ?></td>
                                <td><input type="number" name="items[<?= (int) $producto['id'] ?>][cantidad]" class="form-control tl-compact-input" min="0" value="0" data-qty="<?= (int) $producto['id'] ?>" disabled></td>
                                <td><input type="number" step="0.01" name="items[<?= (int) $producto['id'] ?>][descuento]" class="form-control tl-compact-input" min="0" max="100" value="0" data-disc="<?= (int) $producto['id'] ?>" disabled></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0 small">Marca productos para habilitar cantidad y descuentos por línea.</p>
                <button class="btn btn-primary" type="submit">Guardar cotización</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
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
                                    <li><a class="dropdown-item" href="apps-cotizaciones.php?download_pdf=<?= (int) $cotizacion['id'] ?>">Descargar PDF</a></li>
                                    <li><hr class="dropdown-divider"></li>
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
</div>

<script>
(function () {
    const clientes = <?= $data['clientes_json'] ?: '[]' ?>;
    const byId = Object.fromEntries(clientes.map(c => [String(c.id), c]));
    const select = document.getElementById('clienteSelect');

    function setValue(id, value) {
        const el = document.getElementById(id);
        if (el) el.value = value || '';
    }

    select?.addEventListener('change', function () {
        const c = byId[this.value] || {};
        setValue('c_rut', c.rut);
        setValue('c_empresa', c.empresa);
        setValue('c_tipo', c.tipo_cliente);
        setValue('c_email', c.email);
        setValue('c_telefono', c.telefono);
        setValue('c_direccion', c.direccion);
    });

    document.querySelectorAll('.js-item-check').forEach(function (check) {
        check.addEventListener('change', function () {
            const id = this.dataset.id;
            const qty = document.querySelector('[data-qty="' + id + '"]');
            const disc = document.querySelector('[data-disc="' + id + '"]');
            const enabled = this.checked;
            if (qty) {
                qty.disabled = !enabled;
                qty.value = enabled ? (qty.value === '0' ? '1' : qty.value) : '0';
            }
            if (disc) {
                disc.disabled = !enabled;
                disc.value = enabled ? disc.value : '0';
            }
        });
    });
})();
</script>
