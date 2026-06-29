<?php
$products = $data['products'] ?? [];
$productImages = $data['product_images'] ?? [];
$money = static fn ($value): string => '$' . number_format((float) $value, 0, ',', '.');
?>

<style>
.tl-products-toolbar { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; margin-bottom: 14px; }
.tl-products-toolbar .form-control { max-width: 320px; }
.tl-product-thumb { align-items: center; background: #fff7ef; border: 1px solid #eadfce; border-radius: 10px; display: inline-flex; height: 46px; justify-content: center; overflow: hidden; width: 46px; }
.tl-product-thumb img { height: 100%; object-fit: cover; width: 100%; }
.tl-product-thumb i { color: #9d93a3; font-size: 22px; }
.tl-product-name { color: #27183d; font-weight: 700; line-height: 1.2; }
.tl-product-meta { color: #6d6277; font-size: 12px; margin-top: 3px; }
.tl-products-table thead th { white-space: nowrap; }
.tl-products-table td { vertical-align: middle; }
.tl-product-gallery { display: grid; gap: 10px; grid-template-columns: repeat(3, 1fr); }
.tl-product-gallery img { aspect-ratio: 1 / .78; border: 1px solid #eadfce; border-radius: 12px; object-fit: cover; width: 100%; }
.tl-edit-gallery { display: grid; gap: 10px; grid-template-columns: repeat(3, 1fr); }
.tl-edit-gallery label { border: 1px solid #eadfce; border-radius: 12px; padding: 8px; }
.tl-edit-gallery img { aspect-ratio: 1 / .7; border-radius: 9px; object-fit: cover; width: 100%; }
@media (max-width: 767.98px) { .tl-products-toolbar .form-control { max-width: 100%; width: 100%; } .tl-product-gallery, .tl-edit-gallery { grid-template-columns: 1fr; } }
</style>

<div class="tl-products-toolbar">
    <div>
        <h6 class="tl-section-title mb-1">Catálogo de productos</h6>
        <p class="text-muted small mb-0">Gestiona imágenes, precios, stock y acciones desde un listado compacto.</p>
    </div>
    <input type="search" id="productosSearch" class="form-control form-control-sm" placeholder="Buscar producto, SKU, categoría o marca">
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle tl-products-table" id="productosTable">
        <thead>
            <tr><th>Producto</th><th>Categoría</th><th>Marca</th><th>Venta total</th><th>Stock</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
            <tr><td colspan="6" class="text-muted text-center py-4">Sin productos registrados</td></tr>
        <?php else: foreach ($products as $product): ?>
            <?php
            $productId = (int) $product['id'];
            $images = $productImages[$productId] ?? [];
            $principal = $product['imagen_principal'] ?: ($images[0]['ruta'] ?? '');
            ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="tl-product-thumb"><?php if ($principal): ?><img src="<?= htmlspecialchars($principal) ?>" alt="<?= htmlspecialchars($product['nombre']) ?>"><?php else: ?><i class="bx bx-image"></i><?php endif; ?></span>
                        <span><span class="tl-product-name"><?= htmlspecialchars($product['nombre']) ?></span><span class="tl-product-meta">SKU: <?= htmlspecialchars($product['sku'] ?: '-') ?> · <?= htmlspecialchars($product['modelo'] ?: 'Sin modelo') ?></span></span>
                    </div>
                </td>
                <td><?= htmlspecialchars($product['categoria']) ?></td>
                <td><?= htmlspecialchars($product['marca'] ?: '-') ?></td>
                <td><strong><?= $money($product['precio_venta_total']) ?></strong></td>
                <td><span class="badge bg-light text-dark"><?= (int) $product['existencia'] ?></span></td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">Acciones</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#viewProduct<?= $productId ?>"><i class="bx bx-show me-2"></i>Ver</button></li>
                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editProduct<?= $productId ?>"><i class="bx bx-edit me-2"></i>Editar</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" onsubmit="return confirm('¿Eliminar este producto? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="return_url" value="apps-productos.php">
                                    <input type="hidden" name="id" value="<?= $productId ?>">
                                    <button class="dropdown-item text-danger" type="submit"><i class="bx bx-trash me-2"></i>Eliminar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php foreach ($products as $product): ?>
    <?php
    $productId = (int) $product['id'];
    $images = $productImages[$productId] ?? [];
    ?>
    <div class="modal fade" id="viewProduct<?= $productId ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title mb-0"><?= htmlspecialchars($product['nombre']) ?></h5><small class="text-muted">SKU <?= htmlspecialchars($product['sku'] ?: '-') ?></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <?php if (!empty($images)): ?><div class="tl-product-gallery"><?php foreach ($images as $image): ?><img src="<?= htmlspecialchars($image['ruta']) ?>" alt="<?= htmlspecialchars($product['nombre']) ?>"><?php endforeach; ?></div><?php else: ?><div class="text-muted border rounded-3 p-4 text-center">Sin fotos registradas</div><?php endif; ?>
                    </div>
                    <div class="col-md-7">
                        <dl class="row mb-0 small">
                            <dt class="col-5">Categoría</dt><dd class="col-7"><?= htmlspecialchars($product['categoria']) ?></dd>
                            <dt class="col-5">Marca</dt><dd class="col-7"><?= htmlspecialchars($product['marca'] ?: '-') ?></dd>
                            <dt class="col-5">Unidad</dt><dd class="col-7"><?= htmlspecialchars($product['unidad'] ?: '-') ?></dd>
                            <dt class="col-5">Código barras</dt><dd class="col-7"><?= htmlspecialchars($product['codigo_barras'] ?: '-') ?></dd>
                            <dt class="col-5">Costo neto</dt><dd class="col-7"><?= $money($product['costo_neto']) ?></dd>
                            <dt class="col-5">Venta total</dt><dd class="col-7"><strong><?= $money($product['precio_venta_total']) ?></strong></dd>
                            <dt class="col-5">Stock mínimo</dt><dd class="col-7"><?= (int) $product['stock_minimo'] ?></dd>
                            <dt class="col-5">Existencia</dt><dd class="col-7"><?= (int) $product['existencia'] ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cerrar</button></div>
        </div></div>
    </div>

    <div class="modal fade" id="editProduct<?= $productId ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><div><h5 class="modal-title mb-0">Editar producto</h5><small class="text-muted"><?= htmlspecialchars($product['nombre']) ?></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_product"><input type="hidden" name="return_url" value="apps-productos.php"><input type="hidden" name="id" value="<?= $productId ?>">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Categoría</label><select class="form-select tl-compact-input" name="categoria_id" required><?php foreach ($data['categories'] as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) $product['categoria_id'] === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control tl-compact-input" name="nombre" value="<?= htmlspecialchars($product['nombre']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">SKU</label><input class="form-control tl-compact-input" name="sku" value="<?= htmlspecialchars($product['sku']) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Marca</label><select class="form-select tl-compact-input" name="marca_id"><option value="">Seleccionar</option><?php foreach ($data['brands'] as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($product['marca_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label class="form-label">Modelo</label><input class="form-control tl-compact-input" name="modelo" value="<?= htmlspecialchars($product['modelo'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Unidad</label><select class="form-select tl-compact-input" name="unidad_id"><option value="">Seleccionar</option><?php foreach ($data['units'] as $item): ?><option value="<?= (int) $item['id'] ?>" <?= (int) ($product['unidad_id'] ?? 0) === (int) $item['id'] ? 'selected' : '' ?>><?= htmlspecialchars($item['descripcion'] . ' (' . $item['abreviatura'] . ')') ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3"><label class="form-label">Código barras</label><input class="form-control tl-compact-input" name="codigo_barras" value="<?= htmlspecialchars($product['codigo_barras'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Tipo item</label><input class="form-control tl-compact-input" name="tipo_item" value="<?= htmlspecialchars($product['tipo_item'] ?? '') ?>"></div>
                        <div class="col-md-2"><label class="form-label">Costo neto</label><input class="form-control tl-compact-input" name="costo_neto" type="number" step="0.01" value="<?= htmlspecialchars($product['costo_neto']) ?>"></div>
                        <div class="col-md-2"><label class="form-label">Venta neto</label><input class="form-control tl-compact-input" name="precio_venta_neto" type="number" step="0.01" value="<?= htmlspecialchars($product['precio_venta_neto']) ?>"></div>
                        <div class="col-md-2"><label class="form-label">Venta total</label><input class="form-control tl-compact-input" name="precio_venta_total" type="number" step="0.01" value="<?= htmlspecialchars($product['precio_venta_total']) ?>"></div>
                        <div class="col-md-2"><label class="form-label">Stock mínimo</label><input class="form-control tl-compact-input" name="stock_minimo" type="number" value="<?= (int) $product['stock_minimo'] ?>"></div>
                        <div class="col-md-2"><label class="form-label">Comisión</label><input class="form-control tl-compact-input" name="comision_vendedor" type="number" step="0.01" value="<?= htmlspecialchars($product['comision_vendedor']) ?>"></div>
                        <div class="col-md-2"><label class="form-label">Existencia</label><input class="form-control tl-compact-input" name="existencia" type="number" value="<?= (int) $product['existencia'] ?>"></div>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Foto principal actual</label>
                            <?php if (!empty($images)): ?><div class="tl-edit-gallery"><?php foreach ($images as $image): ?><label><img src="<?= htmlspecialchars($image['ruta']) ?>" alt=""><span class="form-check mt-2"><input class="form-check-input" type="radio" name="principal_image_id" value="<?= (int) $image['id'] ?>" <?= (int) $image['es_principal'] === 1 ? 'checked' : '' ?>> <span class="form-check-label">Principal</span></span></label><?php endforeach; ?></div><?php else: ?><p class="text-muted small">Sin fotos actuales.</p><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reemplazar fotos (máximo 3)</label>
                            <input class="form-control mb-2" name="product_images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><div class="form-check mb-2"><input class="form-check-input" type="radio" name="principal_image_index" value="0" checked><label class="form-check-label">Foto 1 principal</label></div>
                            <input class="form-control mb-2" name="product_images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><div class="form-check mb-2"><input class="form-check-input" type="radio" name="principal_image_index" value="1"><label class="form-check-label">Foto 2 principal</label></div>
                            <input class="form-control mb-2" name="product_images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><div class="form-check"><input class="form-check-input" type="radio" name="principal_image_index" value="2"><label class="form-check-label">Foto 3 principal</label></div>
                            <p class="text-muted small mt-2 mb-0">Si subes fotos nuevas, reemplazarán las actuales.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar cambios</button></div>
            </form>
        </div></div>
    </div>
<?php endforeach; ?>

<script>
(() => {
  const search = document.getElementById('productosSearch');
  const rows = Array.from(document.querySelectorAll('#productosTable tbody tr'));
  search?.addEventListener('input', () => {
    const term = search.value.toLowerCase().trim();
    rows.forEach(row => row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none');
  });
})();
</script>
