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

            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary tl-cotizar-submit-btn" type="submit">Crear cotización</button>
            </div>

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
                        <tr data-product-row data-name="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>" data-stock="<?= $stock ?>" class="<?= $stock <= 0 ? 'd-none' : '' ?>">
                            <td>
                                <span class="tl-product-name"><?= htmlspecialchars($producto['nombre']) ?></span>
                            </td>
                            <td>
                                <?php if ($stock > 0): ?>
                                    <span class="badge bg-success-subtle text-success">Disponible: <?= $stock ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Sin existencia</span>
                                <?php endif; ?>
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
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="sin-stock-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cotización con productos sin existencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="sin-stock-modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-danger" id="confirmar-cotizacion-sin-stock">Continuar cotización</button>
            </div>
        </div>
    </div>
</div>
