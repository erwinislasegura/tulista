<?php foreach ($data['flash'] as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="tl-section-title mb-0">Pedidos en operación</h5>
        <span class="badge bg-light text-dark">Total: <?= count($data['pedidos']) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>#</th><th>Cliente</th><th>Vendedor</th><th>Total</th><th>Fecha</th><th>Estado</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($data['pedidos'] as $pedido): ?>
                <tr>
                    <td><?= (int) $pedido['id'] ?></td>
                    <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($pedido['vendedor'] ?? 'Sin asignar') ?></td>
                    <td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                    <td><span class="badge bg-light text-dark text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></span></td>
                    <td>
                        <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="action" value="estado">
                            <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                            <select name="estado" class="form-select form-select-sm tl-compact-input">
                                <?php foreach (['pendiente','preparacion','enviado','entregado','cancelado'] as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $estado === $pedido['estado'] ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-primary" type="submit">Guardar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
