<form method="post" class="row g-3">
    <input type="hidden" name="action" value="add_product">
    <input type="hidden" name="return_url" value="apps-productos.php">
    <div class="col-md-3"><label class="form-label">Categoría</label><select class="form-select" name="categoria_id" required><option value="">Seleccionar</option><?php foreach ($data['categories'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required></div>
    <div class="col-md-2"><label class="form-label">SKU</label><input class="form-control" name="sku"></div>
    <div class="col-md-2"><label class="form-label">Marca</label><select class="form-select" name="marca_id"><option value="">Seleccionar</option><?php foreach ($data['brands'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Modelo</label><input class="form-control" name="modelo"></div>
    <div class="col-md-2"><label class="form-label">Unidad</label><select class="form-select" name="unidad_id"><option value="">Seleccionar</option><?php foreach ($data['units'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['descripcion'] . ' (' . $item['abreviatura'] . ')') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Código de barras</label><input class="form-control" name="codigo_barras"></div>
    <div class="col-md-2"><label class="form-label">Producto / Servicio</label><input class="form-control" name="tipo_item"></div>
    <div class="col-md-2"><label class="form-label">Costo neto</label><input class="form-control" name="costo_neto" type="number" step="0.01"></div>
    <div class="col-md-2"><label class="form-label">Venta neto</label><input class="form-control" name="precio_venta_neto" type="number" step="0.01"></div>
    <div class="col-md-2"><label class="form-label">Venta total</label><input class="form-control" name="precio_venta_total" type="number" step="0.01"></div>
    <div class="col-md-2"><label class="form-label">Stock mínimo</label><input class="form-control" name="stock_minimo" type="number" step="1"></div>
    <div class="col-md-2"><label class="form-label">Comisión vendedor</label><input class="form-control" name="comision_vendedor" type="number" step="0.01"></div>
    <div class="col-md-2"><label class="form-label">Existencia</label><input class="form-control" name="existencia" type="number" step="1"></div>
    <div class="col-12"><button class="btn btn-primary px-4" type="submit">Guardar producto</button></div>
</form>
