<?php
$products = $data['products'] ?? [];
$productImages = $data['product_images'] ?? [];
$money = static fn ($value): string => '$' . number_format((float) $value, 0, ',', '.');
$editCatalog = [
    'categories' => array_map(static fn ($item): array => ['id' => (int) $item['id'], 'label' => (string) $item['nombre']], $data['categories'] ?? []),
    'brands' => array_map(static fn ($item): array => ['id' => (int) $item['id'], 'label' => (string) $item['nombre']], $data['brands'] ?? []),
    'units' => array_map(static fn ($item): array => ['id' => (int) $item['id'], 'label' => (string) $item['descripcion'] . ' (' . (string) $item['abreviatura'] . ')'], $data['units'] ?? []),
];
$productsPayload = [];
foreach ($products as $product) {
    $productId = (int) $product['id'];
    $productsPayload[$productId] = $product;
    $productsPayload[$productId]['id'] = $productId;
    $productsPayload[$productId]['images'] = $productImages[$productId] ?? [];
}
$jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
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
                        <span class="tl-product-thumb"><?php if ($principal): ?><img loading="lazy" src="<?= htmlspecialchars($principal) ?>" alt="<?= htmlspecialchars($product['nombre']) ?>"><?php else: ?><i class="bx bx-image"></i><?php endif; ?></span>
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
                            <li><button class="dropdown-item js-view-product" type="button" data-product-id="<?= $productId ?>"><i class="bx bx-show me-2"></i>Ver</button></li>
                            <li><button class="dropdown-item js-edit-product" type="button" data-product-id="<?= $productId ?>"><i class="bx bx-edit me-2"></i>Editar</button></li>
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

<div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div><h5 class="modal-title mb-0" id="viewProductTitle"></h5><small class="text-muted" id="viewProductSku"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" id="viewProductBody"></div>
        <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cerrar</button></div>
    </div></div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><div><h5 class="modal-title mb-0">Editar producto</h5><small class="text-muted" id="editProductSubtitle"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="post" enctype="multipart/form-data" id="editProductForm">
            <input type="hidden" name="action" value="update_product"><input type="hidden" name="return_url" value="apps-productos.php"><input type="hidden" name="id" id="editProductId">
            <div class="modal-body" id="editProductBody"></div>
            <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar cambios</button></div>
        </form>
    </div></div>
</div>

<script type="application/json" id="productosPayload"><?= json_encode($productsPayload, $jsonOptions) ?></script>
<script type="application/json" id="productosCatalogPayload"><?= json_encode($editCatalog, $jsonOptions) ?></script>
<script>
(() => {
  const search = document.getElementById('productosSearch');
  const rows = Array.from(document.querySelectorAll('#productosTable tbody tr'));
  const products = JSON.parse(document.getElementById('productosPayload')?.textContent || '{}');
  const catalog = JSON.parse(document.getElementById('productosCatalogPayload')?.textContent || '{}');
  const money = value => '$' + Number(value || 0).toLocaleString('es-CL', {maximumFractionDigits: 0});
  const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
  const modal = id => window.bootstrap?.Modal.getOrCreateInstance(document.getElementById(id));
  const optionList = (items, selected, placeholder = 'Seleccionar') => [`<option value="">${placeholder}</option>`].concat((items || []).map(item => `<option value="${item.id}" ${Number(selected || 0) === Number(item.id) ? 'selected' : ''}>${escapeHtml(item.label)}</option>`)).join('');

  search?.addEventListener('input', () => {
    const term = search.value.toLowerCase().trim();
    rows.forEach(row => row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none');
  });

  document.addEventListener('click', event => {
    const viewButton = event.target.closest('.js-view-product');
    const editButton = event.target.closest('.js-edit-product');
    if (viewButton) showView(products[viewButton.dataset.productId]);
    if (editButton) showEdit(products[editButton.dataset.productId]);
  });

  function showView(product) {
    if (!product) return;
    const images = product.images || [];
    document.getElementById('viewProductTitle').textContent = product.nombre || '';
    document.getElementById('viewProductSku').textContent = `SKU ${product.sku || '-'}`;
    document.getElementById('viewProductBody').innerHTML = `<div class="row g-3"><div class="col-md-5">${images.length ? `<div class="tl-product-gallery">${images.map(image => `<img loading="lazy" src="${escapeHtml(image.ruta)}" alt="${escapeHtml(product.nombre)}">`).join('')}</div>` : '<div class="text-muted border rounded-3 p-4 text-center">Sin fotos registradas</div>'}</div><div class="col-md-7"><dl class="row mb-0 small"><dt class="col-5">Categoría</dt><dd class="col-7">${escapeHtml(product.categoria)}</dd><dt class="col-5">Marca</dt><dd class="col-7">${escapeHtml(product.marca || '-')}</dd><dt class="col-5">Unidad</dt><dd class="col-7">${escapeHtml(product.unidad || '-')}</dd><dt class="col-5">Código barras</dt><dd class="col-7">${escapeHtml(product.codigo_barras || '-')}</dd><dt class="col-5">Costo neto</dt><dd class="col-7">${money(product.costo_neto)}</dd><dt class="col-5">Venta total</dt><dd class="col-7"><strong>${money(product.precio_venta_total)}</strong></dd><dt class="col-5">Stock mínimo</dt><dd class="col-7">${Number(product.stock_minimo || 0)}</dd><dt class="col-5">Existencia</dt><dd class="col-7">${Number(product.existencia || 0)}</dd></dl></div></div>`;
    modal('viewProductModal')?.show();
  }

  function showEdit(product) {
    if (!product) return;
    const images = product.images || [];
    document.getElementById('editProductId').value = product.id;
    document.getElementById('editProductSubtitle').textContent = product.nombre || '';
    document.getElementById('editProductBody').innerHTML = `<div class="row g-2"><div class="col-md-4"><label class="form-label">Categoría</label><select class="form-select tl-compact-input" name="categoria_id" required>${optionList(catalog.categories, product.categoria_id, 'Seleccionar')}</select></div><div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control tl-compact-input" name="nombre" value="${escapeHtml(product.nombre)}" required></div><div class="col-md-4"><label class="form-label">SKU</label><input class="form-control tl-compact-input" name="sku" value="${escapeHtml(product.sku)}"></div><div class="col-md-4"><label class="form-label">Marca</label><select class="form-select tl-compact-input" name="marca_id">${optionList(catalog.brands, product.marca_id, 'Seleccionar')}</select></div><div class="col-md-4"><label class="form-label">Modelo</label><input class="form-control tl-compact-input" name="modelo" value="${escapeHtml(product.modelo || '')}"></div><div class="col-md-4"><label class="form-label">Unidad</label><select class="form-select tl-compact-input" name="unidad_id">${optionList(catalog.units, product.unidad_id, 'Seleccionar')}</select></div><div class="col-md-3"><label class="form-label">Código barras</label><input class="form-control tl-compact-input" name="codigo_barras" value="${escapeHtml(product.codigo_barras || '')}"></div><div class="col-md-3"><label class="form-label">Tipo item</label><input class="form-control tl-compact-input" name="tipo_item" value="${escapeHtml(product.tipo_item || '')}"></div>${numberInput('Costo neto','costo_neto',product.costo_neto,2)}${numberInput('Venta neto','precio_venta_neto',product.precio_venta_neto,2)}${numberInput('Venta total','precio_venta_total',product.precio_venta_total,2)}${numberInput('Stock mínimo','stock_minimo',product.stock_minimo,0)}${numberInput('Comisión','comision_vendedor',product.comision_vendedor,2)}${numberInput('Existencia','existencia',product.existencia,0)}</div><hr><div class="row g-3"><div class="col-md-6"><label class="form-label">Foto principal actual</label>${images.length ? `<div class="tl-edit-gallery">${images.map(image => `<label><img loading="lazy" src="${escapeHtml(image.ruta)}" alt=""><span class="form-check mt-2"><input class="form-check-input" type="radio" name="principal_image_id" value="${Number(image.id)}" ${Number(image.es_principal) === 1 ? 'checked' : ''}> <span class="form-check-label">Principal</span></span></label>`).join('')}</div>` : '<p class="text-muted small">Sin fotos actuales.</p>'}</div><div class="col-md-6"><label class="form-label">Reemplazar fotos (máximo 3)</label>${[0,1,2].map(i => `<input class="form-control mb-2" name="product_images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><div class="form-check ${i < 2 ? 'mb-2' : ''}"><input class="form-check-input" type="radio" name="principal_image_index" value="${i}" ${i === 0 ? 'checked' : ''}><label class="form-check-label">Foto ${i + 1} principal</label></div>`).join('')}<p class="text-muted small mt-2 mb-0">Si subes fotos nuevas, reemplazarán las actuales.</p></div></div>`;
    modal('editProductModal')?.show();
  }

  function numberInput(label, name, value, decimals) {
    return `<div class="col-md-2"><label class="form-label">${label}</label><input class="form-control tl-compact-input" name="${name}" type="number" step="${decimals ? '0.01' : '1'}" value="${escapeHtml(value ?? 0)}"></div>`;
  }
})();
</script>
