<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>
<h5>Nueva cotización</h5>
<form method="post">
    <input type="hidden" name="action" value="crear_cotizacion">
    <table class="table table-sm">
        <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th></tr></thead>
        <tbody>
            <?php foreach ($data['productos'] as $producto): ?>
                <tr>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td><?= htmlspecialchars($producto['precio_venta_total']) ?></td>
                    <td><input type="number" min="0" class="form-control" name="items[<?= (int) $producto['id'] ?>]" value="0"></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button class="btn btn-success">Enviar cotización</button>
</form>
<hr>
<h5>Historial</h5>
<table class="table table-sm">
<thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
<tbody>
<?php foreach ($data['cotizaciones'] as $cotizacion): ?>
<tr><td><?= (int) $cotizacion['id'] ?></td><td><?= htmlspecialchars($cotizacion['estado']) ?></td><td><?= htmlspecialchars($cotizacion['total']) ?></td><td><?= htmlspecialchars($cotizacion['created_at']) ?></td></tr>
<?php endforeach; ?>
</tbody>
</table>
