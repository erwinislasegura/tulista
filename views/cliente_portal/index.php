<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Nueva cotización</h5>
    <form method="post" class="tl-minimal-form">
        <input type="hidden" name="action" value="crear_cotizacion">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th></tr></thead>
                <tbody>
                    <?php foreach ($data['productos'] as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td><?= htmlspecialchars($producto['precio_venta_total']) ?></td>
                            <td><input type="number" min="0" class="form-control tl-compact-input" name="items[<?= (int) $producto['id'] ?>]" value="0"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary" type="submit">Enviar cotización</button>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Historial</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
            <tr><td><?= (int) $cotizacion['id'] ?></td><td><?= htmlspecialchars($cotizacion['estado']) ?></td><td><?= htmlspecialchars($cotizacion['total']) ?></td><td><?= htmlspecialchars($cotizacion['fecha']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
