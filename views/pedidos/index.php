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
            <thead><tr><th>#</th><th>Cliente</th><th>Vendedor</th><th>Total</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
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
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" type="button">Acciones</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text">Pedido #<?= (int) $pedido['id'] ?></span></li>
                                <li>
                                    <form method="post" class="px-2 py-1">
                                        <input type="hidden" name="action" value="estado">
                                        <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                                        <label class="form-label small mb-1">Editar estado</label>
                                        <select name="estado" class="form-select form-select-sm tl-compact-input mb-2">
                                            <?php foreach (['pendiente','preparacion','enviado','entregado','cancelado'] as $estado): ?>
                                                <option value="<?= $estado ?>" <?= $estado === $pedido['estado'] ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-primary btn-sm w-100" type="submit">Guardar</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form method="post" onsubmit="return confirm('¿Eliminar pedido?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>"><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
