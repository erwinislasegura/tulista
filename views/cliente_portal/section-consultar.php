<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Consultar catálogo</h5>
        <p class="text-muted mb-3">Explora el catálogo, revisa existencias y filtra por nombre de producto.</p>

        <div class="mb-3">
            <label for="buscar-catalogo" class="form-label">Buscar producto</label>
            <input type="search" id="buscar-catalogo" class="form-control" placeholder="Ej: Etiqueta térmica 100x150">
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Producto</th><th>Stock</th><th>Precio</th></tr></thead>
                <tbody>
                <?php foreach ($data['productos'] as $producto): ?>
                    <?php $stock = (int) ($producto['existencia'] ?? 0); ?>
                    <tr data-consulta-row data-name="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                        <td><span class="tl-product-name"><?= htmlspecialchars($producto['nombre']) ?></span></td>
                        <td>
                            <?php if ($stock > 0): ?>
                                <span class="badge bg-success-subtle text-success">Disponible: <?= $stock ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Sin existencia</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($formatCurrency((float) $producto['precio_venta_total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('buscar-catalogo');
    const rows = Array.from(document.querySelectorAll('[data-consulta-row]'));

    if (!searchInput || rows.length === 0) {
        return;
    }

    searchInput.addEventListener('input', () => {
        const term = searchInput.value.trim().toLowerCase();
        rows.forEach((row) => {
            row.classList.toggle('d-none', !row.dataset.name.includes(term));
        });
    });
})();
</script>
