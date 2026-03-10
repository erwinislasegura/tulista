<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="mb-1">Productos</h4>
                <p class="text-muted mb-0">Módulo MVC conectado a MySQL.</p>
            </div>
        </div>

        <?php foreach ($data['flash'] as $alert): ?>
            <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($alert['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>

        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-productos" type="button">Ingreso de productos</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-categorias" type="button">Ingreso de categorías</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-marcas" type="button">Ingreso de marcas</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-unidades" type="button">Ingreso unidad medida</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-importacion" type="button">Importación productos</button></li>
                </ul>

                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="tab-productos">
                        <form method="post" class="row g-3">
                            <input type="hidden" name="action" value="add_product">
                            <input type="hidden" name="return_tab" value="tab-productos">
                            <div class="col-md-3"><label class="form-label">Categoría</label><select class="form-select" name="categoria_id" required><option value="">Seleccionar</option><?php foreach ($data['categories'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" required></div>
                            <div class="col-md-2"><label class="form-label">SKU</label><input class="form-control" name="sku"></div>
                            <div class="col-md-2"><label class="form-label">Marca</label><select class="form-select" name="marca_id"><option value="">Seleccionar</option><?php foreach ($data['brands'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-2"><label class="form-label">Modelo</label><input class="form-control" name="modelo"></div>
                            <div class="col-md-2"><label class="form-label">Unidad</label><select class="form-select" name="unidad_id"><option value="">Seleccionar</option><?php foreach ($data['units'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-2"><label class="form-label">Código de barras</label><input class="form-control" name="codigo_barras"></div>
                            <div class="col-md-2"><label class="form-label">Producto / Servicio</label><input class="form-control" name="tipo_item"></div>
                            <div class="col-md-2"><label class="form-label">Costo neto</label><input class="form-control" name="costo_neto" type="number" step="0.01"></div>
                            <div class="col-md-2"><label class="form-label">Venta: Precio neto</label><input class="form-control" name="precio_venta_neto" type="number" step="0.01"></div>
                            <div class="col-md-2"><label class="form-label">Venta: Precio total</label><input class="form-control" name="precio_venta_total" type="number" step="0.01"></div>
                            <div class="col-md-2"><label class="form-label">Stock mínimo</label><input class="form-control" name="stock_minimo" type="number" step="1"></div>
                            <div class="col-md-2"><label class="form-label">Comisión vendedor</label><input class="form-control" name="comision_vendedor" type="number" step="0.01"></div>
                            <div class="col-md-2"><label class="form-label">Existencia</label><input class="form-control" name="existencia" type="number" step="1"></div>
                            <div class="col-12"><button class="btn btn-primary" type="submit">Guardar producto</button></div>
                        </form>

                        <hr>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead><tr><th>Categoría</th><th>Nombre</th><th>SKU</th><th>Marca</th><th>Unidad</th><th>Venta total</th><th>Existencia</th></tr></thead>
                                <tbody>
                                <?php if (empty($data['products'])): ?>
                                    <tr><td colspan="7" class="text-muted text-center">Sin productos</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data['products'] as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['categoria']) ?></td>
                                            <td><?= htmlspecialchars($product['nombre']) ?></td>
                                            <td><?= htmlspecialchars($product['sku']) ?></td>
                                            <td><?= htmlspecialchars($product['marca'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($product['unidad'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($product['precio_venta_total']) ?></td>
                                            <td><?= htmlspecialchars($product['existencia']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-categorias">
                        <form method="post" class="row g-2 mb-3">
                            <input type="hidden" name="action" value="add_category">
                            <input type="hidden" name="return_tab" value="tab-categorias">
                            <div class="col-md-6"><input class="form-control" name="name" placeholder="Nombre de categoría" required></div>
                            <div class="col-md-2"><button class="btn btn-primary" type="submit">Agregar categoría</button></div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-marcas">
                        <form method="post" class="row g-2 mb-3">
                            <input type="hidden" name="action" value="add_brand">
                            <input type="hidden" name="return_tab" value="tab-marcas">
                            <div class="col-md-6"><input class="form-control" name="name" placeholder="Nombre de marca" required></div>
                            <div class="col-md-2"><button class="btn btn-primary" type="submit">Agregar marca</button></div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-unidades">
                        <form method="post" class="row g-2 mb-3">
                            <input type="hidden" name="action" value="add_unit">
                            <input type="hidden" name="return_tab" value="tab-unidades">
                            <div class="col-md-6"><input class="form-control" name="name" placeholder="Nombre de unidad" required></div>
                            <div class="col-md-2"><button class="btn btn-primary" type="submit">Agregar unidad</button></div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab-importacion">
                        <form method="post" id="import-form" class="row g-3">
                            <input type="hidden" name="action" value="import_products">
                            <input type="hidden" name="return_tab" value="tab-importacion">
                            <input type="hidden" name="import_payload" id="import_payload">
                            <div class="col-md-6"><label class="form-label">Planilla Excel/CSV</label><input class="form-control" id="excel-file" type="file" accept=".xlsx,.xls,.csv" required></div>
                            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-success" type="submit">Importar planilla</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "partials/footer.php" ?>
</div>
