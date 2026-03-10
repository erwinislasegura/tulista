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
