<div class="card">
    <h5 class="tl-section-title">Cotizaciones recibidas</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Cliente</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Actualizar</th></tr></thead>
            <tbody>
            <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
                <tr>
                    <td><?= (int) $cotizacion['id'] ?></td>
                    <td><?= htmlspecialchars($cotizacion['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($cotizacion['estado']) ?></td>
                    <td><?= htmlspecialchars($cotizacion['total']) ?></td>
                    <td><?= htmlspecialchars($cotizacion['created_at']) ?></td>
                    <td>
                        <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="action" value="cambiar_estado">
                            <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                            <select name="estado" class="form-select form-select-sm">
                                <?php foreach (['pendiente','respondida','aprobada','rechazada'] as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $estado === $cotizacion['estado'] ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
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
