<?php
$products = $data['products'] ?? [];
$productImages = $data['product_images'] ?? [];
$productsWithImagesCount = count(array_filter($productImages, static fn ($images): bool => !empty($images)));
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
.tl-products-summary { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; }
.tl-products-image-count { background: #f6f0ff; border: 1px solid #e4d6ff; border-radius: 999px; color: #4d2f78; display: inline-flex; font-size: 12px; font-weight: 700; gap: 6px; line-height: 1; padding: 7px 10px; white-space: nowrap; }
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
        <div class="tl-products-summary mb-1">
            <h6 class="tl-section-title mb-0">Catálogo de productos</h6>
            <span class="tl-products-image-count"><i class="bx bx-image-alt"></i><?= $productsWithImagesCount ?> con imagen</span>
        </div>
        <p class="text-muted small mb-0">Gestiona imágenes, precios, stock y acciones desde un listado compacto.</p>
    </div>
    <input type="search" id="productosSearch" class="form-control form-control-sm" placeholder="Buscar por producto, SKU, código de barras, categoría o marca">
</div>

<div class="alert alert-info d-flex flex-column flex-lg-row gap-3 align-items-lg-end justify-content-between" role="region" aria-label="Asociar categorías por lote">
    <div>
        <h6 class="mb-1">Asociar categorías por lote</h6>
        <p class="small mb-0">Selecciona una categoría destino y filtra los productos por categoría actual, marca, modelo, nombre, SKU o código de barras.</p>
    </div>
    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#bulkCategoryProducts" aria-expanded="false" aria-controls="bulkCategoryProducts">
        <i class="bx bx-purchase-tag-alt me-1"></i>Asignar categoría por criterios
    </button>
</div>

<div class="collapse mb-3" id="bulkCategoryProducts">
    <div class="card border-primary">
        <div class="card-body">
            <form method="post" class="row g-3" onsubmit="return confirm('¿Asignar la categoría seleccionada a todos los productos que coincidan con estos criterios?');">
                <input type="hidden" name="action" value="bulk_update_product_category">
                <input type="hidden" name="return_url" value="apps-productos.php">
                <div class="col-md-3"><label class="form-label">Categoría destino</label><select class="form-select form-select-sm" name="bulk_new_categoria_id" required><option value="">Seleccionar categoría</option><?php foreach (($data['categories'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Categoría actual</label><select class="form-select form-select-sm" name="bulk_filter_categoria_id"><option value="">Cualquier categoría actual</option><?php foreach (($data['categories'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Marca</label><select class="form-select form-select-sm" name="bulk_filter_marca_id"><option value="">Cualquier marca</option><?php foreach (($data['brands'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Modelo contiene</label><input class="form-control form-control-sm" name="bulk_filter_modelo" placeholder="Modelo"></div>
                <div class="col-md-3"><label class="form-label">Nombre contiene</label><input class="form-control form-control-sm" name="bulk_filter_nombre" placeholder="Producto"></div>
                <div class="col-md-3"><label class="form-label">SKU contiene</label><input class="form-control form-control-sm" name="bulk_filter_sku" placeholder="SKU"></div>
                <div class="col-md-3"><label class="form-label">Código barras contiene</label><input class="form-control form-control-sm" name="bulk_filter_codigo_barras" placeholder="Código de barras"></div>
                <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary btn-sm w-100" type="submit">Asociar categoría</button></div>
                <div class="col-12"><p class="text-muted small mb-0"><strong>Nota:</strong> todos los criterios se combinan y se omiten productos que ya pertenecen a la categoría destino.</p></div>
            </form>
        </div>
    </div>
</div>

<div class="alert alert-warning d-flex flex-column flex-lg-row gap-3 align-items-lg-end justify-content-between" role="region" aria-label="Acciones por lote">
    <div>
        <h6 class="mb-1">Acciones por lote</h6>
        <p class="small mb-0">Elimina productos que coincidan con una marca, modelo, categoría u otro criterio. Esta acción no se puede deshacer.</p>
    </div>
    <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#bulkDeleteProducts" aria-expanded="false" aria-controls="bulkDeleteProducts">
        <i class="bx bx-trash me-1"></i>Eliminar por criterios
    </button>
</div>

<div class="collapse mb-3" id="bulkDeleteProducts">
    <div class="card border-danger">
        <div class="card-body">
            <form method="post" class="row g-3" onsubmit="return confirm('¿Eliminar por lote todos los productos que coincidan con estos criterios? Esta acción no se puede deshacer.');">
                <input type="hidden" name="action" value="bulk_delete_products">
                <input type="hidden" name="return_url" value="apps-productos.php">
                <div class="col-md-3"><label class="form-label">Categoría</label><select class="form-select form-select-sm" name="bulk_categoria_id"><option value="">Cualquier categoría</option><?php foreach (($data['categories'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Marca</label><select class="form-select form-select-sm" name="bulk_marca_id"><option value="">Cualquier marca</option><?php foreach (($data['brands'] ?? []) as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label">Modelo contiene</label><input class="form-control form-control-sm" name="bulk_modelo" placeholder="Modelo"></div>
                <div class="col-md-2"><label class="form-label">Nombre contiene</label><input class="form-control form-control-sm" name="bulk_nombre" placeholder="Producto"></div>
                <div class="col-md-2"><label class="form-label">SKU contiene</label><input class="form-control form-control-sm" name="bulk_sku" placeholder="SKU"></div>
                <div class="col-md-3"><label class="form-label">Código barras contiene</label><input class="form-control form-control-sm" name="bulk_codigo_barras" placeholder="Código de barras"></div>
                <div class="col-md-3"><label class="form-label">Confirmación</label><input class="form-control form-control-sm" name="bulk_confirm" placeholder="Escribe ELIMINAR" required></div>
                <div class="col-md-3 d-flex align-items-end"><button class="btn btn-danger btn-sm w-100" type="submit">Eliminar productos filtrados</button></div>
                <div class="col-12"><p class="text-danger small mb-0"><strong>Importante:</strong> todos los criterios se combinan. Por ejemplo, marca + modelo elimina solo productos de esa marca cuyo modelo contenga el texto indicado.</p></div>
            </form>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle tl-products-table" id="productosTable">
        <thead>
            <tr><th>Producto</th><th>SKU</th><th>Código de barras</th><th>Categoría</th><th>Marca</th><th>Venta total</th><th>Stock</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
            <tr><td colspan="8" class="text-muted text-center py-4">Sin productos registrados</td></tr>
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
                        <span><span class="tl-product-name"><?= htmlspecialchars($product['nombre']) ?></span><span class="tl-product-meta"><?= htmlspecialchars($product['modelo'] ?: 'Sin modelo') ?></span></span>
                    </div>
                </td>
                <td><code><?= htmlspecialchars($product['sku'] ?: '-') ?></code></td>
                <td><?= htmlspecialchars($product['codigo_barras'] ?: '-') ?></td>
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
    document.getElementById('viewProductBody').innerHTML = `<div class="row g-3"><div class="col-md-5">${images.length ? `<div class="tl-product-gallery">${images.map(image => `<img loading="lazy" src="${escapeHtml(image.ruta)}" alt="${escapeHtml(product.nombre)}">`).join('')}</div>` : '<div class="text-muted border rounded-3 p-4 text-center">Sin fotos registradas</div>'}</div><div class="col-md-7"><dl class="row mb-0 small"><dt class="col-5">Categoría</dt><dd class="col-7">${escapeHtml(product.categoria)}</dd><dt class="col-5">Marca</dt><dd class="col-7">${escapeHtml(product.marca || '-')}</dd><dt class="col-5">Unidad</dt><dd class="col-7">${escapeHtml(product.unidad || '-')}</dd><dt class="col-5">Código barras</dt><dd class="col-7">${escapeHtml(product.codigo_barras || '-')}</dd><dt class="col-5">Costo neto</dt><dd class="col-7">${money(product.costo_neto)}</dd><dt class="col-5">IVA</dt><dd class="col-7">${Number(product.afecto_iva || 0) === 1 ? 'Afecto 19%' : 'Exento'}</dd><dt class="col-5">Venta total</dt><dd class="col-7"><strong>${money(product.precio_venta_total)}</strong></dd><dt class="col-5">Stock mínimo</dt><dd class="col-7">${Number(product.stock_minimo || 0)}</dd><dt class="col-5">Existencia</dt><dd class="col-7">${Number(product.existencia || 0)}</dd></dl></div></div>`;
    modal('viewProductModal')?.show();
  }

  function showEdit(product) {
    if (!product) return;
    const images = product.images || [];
    document.getElementById('editProductId').value = product.id;
    document.getElementById('editProductSubtitle').textContent = product.nombre || '';
    document.getElementById('editProductBody').innerHTML = `<div class="row g-2"><div class="col-md-4"><label class="form-label">Categoría</label><select class="form-select tl-compact-input" name="categoria_id" required>${optionList(catalog.categories, product.categoria_id, 'Seleccionar')}</select></div><div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control tl-compact-input" name="nombre" value="${escapeHtml(product.nombre)}" required></div><div class="col-md-4"><label class="form-label">SKU</label><input class="form-control tl-compact-input" name="sku" value="${escapeHtml(product.sku)}"></div><div class="col-md-4"><label class="form-label">Marca</label><select class="form-select tl-compact-input" name="marca_id">${optionList(catalog.brands, product.marca_id, 'Seleccionar')}</select></div><div class="col-md-4"><label class="form-label">Modelo</label><input class="form-control tl-compact-input" name="modelo" value="${escapeHtml(product.modelo || '')}"></div><div class="col-md-4"><label class="form-label">Unidad</label><select class="form-select tl-compact-input" name="unidad_id">${optionList(catalog.units, product.unidad_id, 'Seleccionar')}</select></div><div class="col-md-3"><label class="form-label">Código barras</label><input class="form-control tl-compact-input" name="codigo_barras" value="${escapeHtml(product.codigo_barras || '')}"></div><div class="col-md-3"><label class="form-label">Tipo item</label><input class="form-control tl-compact-input" name="tipo_item" value="${escapeHtml(product.tipo_item || '')}"></div>${numberInput('Costo neto','costo_neto',product.costo_neto,2)}${numberInput('Precio neto','precio_venta_neto',product.precio_venta_neto,2, 'js-net-price')}${ivaSwitch(product)}${numberInput('Precio con IVA / total','precio_venta_total',product.precio_venta_total,2, 'js-total-price', true)}${numberInput('Stock mínimo','stock_minimo',product.stock_minimo,0)}${numberInput('Comisión','comision_vendedor',product.comision_vendedor,2)}${numberInput('Existencia','existencia',product.existencia,0)}</div><hr><div class="row g-3"><div class="col-md-6"><label class="form-label">Foto principal actual</label>${images.length ? `<div class="tl-edit-gallery">${images.map(image => `<label><img loading="lazy" src="${escapeHtml(image.ruta)}" alt=""><span class="form-check mt-2"><input class="form-check-input" type="radio" name="principal_image_id" value="${Number(image.id)}" ${Number(image.es_principal) === 1 ? 'checked' : ''}> <span class="form-check-label">Principal</span></span></label>`).join('')}</div>` : '<p class="text-muted small">Sin fotos actuales.</p>'}</div><div class="col-md-6"><label class="form-label">Reemplazar fotos (máximo 3)</label>${[0,1,2].map(i => `<input class="form-control mb-2" name="product_images[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><div class="form-check ${i < 2 ? 'mb-2' : ''}"><input class="form-check-input" type="radio" name="principal_image_index" value="${i}" ${i === 0 ? 'checked' : ''}><label class="form-check-label">Foto ${i + 1} principal</label></div>`).join('')}<p class="text-muted small mt-2 mb-0">Si subes fotos nuevas, reemplazarán las actuales.</p></div></div>`;
    bindIvaCalculator(document.getElementById('editProductForm'));
    modal('editProductModal')?.show();
  }

  function numberInput(label, name, value, decimals, extraClass = '', readonly = false) {
    return `<div class="col-md-2"><label class="form-label">${label}</label><input class="form-control tl-compact-input ${extraClass}" name="${name}" type="number" step="${decimals ? '0.01' : '1'}" value="${escapeHtml(value ?? 0)}" ${readonly ? 'readonly' : ''}></div>`;
  }

  function ivaSwitch(product) {
    const checked = Number(product.afecto_iva ?? 1) === 1 ? 'checked' : '';
    return `<div class="col-md-4"><label class="form-label">IVA</label><div class="form-check form-switch"><input class="form-check-input js-iva-check" name="afecto_iva" type="checkbox" value="1" ${checked}><label class="form-check-label">Afecto a IVA chileno (19%)</label></div><small class="text-muted">Desmarca para exento.</small></div>`;
  }

  function bindIvaCalculator(form) {
    const net = form?.querySelector('.js-net-price');
    const affected = form?.querySelector('.js-iva-check');
    const total = form?.querySelector('.js-total-price');
    if (!net || !affected || !total) return;
    const calculate = () => {
      const netValue = Number.parseFloat(net.value) || 0;
      const totalValue = affected.checked ? netValue * 1.19 : netValue;
      total.value = totalValue ? totalValue.toFixed(2) : '';
    };
    net.addEventListener('input', calculate);
    affected.addEventListener('change', calculate);
    calculate();
  }
})();
</script>
