<form method="post" class="row g-3 tl-minimal-form">
    <input type="hidden" name="action" value="add_product">
    <input type="hidden" name="return_url" value="apps-productos.php">

    <div class="col-12 col-xl-6">
        <div class="tl-form-card h-100">
            <h6 class="tl-form-card-title">Datos base</h6>
            <div class="row g-2 tl-minimal-form">
                <div class="col-md-6"><label class="form-label">Categoría</label><select class="form-select tl-compact-input" name="categoria_id" required><option value="">Seleccionar</option><?php foreach ($data['categories'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control tl-compact-input" name="nombre" required></div>
                <div class="col-md-4"><label class="form-label">SKU</label><input class="form-control tl-compact-input" name="sku"></div>
                <div class="col-md-4"><label class="form-label">Marca</label><select class="form-select tl-compact-input" name="marca_id"><option value="">Seleccionar</option><?php foreach ($data['brands'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Modelo</label><input class="form-control tl-compact-input" name="modelo"></div>
                <div class="col-md-6"><label class="form-label">Unidad</label><select class="form-select tl-compact-input" name="unidad_id"><option value="">Seleccionar</option><?php foreach ($data['units'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['descripcion'] . ' (' . $item['abreviatura'] . ')') ?></option><?php endforeach; ?></select></div>
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
