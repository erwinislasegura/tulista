<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Crear nueva cotización</h5>
                <p class="text-muted mb-0">Selecciona productos y cantidades para enviar tu solicitud al equipo comercial.</p>
            </div>
            <span class="badge rounded-pill text-bg-info">Paso 1 de 3</span>
        </div>

        <form method="post" id="cotizacion-form">
            <input type="hidden" name="action" value="crear_cotizacion">
            <input type="hidden" name="return_url" value="cotizar.php?view=cotizar">

            <div class="row g-3 mb-3">
                <div class="col-lg-8">
                    <label for="buscar-producto" class="form-label">Buscar producto</label>
                    <input type="search" id="buscar-producto" class="form-control" placeholder="Ej: Etiqueta térmica 100x150">
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
                    <tr><th>Producto</th><th>Precio</th><th style="width:220px;">Cantidad</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data['productos'] as $producto): ?>
                        <?php $precio = (float) $producto['precio_venta_total']; ?>
                        <tr data-product-row data-name="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                            <td>
                                <strong><?= htmlspecialchars($producto['nombre']) ?></strong>
                            </td>
                            <td data-precio="<?= $precio ?>"><?= htmlspecialchars($formatCurrency($precio)) ?></td>
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

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-muted">Tip: usa los botones +/- para completar más rápido tu pedido.</small>
                <button class="btn btn-primary" type="submit">Enviar cotización</button>
            </div>
        </form>
    </div>
</div>
