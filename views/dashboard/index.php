<?php $k = $data['kpis']; ?>
<div class="row g-3 mb-3">
    <?php
    $cards = [
        ['Ventas del día', $k['ventas_dia']],
        ['Ventas del mes', $k['ventas_mes']],
        ['Ganancia del mes', $k['ganancia_mes']],
        ['Comisiones del mes', $k['comisiones_mes']],
        ['Cotizaciones pendientes', $k['cotizaciones_pendientes'], false],
        ['Pedidos en proceso', $k['pedidos_proceso'], false],
        ['Stock bajo mínimo', $k['stock_bajo'], false],
        ['Clientes nuevos', $k['clientes_nuevos'], false],
    ];
    foreach ($cards as $card):
        $isMoney = $card[2] ?? true;
    ?>
    <div class="col-6 col-xl-3">
        <div class="card p-3">
            <div class="text-muted small"><?= htmlspecialchars($card[0]) ?></div>
            <div class="fs-4 fw-semibold"><?= $isMoney ? '$' . number_format((float) $card[1], 0, ',', '.') : (int) $card[1] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom">
        <div>
            <h5 class="mb-1">Cotizaciones sin revisión comercial</h5>
            <p class="text-muted mb-0 small">Solicitudes de clientes pendientes por aprobar/rechazar y validar stock.</p>
        </div>
        <a href="apps-cotizaciones.php" class="btn btn-sm btn-primary">Ir a cotizaciones</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>ID</th><th>Cliente</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Riesgo stock</th><th>Acción</th></tr></thead>
            <tbody>
            <?php foreach (($data['cotizaciones_sin_revision'] ?? []) as $cotizacion): ?>
                <?php $sinStock = (int) ($cotizacion['items_sin_stock'] ?? 0) > 0; ?>
                <tr>
                    <td>#<?= (int) $cotizacion['id'] ?></td>
                    <td><?= htmlspecialchars($cotizacion['cliente_nombre']) ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars($cotizacion['estado']) ?></td>
                    <td>$<?= number_format((float) $cotizacion['total'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                    <td>
                        <?php if ($sinStock): ?>
                            <span class="badge bg-warning-subtle text-warning">Posible quiebre (<?= (int) $cotizacion['items_sin_stock'] ?>)</span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success">Sin alertas</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-flex gap-2 flex-wrap">
                        <a href="apps-cotizaciones.php" class="btn btn-sm btn-outline-primary">Aprobar/Rechazar</a>
                        <?php if ($sinStock): ?>
                            <a href="apps-bodega.php?menu=revision" class="btn btn-sm btn-outline-warning">Ver reemplazos</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['cotizaciones_sin_revision'])): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">No hay cotizaciones pendientes de revisión.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <h5 class="tl-section-title">Top clientes</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($data['top_clientes'] as $cliente): ?>
                    <li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($cliente['nombre']) ?></span><strong>$<?= number_format((float) $cliente['total_monto'], 0, ',', '.') ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <h5 class="tl-section-title">Top productos</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($data['top_productos'] as $producto): ?>
                    <li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($producto['nombre']) ?></span><strong><?= (int) $producto['total_vendido'] ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <h5 class="tl-section-title">Actividad reciente</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($data['actividad'] as $log): ?>
                    <li class="list-group-item">
                        <div class="small text-muted"><?= htmlspecialchars($log['fecha']) ?></div>
                        <div><?= htmlspecialchars(($log['usuario_nombre'] ?? 'Sistema') . ' - ' . $log['descripcion']) ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
