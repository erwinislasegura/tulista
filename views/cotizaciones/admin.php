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
                <div class="tl-form-card mb-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-7">
                            <label class="form-label mb-1">Buscar producto</label>
                            <input id="productoSearch" type="search" class="form-control tl-compact-input" placeholder="Buscar por nombre o SKU...">
                        </div>
                        <div class="col-lg-3">
                            <div class="form-check mt-4 pt-1">
                                <input class="form-check-input" type="checkbox" id="mostrarSinStock">
                                <label class="form-check-label" for="mostrarSinStock">Mostrar productos sin stock</label>
                            </div>
                        </div>
                        <div class="col-lg-2 text-lg-end">
                            <small id="productosStatus" class="text-muted">Mostrando <?= count($data['productos']) ?> productos</small>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle tl-cotizaciones-table">
                        <thead>
                        <tr>
                            <th style="width:60px">Sel.</th>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th style="width:120px">Cantidad</th>
                            <th style="width:130px">Desc. %</th>
                        </tr>
                        </thead>
                        <tbody id="productosBody">
                        <?php foreach ($data['productos'] as $producto): ?>
                            <tr data-product-row data-name="<?= htmlspecialchars(strtolower((string) $producto['nombre'])) ?>" data-sku="<?= htmlspecialchars(strtolower((string) $producto['sku'])) ?>" data-stock="<?= (int) $producto['existencia'] ?>">
                                <td><input type="checkbox" class="form-check-input js-item-check" data-id="<?= (int) $producto['id'] ?>" data-price="<?= (float) $producto['precio_venta_total'] ?>"></td>
                                <td>
                                    <p class="fw-semibold tl-product-name"><?= htmlspecialchars($producto['nombre']) ?></p>
                                    <small class="text-muted tl-product-sku"><?= htmlspecialchars($producto['sku']) ?></small>
                                </td>
                                <td>
                                    <?php
                                        $stock = (int) $producto['existencia'];
                                        $stockClass = $stock <= 0 ? 'text-bg-danger' : ($stock <= 5 ? 'text-bg-warning' : 'text-bg-success');
                                        $stockLabel = $stock <= 0 ? 'Sin stock' : ($stock <= 5 ? 'Stock bajo' : 'Disponible');
                                    ?>
                                    <span class="badge <?= $stockClass ?> tl-stock-badge"><?= $stockLabel ?> · <?= $stock ?></span>
                                </td>
                                <td>$<?= number_format((float) $producto['precio_venta_total'], 0, ',', '.') ?></td>
                                <td>
                                    <div class="tl-qty-wrapper">
                                        <button type="button" class="btn btn-outline-secondary btn-sm tl-qty-step" data-qty-step="<?= (int) $producto['id'] ?>" data-delta="-1" disabled>-</button>
                                        <input type="number" inputmode="numeric" name="items[<?= (int) $producto['id'] ?>][cantidad]" class="form-control tl-compact-input tl-qty-input" min="0" step="1" value="0" data-qty="<?= (int) $producto['id'] ?>" data-price="<?= (float) $producto['precio_venta_total'] ?>" disabled>
                                        <button type="button" class="btn btn-outline-secondary btn-sm tl-qty-step" data-qty-step="<?= (int) $producto['id'] ?>" data-delta="1" disabled>+</button>
                                    </div>
                                </td>
                                <td><input type="number" step="0.01" name="items[<?= (int) $producto['id'] ?>][descuento]" class="form-control tl-compact-input" min="0" max="100" value="0" data-disc="<?= (int) $producto['id'] ?>" data-price="<?= (float) $producto['precio_venta_total'] ?>" disabled></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-wrap gap-3 align-items-center mt-2">
                    <span id="seleccionResumen" class="badge text-bg-primary">0 productos seleccionados</span>
                    <span id="totalResumen" class="text-muted small">Total estimado: $0</span>
                    <span class="text-muted tl-table-tip">Tip rápido: selecciona la fila y ajusta cantidad con + / -</span>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-between align-items-center">
                <p class="text-muted mb-0 small">Tip: escribe 3+ letras para filtrar más rápido y mantén solo productos con stock para cotizar en menos pasos.</p>
                <div class="d-flex gap-2">
                    <a href="apps-cotizaciones-registradas.php" class="btn btn-light">Ver cotizaciones registradas</a>
                    <button class="btn btn-primary" type="submit">Guardar cotización</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const clientes = <?= $data['clientes_json'] ?: '[]' ?>;
    const productosIniciales = <?= json_encode($data['productos'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]' ?>;
    const byId = Object.fromEntries(clientes.map(c => [String(c.id), c]));
    const select = document.getElementById('clienteSelect');
    const productSearch = document.getElementById('productoSearch');
    const mostrarSinStock = document.getElementById('mostrarSinStock');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    const productosStatus = document.getElementById('productosStatus');
    const seleccionResumen = document.getElementById('seleccionResumen');
    const totalResumen = document.getElementById('totalResumen');
    const selectedItems = new Map();
    let selectedCount = 0;
    let totalEstimate = 0;

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

    function refreshSummary() {
        if (seleccionResumen) {
            seleccionResumen.textContent = selectedCount + ' producto' + (selectedCount === 1 ? '' : 's') + ' seleccionado' + (selectedCount === 1 ? '' : 's');
        }
        if (totalResumen) {
            totalResumen.textContent = 'Total estimado: $' + Number(totalEstimate || 0).toLocaleString('es-CL');
        }
    }
    function setStepButtonsEnabled(id, enabled) {
        document.querySelectorAll('[data-qty-step="' + id + '"]').forEach(function (button) {
            button.disabled = !enabled;
        });
    }

    function recalculateItem(id) {
        const check = document.querySelector('.js-item-check[data-id="' + id + '"]');
        const qty = document.querySelector('[data-qty="' + id + '"]');
        const disc = document.querySelector('[data-disc="' + id + '"]');
        const price = Number(check?.dataset.price || 0);
        const quantity = Math.max(0, Number(qty?.value || 0));
        const discount = Math.max(0, Math.min(100, Number(disc?.value || 0)));
        const bruto = price * quantity;
        const lineTotal = bruto - (bruto * discount / 100);
        const previous = selectedItems.get(id) || 0;

        if (check?.checked && quantity > 0) {
            if (!selectedItems.has(id)) selectedCount += 1;
            selectedItems.set(id, lineTotal);
            totalEstimate += lineTotal - previous;
        } else if (selectedItems.has(id)) {
            selectedItems.delete(id);
            selectedCount = Math.max(0, selectedCount - 1);
            totalEstimate = Math.max(0, totalEstimate - previous);
        }

        refreshSummary();
    }

    function applyFilter() {
        const term = (productSearch?.value || '').trim().toLowerCase();
        const includeNoStock = !!mostrarSinStock?.checked;
        let visibles = 0;

        rows.forEach(function (row) {
            const name = row.dataset.name || '';
            const sku = row.dataset.sku || '';
            const stock = Number(row.dataset.stock || 0);
            const matchText = term === '' || name.includes(term) || sku.includes(term);
            const matchStock = includeNoStock || stock > 0;
            const visible = matchText && matchStock;
            row.classList.toggle('d-none', !visible);
            if (visible) visibles++;
        });

        if (productosStatus) {
            productosStatus.textContent = 'Mostrando ' + visibles + ' productos';
        }
    }

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
            setStepButtonsEnabled(id, enabled);
            recalculateItem(id);
        });
    });

    document.querySelectorAll('[data-qty], [data-disc]').forEach(function (input) {
        input.addEventListener('input', function () {
            const id = this.getAttribute('data-qty') || this.getAttribute('data-disc');
            if (!id) return;
            recalculateItem(id);
        });
    });
    document.querySelectorAll('[data-product-row]').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('input,button,label,a')) return;
            const check = row.querySelector('.js-item-check');
            if (!check) return;
            check.checked = !check.checked;
            check.dispatchEvent(new Event('change'));
        });
    });
    document.querySelectorAll('[data-qty-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-qty-step');
            const delta = Number(this.getAttribute('data-delta') || 0);
            const qty = document.querySelector('[data-qty="' + id + '"]');
            if (!qty || qty.disabled) return;
            const next = Math.max(0, Number(qty.value || 0) + delta);
            qty.value = String(next);
            qty.dispatchEvent(new Event('input'));
        });
    });

    productSearch?.addEventListener('input', applyFilter);
    mostrarSinStock?.addEventListener('change', applyFilter);
    productSearch?.focus();
    applyFilter();
    refreshSummary();
})();
</script>
