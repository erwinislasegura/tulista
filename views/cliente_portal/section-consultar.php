<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Consultar catálogo</h5>
        <p class="text-muted mb-3">Explora el catálogo de productos disponibles para cotizar.</p>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Producto</th><th>Precio</th></tr></thead>
                <tbody>
                <?php foreach ($data['productos'] as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td><?= htmlspecialchars($formatCurrency((float) $producto['precio_venta_total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
