<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <h5 class="tl-section-title">Ventas por vendedor</h5>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Vendedor</th><th>Pedidos</th><th>Total</th></tr></thead><tbody>
            <?php foreach ($data['ventas_vendedor'] as $row): ?>
                <tr><td><?= htmlspecialchars($row['vendedor']) ?></td><td><?= (int) $row['pedidos'] ?></td><td>$<?= number_format((float) $row['total'], 0, ',', '.') ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <h5 class="tl-section-title">Comisiones por vendedor</h5>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Vendedor</th><th>Operaciones</th><th>Total comisión</th></tr></thead><tbody>
            <?php foreach ($data['comisiones_vendedor'] as $row): ?>
                <tr><td><?= htmlspecialchars($row['vendedor']) ?></td><td><?= (int) $row['operaciones'] ?></td><td>$<?= number_format((float) $row['total_comision'], 0, ',', '.') ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
</div>
