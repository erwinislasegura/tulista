<form method="post" class="row g-3 tl-minimal-form">
    <input type="hidden" name="action" value="add_product">
    <input type="hidden" name="return_url" value="apps-productos.php">

    <div class="col-12 col-xl-6">
        <div class="tl-form-card h-100">
            <h6 class="tl-form-card-title">Datos base</h6>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Categoría</span>
                        <button class="btn btn-sm btn-outline-primary py-0 px-2" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoria">+ Nueva</button>
                    </label>
                    <select class="form-select tl-compact-input" name="categoria_id" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($data['categories'] as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= (int) ($data['last']['categoria_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control tl-compact-input" name="nombre" required></div>
                <div class="col-md-4"><label class="form-label">SKU</label><input class="form-control tl-compact-input" name="sku"></div>
                <div class="col-md-4">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Marca</span>
                        <button class="btn btn-sm btn-outline-primary py-0 px-2" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaMarca">+ Nueva</button>
                    </label>
                    <select class="form-select tl-compact-input" name="marca_id">
                        <option value="">Seleccionar</option>
                        <?php foreach ($data['brands'] as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= (int) ($data['last']['marca_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">Modelo</label><input class="form-control tl-compact-input" name="modelo"></div>
                <div class="col-md-6">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Unidad</span>
                        <button class="btn btn-sm btn-outline-primary py-0 px-2" type="button" data-bs-toggle="modal" data-bs-target="#modalNuevaUnidad">+ Nueva</button>
                    </label>
                    <select class="form-select tl-compact-input" name="unidad_id">
                        <option value="">Seleccionar</option>
                        <?php foreach ($data['units'] as $item): ?>
                            <option value="<?= (int) $item['id'] ?>" <?= (int) ($data['last']['unidad_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['descripcion'] . ' (' . $item['abreviatura'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Código de barras</label><input class="form-control tl-compact-input" name="codigo_barras"></div>
                <div class="col-md-6"><label class="form-label">Producto / Servicio</label><input class="form-control tl-compact-input" name="tipo_item"></div>
                <div class="col-md-6"><label class="form-label">Existencia</label><input class="form-control tl-compact-input" name="existencia" type="number" step="1"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="tl-form-card h-100">
            <h6 class="tl-form-card-title">Precios y control</h6>
            <div class="row g-2 tl-minimal-form">
                <div class="col-md-6 tl-input-group">
                    <label class="form-label">Costo neto</label>
                    <div class="input-group input-group-sm"><span class="input-group-text">$</span><input class="form-control tl-compact-input" name="costo_neto" type="number" step="0.01"></div>
                </div>
                <div class="col-md-6 tl-input-group">
                    <label class="form-label">Venta neto</label>
                    <div class="input-group input-group-sm"><span class="input-group-text">$</span><input class="form-control tl-compact-input" name="precio_venta_neto" type="number" step="0.01"></div>
                </div>
                <div class="col-md-6 tl-input-group">
                    <label class="form-label">Venta total</label>
                    <div class="input-group input-group-sm"><span class="input-group-text">$</span><input class="form-control tl-compact-input" name="precio_venta_total" type="number" step="0.01"></div>
                </div>
                <div class="col-md-6"><label class="form-label">Stock mínimo</label><input class="form-control tl-compact-input" name="stock_minimo" type="number" step="1"></div>
                <div class="col-md-6"><label class="form-label">Comisión vendedor</label><input class="form-control tl-compact-input" name="comision_vendedor" type="number" step="0.01"></div>
            </div>

            <div class="tl-form-actions d-flex justify-content-end mt-3">
                <button class="btn btn-primary px-4" type="submit">Guardar producto</button>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modalNuevaCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nueva categoría</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post">
                <input type="hidden" name="action" value="add_category">
                <input type="hidden" name="return_url" value="apps-productos.php">
                <div class="modal-body">
                    <label class="form-label">Nombre de categoría</label>
                    <input class="form-control" name="name" required>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaMarca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nueva marca</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post">
                <input type="hidden" name="action" value="add_brand">
                <input type="hidden" name="return_url" value="apps-productos.php">
                <div class="modal-body">
                    <label class="form-label">Nombre de marca</label>
                    <input class="form-control" name="name" required>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaUnidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Nueva unidad</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post">
                <input type="hidden" name="action" value="add_unit">
                <input type="hidden" name="return_url" value="apps-productos.php">
                <div class="modal-body row g-2">
                    <div class="col-8"><label class="form-label">Descripción</label><input class="form-control" name="descripcion" required></div>
                    <div class="col-4"><label class="form-label">Abreviatura</label><input class="form-control" name="abreviatura" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
            </form>
        </div>
    </div>
</div>
