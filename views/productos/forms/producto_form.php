<style>
.tl-product-form { background: #fff; border-color: #eadfce; }
.tl-product-media { background: #fffaf4; border: 1px solid #eadfce; border-radius: 14px; padding: 14px; }
.tl-upload-slot { background: #fff; border: 1px dashed #dccbb8; border-radius: 12px; cursor: pointer; display: block; min-height: 138px; padding: 12px; transition: .18s ease; }
.tl-upload-slot:hover { border-color: #ff6b00; box-shadow: 0 8px 22px rgba(39,24,61,.08); transform: translateY(-1px); }
.tl-upload-icon { align-items: center; background: #fff1e5; border-radius: 10px; color: #ff6b00; display: inline-flex; height: 34px; justify-content: center; margin-bottom: 8px; width: 34px; }
.tl-upload-title { color: #27183d; display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; }
</style>
<form method="post" class="tl-minimal-form tl-product-form" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add_product">
    <input type="hidden" name="return_url" value="apps-productos.php">

    <div class="tl-form-card">
        <h6 class="tl-form-card-title">Datos del producto</h6>

        <div class="row g-2 mb-3">
            <div class="col-12">
                <p class="text-muted small mb-1">Información base</p>
            </div>

            <div class="col-md-6 col-xl-4">
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

            <div class="col-md-6 col-xl-4"><label class="form-label">Nombre</label><input class="form-control tl-compact-input" name="nombre" required></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">SKU</label><input class="form-control tl-compact-input" name="sku"></div>

            <div class="col-md-6 col-xl-4">
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

            <div class="col-md-6 col-xl-4"><label class="form-label">Modelo</label><input class="form-control tl-compact-input" name="modelo"></div>

            <div class="col-md-6 col-xl-4">
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

            <div class="col-md-6 col-xl-4"><label class="form-label">Código de barras</label><input class="form-control tl-compact-input" name="codigo_barras"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">Producto / Servicio</label><input class="form-control tl-compact-input" name="tipo_item"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">Existencia</label><input class="form-control tl-compact-input" name="existencia" type="number" step="1"></div>
        </div>

        <div class="row g-2 tl-minimal-form border-top pt-3">
            <div class="col-12">
                <p class="text-muted small mb-1">Precios y control</p>
            </div>

            <div class="col-md-6 col-xl-4 tl-input-group">
                <label class="form-label">Costo neto</label>
                <div class="input-group input-group-sm"><span class="input-group-text">$</span><input class="form-control tl-compact-input" name="costo_neto" type="number" step="0.01"></div>
            </div>
            <div class="col-md-6 col-xl-4 tl-input-group">
                <label class="form-label">Precio neto</label>
                <div class="input-group input-group-sm"><span class="input-group-text">$</span><input class="form-control tl-compact-input js-net-price" name="precio_venta_neto" type="number" step="0.01" min="0"></div>
            </div>
            <div class="col-md-6 col-xl-4">
                <label class="form-label">IVA</label>
                <div class="form-check form-switch pt-1"><input class="form-check-input js-iva-check" name="afecto_iva" type="checkbox" value="1" checked><label class="form-check-label">Afecto a IVA chileno (19%)</label></div>
                <small class="text-muted">Desmarca para productos exentos.</small>
            </div>
            <div class="col-md-6 col-xl-4 tl-input-group">
                <label class="form-label">Precio con IVA / total</label>
                <div class="input-group input-group-sm"><span class="input-group-text">$</span><input class="form-control tl-compact-input js-total-price" name="precio_venta_total" type="number" step="0.01" readonly></div>
            </div>
            <div class="col-md-6 col-xl-4"><label class="form-label">Stock mínimo</label><input class="form-control tl-compact-input" name="stock_minimo" type="number" step="1"></div>
            <div class="col-md-6 col-xl-4"><label class="form-label">Comisión vendedor</label><input class="form-control tl-compact-input" name="comision_vendedor" type="number" step="0.01"></div>
        </div>

        <div class="tl-product-media mt-3">
            <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-2">
                <div>
                    <h6 class="tl-form-card-title mb-1">Fotos del producto</h6>
                    <p class="text-muted small mb-0">Sube hasta 3 imágenes y marca cuál será la principal para el catálogo.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary">Máximo 3 fotos</span>
            </div>
            <div class="row g-2">
                <?php for ($i = 0; $i < 3; $i++): ?>
                    <div class="col-md-4">
                        <label class="tl-upload-slot">
                            <span class="tl-upload-icon"><i class="bx bx-image-add"></i></span>
                            <span class="tl-upload-title">Foto <?= $i + 1 ?></span>
                            <input class="form-control tl-compact-input" name="product_images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                            <span class="form-check mt-2"><input class="form-check-input" type="radio" name="principal_image_index" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>> <span class="form-check-label">Principal</span></span>
                        </label>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="col-12">
            <div class="tl-form-actions d-flex justify-content-end mt-3">
                <button class="btn btn-primary px-4" type="submit"><i class="bx bx-save me-1"></i>Guardar producto</button>
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

<script>
(() => {
  const IVA_CHILENO = 0.19;
  document.querySelectorAll('form').forEach(form => {
    const net = form.querySelector('.js-net-price');
    const affected = form.querySelector('.js-iva-check');
    const total = form.querySelector('.js-total-price');
    if (!net || !affected || !total) return;

    const calculate = () => {
      const netValue = Number.parseFloat(net.value) || 0;
      const totalValue = affected.checked ? netValue * (1 + IVA_CHILENO) : netValue;
      total.value = totalValue ? totalValue.toFixed(2) : '';
    };

    net.addEventListener('input', calculate);
    affected.addEventListener('change', calculate);
    calculate();
  });
})();
</script>
