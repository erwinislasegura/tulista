<?php foreach (($data['flash'] ?? []) as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>
<style>
    .tl-cotizaciones-table {
        font-size: 0.86rem;
    }
    .tl-cotizaciones-table th,
    .tl-cotizaciones-table td {
        padding-top: 0.38rem;
        padding-bottom: 0.38rem;
        vertical-align: middle;
    }
    .tl-cotizaciones-table .tl-product-name {
        line-height: 1.1;
        margin-bottom: 0;
    }
    .tl-cotizaciones-table .tl-product-sku {
        font-size: 0.74rem;
    }
    .tl-stock-badge {
        min-width: 94px;
        font-size: 0.74rem;
        padding: 0.26rem 0.45rem;
    }
    .tl-qty-wrapper {
        display: flex;
        gap: 0.2rem;
        align-items: center;
    }
    .tl-qty-step {
        width: 28px;
        height: 28px;
        padding: 0;
        border-radius: 6px;
        line-height: 1;
    }
    .tl-qty-input {
        min-width: 72px;
        text-align: center;
    }
    .tl-table-tip {
        font-size: 0.74rem;
    }
</style>

<style>
    .tl-cotizar-admin .table th,
    .tl-cotizar-admin .table td {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        vertical-align: middle;
    }
    .tl-cotizar-admin .tl-product-name {
        line-height: 1.15;
        margin-bottom: 0;
        font-weight: 600;
    }
    .tl-cotizar-admin .tl-product-sku {
        font-size: 0.76rem;
    }
    .tl-cotizar-admin .tl-stock-badge {
        font-size: 0.74rem;
    }
</style>

<div class="card mb-4 tl-cotizar-admin">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Crear nueva cotización</h5>
                <p class="text-muted mb-0">Usa el mismo flujo del portal cliente, seleccionando primero el cliente para completar contacto y despacho.</p>
            </div>
            <span class="badge rounded-pill text-bg-info">Paso 1 de 3</span>
        </div>

        <form method="post" class="row g-3" id="cotizacion-admin-form">
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
                <div class="tl-form-card">
                    <h6 class="tl-form-card-title">Datos de contacto y despacho</h6>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label">Contacto</label><input id="f_contacto_nombre" name="contacto_nombre" class="form-control tl-compact-input" required></div>
                        <div class="col-md-3"><label class="form-label">Email contacto</label><input id="f_contacto_email" type="email" name="contacto_email" class="form-control tl-compact-input"></div>
                        <div class="col-md-2"><label class="form-label">Teléfono</label><input id="f_contacto_telefono" name="contacto_telefono" class="form-control tl-compact-input"></div>
                        <div class="col-md-4"><label class="form-label">Dirección entrega</label><input id="f_direccion_entrega" name="direccion_entrega" class="form-control tl-compact-input"></div>
                        <div class="col-12"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control tl-compact-input" placeholder="Horario de entrega, referencias, etc."></div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row g-3 mb-3">
                    <div class="col-lg-8">
                        <label for="buscar-producto" class="form-label">Buscar producto</label>
                        <input type="search" id="buscar-producto" class="form-control" placeholder="Ej: Etiqueta térmica 100x150">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="mostrar-sin-stock">
                            <label class="form-check-label small text-muted" for="mostrar-sin-stock">Mostrar también productos sin existencia</label>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">Resumen</label>
                        <div class="alert alert-info mb-0 py-2">
                            <strong id="resumen-productos">0 productos</strong>
                            <span class="text-muted"> seleccionados</span>
                            <div class="small mt-1">Total estimado: <strong id="resumen-total">$0</strong></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive border rounded-3">
                    <table class="table align-middle mb-0" id="tabla-productos">
                        <thead class="table-light">
                        <tr><th>Producto</th><th>Stock</th><th>Precio</th><th style="width:220px;">Cantidad</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($data['productos'] as $producto): ?>
                            <?php $precio = (float) $producto['precio_venta_total']; ?>
                            <?php $stock = (int) ($producto['existencia'] ?? 0); ?>
                            <tr data-product-row data-name="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>" data-sku="<?= htmlspecialchars(strtolower((string) $producto['sku'])) ?>" data-stock="<?= $stock ?>" class="<?= $stock <= 0 ? 'd-none' : '' ?>">
                                <td>
                                    <p class="tl-product-name"><?= htmlspecialchars($producto['nombre']) ?></p>
                                    <small class="text-muted tl-product-sku"><?= htmlspecialchars((string) $producto['sku']) ?></small>
                                </td>
                                <td>
                                    <?php if ($stock > 5): ?>
                                        <span class="badge bg-success-subtle text-success tl-stock-badge">Disponible: <?= $stock ?></span>
                                    <?php elseif ($stock > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis tl-stock-badge">Stock bajo: <?= $stock ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger tl-stock-badge">Sin existencia</span>
                                    <?php endif; ?>
                                </td>
                                <td data-precio="<?= $precio ?>"><?= '$' . number_format($precio, 0, ',', '.') ?></td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary" type="button" data-minus>-</button>
                                        <input type="number" min="0" step="1" class="form-control text-center" data-cantidad name="items[<?= (int) $producto['id'] ?>][cantidad]" value="0">
                                        <button class="btn btn-outline-secondary" type="button" data-plus>+</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">Tip: usa los botones +/- para completar más rápido.</small>
                <div class="d-flex gap-2">
                    <a href="apps-cotizaciones-registradas.php" class="btn btn-light">Ver cotizaciones registradas</a>
                    <button class="btn btn-primary" type="submit">Crear cotización</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const clientes = <?= $data['clientes_json'] ?: '[]' ?>;
    const byId = Object.fromEntries(clientes.map(c => [String(c.id), c]));

    const select = document.getElementById('clienteSelect');
    const searchInput = document.getElementById('buscar-producto');
    const mostrarSinStock = document.getElementById('mostrar-sin-stock');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    const productCount = document.getElementById('resumen-productos');
    const totalText = document.getElementById('resumen-total');

    const formatCurrency = (value) => new Intl.NumberFormat('es-CL').format(value);

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
        setValue('f_contacto_nombre', c.nombre);
        setValue('f_contacto_email', c.email);
        setValue('f_contacto_telefono', c.telefono);
        setValue('f_direccion_entrega', c.direccion);
    });

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

        if (productCount) {
            productCount.textContent = `${selected} producto${selected === 1 ? '' : 's'}`;
        }
        if (totalText) {
            totalText.textContent = `$${formatCurrency(total)}`;
        }
    };

    const applyFilters = () => {
        const term = (searchInput?.value || '').trim().toLowerCase();
        const includeNoStock = !!mostrarSinStock?.checked;

        rows.forEach((row) => {
            const stock = Number(row.dataset.stock || 0);
            const name = row.dataset.name || '';
            const sku = row.dataset.sku || '';
            const matchTerm = term === '' || name.includes(term) || sku.includes(term);
            const matchStock = includeNoStock || stock > 0;
            row.classList.toggle('d-none', !(matchTerm && matchStock));
        });
    };

    searchInput?.addEventListener('input', applyFilters);
    mostrarSinStock?.addEventListener('change', applyFilters);

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

    applyFilters();
    updateSummary();
    searchInput?.focus();
})();
</script>
