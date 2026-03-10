<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Categoría</th><th>Nombre</th><th>SKU</th><th>Marca</th><th>Unidad</th><th>Venta total</th><th>Existencia</th></tr></thead>
        <tbody>
        <?php if (empty($data['products'])): ?>
            <tr><td colspan="7" class="text-muted text-center">Sin productos</td></tr>
        <?php else: foreach ($data['products'] as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['categoria']) ?></td>
                <td><?= htmlspecialchars($product['nombre']) ?></td>
                <td><?= htmlspecialchars($product['sku']) ?></td>
                <td><?= htmlspecialchars($product['marca'] ?? '') ?></td>
                <td><?= htmlspecialchars($product['unidad'] ?? '') ?></td>
                <td><?= htmlspecialchars($product['precio_venta_total']) ?></td>
                <td><?= htmlspecialchars($product['existencia']) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
