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
                                <input class="form-check-input" type="checkbox" id="soloStock">
                                <label class="form-check-label" for="soloStock">Mostrar solo con stock</label>
                            </div>
                        </div>
                        <div class="col-lg-2 text-lg-end">
                            <small id="productosStatus" class="text-muted">Mostrando <?= count($data['productos']) ?> productos</small>
                        </div>
                    </div>
                </div>
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
                        <tbody id="productosBody">
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
                <div class="d-flex flex-wrap gap-3 align-items-center mt-2">
                    <span id="seleccionResumen" class="badge text-bg-primary">0 productos seleccionados</span>
                    <span id="totalResumen" class="text-muted small">Total estimado: $0</span>
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
    const soloStock = document.getElementById('soloStock');
    const productosBody = document.getElementById('productosBody');
    const productosStatus = document.getElementById('productosStatus');
    const seleccionResumen = document.getElementById('seleccionResumen');
    const totalResumen = document.getElementById('totalResumen');
    const state = {};
    let debounceTimer = null;

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

    function formatCLP(value) {
        return '$' + Number(value || 0).toLocaleString('es-CL');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function refreshSummary() {
        const selected = Object.values(state).filter(item => item.selected && item.qty > 0);
        const total = selected.reduce((acc, item) => {
            const bruto = item.price * item.qty;
            const descuento = bruto * (Math.max(0, Math.min(100, item.disc || 0)) / 100);
            return acc + (bruto - descuento);
        }, 0);

        if (seleccionResumen) {
            seleccionResumen.textContent = selected.length + ' producto' + (selected.length === 1 ? '' : 's') + ' seleccionado' + (selected.length === 1 ? '' : 's');
        }
        if (totalResumen) {
            totalResumen.textContent = 'Total estimado: ' + formatCLP(total);
        }
    }

    function bindRowEvents() {
        document.querySelectorAll('.js-item-check').forEach(function (check) {
            check.addEventListener('change', function () {
                const id = this.dataset.id;
                const qty = document.querySelector('[data-qty="' + id + '"]');
                const disc = document.querySelector('[data-disc="' + id + '"]');
                const enabled = this.checked;
                state[id] = state[id] || { selected: false, qty: 1, disc: 0, price: Number(this.dataset.price || 0) };
                state[id].selected = enabled;
                if (qty) {
                    qty.disabled = !enabled;
                    qty.value = enabled ? (qty.value === '0' ? '1' : qty.value) : '0';
                    state[id].qty = Number(qty.value || 0);
                }
                if (disc) {
                    disc.disabled = !enabled;
                    disc.value = enabled ? disc.value : '0';
                    state[id].disc = Number(disc.value || 0);
                }
                refreshSummary();
            });
        });

        document.querySelectorAll('[data-qty]').forEach(function (qty) {
            qty.addEventListener('input', function () {
                const id = this.dataset.qty;
                state[id] = state[id] || { selected: false, qty: 0, disc: 0, price: Number(this.dataset.price || 0) };
                state[id].qty = Number(this.value || 0);
                refreshSummary();
            });
        });

        document.querySelectorAll('[data-disc]').forEach(function (disc) {
            disc.addEventListener('input', function () {
                const id = this.dataset.disc;
                state[id] = state[id] || { selected: false, qty: 0, disc: 0, price: Number(this.dataset.price || 0) };
                state[id].disc = Number(this.value || 0);
                refreshSummary();
            });
        });
    }

    function renderRows(productos) {
        if (!productosBody) return;
        if (!productos.length) {
            productosBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No hay productos con ese filtro.</td></tr>';
            if (productosStatus) productosStatus.textContent = '0 productos encontrados';
            return;
        }

        productosBody.innerHTML = productos.map(function (producto) {
            const id = String(producto.id);
            const saved = state[id] || { selected: false, qty: 0, disc: 0, price: Number(producto.precio_venta_total || 0) };
            const selected = !!saved.selected;
            const qty = selected ? (saved.qty > 0 ? saved.qty : 1) : 0;
            const disc = selected ? (saved.disc || 0) : 0;
            state[id] = { selected, qty, disc, price: Number(producto.precio_venta_total || 0) };
            return '<tr>' +
                '<td><input type="checkbox" class="form-check-input js-item-check" data-id="' + id + '" data-price="' + Number(producto.precio_venta_total || 0) + '"' + (selected ? ' checked' : '') + '></td>' +
                '<td>' + escapeHtml(producto.nombre) + '</td>' +
                '<td>' + escapeHtml(producto.sku) + '</td>' +
                '<td>' + Number(producto.existencia || 0) + '</td>' +
                '<td>' + formatCLP(producto.precio_venta_total || 0) + '</td>' +
                '<td><input type="number" name="items[' + id + '][cantidad]" class="form-control tl-compact-input" min="0" value="' + qty + '" data-qty="' + id + '" data-price="' + Number(producto.precio_venta_total || 0) + '"' + (selected ? '' : ' disabled') + '></td>' +
                '<td><input type="number" step="0.01" name="items[' + id + '][descuento]" class="form-control tl-compact-input" min="0" max="100" value="' + disc + '" data-disc="' + id + '" data-price="' + Number(producto.precio_venta_total || 0) + '"' + (selected ? '' : ' disabled') + '></td>' +
            '</tr>';
        }).join('');
        if (productosStatus) productosStatus.textContent = 'Mostrando ' + productos.length + ' productos';
        bindRowEvents();
        refreshSummary();
    }

    async function fetchProductos() {
        const q = (productSearch?.value || '').trim();
        const params = new URLSearchParams({
            ajax: 'productos',
            q: q,
            solo_stock: soloStock?.checked ? '1' : '0'
        });
        const response = await fetch('apps-cotizaciones.php?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error('No se pudieron cargar productos');
        const data = await response.json();
        renderRows(Array.isArray(data.productos) ? data.productos : []);
    }

    function queueSearch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchProductos().catch(function () {
                if (productosStatus) productosStatus.textContent = 'Error al filtrar productos';
            });
        }, 250);
    }

    bindRowEvents();
    renderRows(productosIniciales);

    productSearch?.addEventListener('input', queueSearch);
    soloStock?.addEventListener('change', queueSearch);
})();
</script>
