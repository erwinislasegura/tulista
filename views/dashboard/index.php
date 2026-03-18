<?php
$k = $data['kpis'];
$ventasMensuales = array_fill(1, 12, 0.0);
$meses = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];

foreach (($data['ventas_mensuales'] ?? []) as $venta) {
    $periodo = (string) ($venta['periodo'] ?? '');
    $timestamp = strtotime($periodo . '-01');
    if ($timestamp) {
        $ventasMensuales[(int) date('n', $timestamp)] = (float) ($venta['total'] ?? 0);
    }
}

$maxMensual = max($ventasMensuales) > 0 ? max($ventasMensuales) : 1;
$pedidosObjetivo = max(1, (int) ($k['pedidos_proceso'] ?? 0) + (int) ($k['cotizaciones_pendientes'] ?? 0));
$progresoPedidos = min(100, ((int) ($k['pedidos_proceso'] ?? 0) / $pedidosObjetivo) * 100);
$progresoClientes = min(100, ((int) ($k['clientes_nuevos'] ?? 0) / 20) * 100);
?>
<style>
    .ad-shell { display: grid; gap: .85rem; }
    .ad-panel { border: 1px solid #e3e8f2; border-radius: 14px; background: #fff; padding: 1rem; }
    .ad-title { font-size: 1.08rem; font-weight: 700; color: #12233d; margin-bottom: .2rem; }
    .ad-subtitle { color: #61748f; margin-bottom: 0; }
    .ad-kpis { display: grid; gap: .75rem; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .ad-kpi { border-radius: 12px; padding: .8rem; border: 1px solid transparent; color: #fff; min-height: 96px; }
    .ad-kpi--teal { background: linear-gradient(135deg, #1f97b3 0%, #16758d 100%); }
    .ad-kpi--purple { background: linear-gradient(135deg, #6b46c1 0%, #4c1d95 100%); }
    .ad-kpi--green { background: linear-gradient(135deg, #1fa968 0%, #15803d 100%); }
    .ad-kpi--red { background: linear-gradient(135deg, #de3f54 0%, #be123c 100%); }
    .ad-kpi-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: rgba(255, 255, 255, 0.82); margin-bottom: .2rem; }
    .ad-kpi-value { font-size: 1.25rem; font-weight: 700; margin-bottom: 0; }
    .ad-kpi small { color: rgba(255, 255, 255, 0.85) !important; }
    .ad-grid-2 { display: grid; gap: .75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ad-progress { height: 8px; border-radius: 999px; background: #edf2f7; overflow: hidden; }
    .ad-progress i { display: block; height: 100%; border-radius: inherit; }
    .ad-bars { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: .35rem; align-items: end; min-height: 130px; }
    .ad-bar { position: relative; background: #d7e5ff; border-radius: 8px 8px 4px 4px; min-height: 8px; }
    .ad-bar span { position: absolute; bottom: -1.05rem; left: 50%; transform: translateX(-50%); font-size: .62rem; color: #6b7b93; }
    .ad-table thead th { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: #5f718d; }
    .ad-section-title { font-size: .92rem; font-weight: 700; margin-bottom: .75rem; color: #1b2b47; }
    .ad-list .list-group-item { padding: .65rem .75rem; }
    @media (max-width: 1199.98px) { .ad-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) { .ad-kpis, .ad-grid-2 { grid-template-columns: 1fr; } }
</style>

<section class="ad-shell">
    <article class="ad-panel d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="ad-title">Dashboard administración</h5>
            <p class="ad-subtitle">Vista ejecutiva con la misma línea visual del portal cliente para unificar experiencia.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="apps-cotizaciones.php" class="btn btn-primary btn-sm">Gestionar cotizaciones</a>
            <a href="apps-pedidos.php" class="btn btn-outline-secondary btn-sm">Ver pedidos</a>
        </div>
    </article>

    <section class="ad-kpis">
        <article class="ad-kpi ad-kpi--teal">
            <p class="ad-kpi-label">Ventas del día</p>
            <p class="ad-kpi-value">$<?= number_format((float) ($k['ventas_dia'] ?? 0), 0, ',', '.') ?></p>
            <small>Flujo diario actual</small>
        </article>
        <article class="ad-kpi ad-kpi--purple">
            <p class="ad-kpi-label">Ventas del mes</p>
            <p class="ad-kpi-value">$<?= number_format((float) ($k['ventas_mes'] ?? 0), 0, ',', '.') ?></p>
            <small>Acumulado mensual</small>
        </article>
        <article class="ad-kpi ad-kpi--green">
            <p class="ad-kpi-label">Ganancia del mes</p>
            <p class="ad-kpi-value">$<?= number_format((float) ($k['ganancia_mes'] ?? 0), 0, ',', '.') ?></p>
            <small>Margen consolidado</small>
        </article>
        <article class="ad-kpi ad-kpi--red">
            <p class="ad-kpi-label">Comisiones del mes</p>
            <p class="ad-kpi-value">$<?= number_format((float) ($k['comisiones_mes'] ?? 0), 0, ',', '.') ?></p>
            <small>Pagos variables</small>
        </article>
    </section>

    <section class="ad-grid-2">
        <article class="ad-panel">
            <h6 class="ad-section-title">Indicadores operativos</h6>
            <div class="d-grid gap-3">
                <div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Pedidos en proceso</span>
                        <strong><?= (int) ($k['pedidos_proceso'] ?? 0) ?> activos</strong>
                    </div>
                    <div class="ad-progress"><i style="width: <?= $progresoPedidos ?>%; background:#2563eb;"></i></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Clientes nuevos (30 días)</span>
                        <strong><?= (int) ($k['clientes_nuevos'] ?? 0) ?></strong>
                    </div>
                    <div class="ad-progress"><i style="width: <?= $progresoClientes ?>%; background:#16a34a;"></i></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Stock bajo mínimo</span>
                        <strong><?= (int) ($k['stock_bajo'] ?? 0) ?> SKU</strong>
                    </div>
                    <div class="ad-progress"><i style="width: <?= min(100, ((int) ($k['stock_bajo'] ?? 0) / 50) * 100) ?>%; background:#f59e0b;"></i></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Cotizaciones pendientes</span>
                        <strong><?= (int) ($k['cotizaciones_pendientes'] ?? 0) ?></strong>
                    </div>
                    <div class="ad-progress"><i style="width: <?= min(100, ((int) ($k['cotizaciones_pendientes'] ?? 0) / 40) * 100) ?>%; background:#ef4444;"></i></div>
                </div>
            </div>
        </article>

        <article class="ad-panel">
            <h6 class="ad-section-title">Ventas por mes</h6>
            <div class="ad-bars" aria-label="Gráfico mensual de ventas">
                <?php foreach ($ventasMensuales as $mesNumero => $monto): ?>
                    <?php $altura = max(8, ($monto / $maxMensual) * 100); ?>
                    <div class="ad-bar" style="height: <?= $altura ?>%" title="<?= $meses[$mesNumero] ?>: $<?= number_format($monto, 0, ',', '.') ?>">
                        <span><?= $meses[$mesNumero] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <article class="ad-panel p-0 overflow-hidden">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom">
            <div>
                <h5 class="mb-1">Cotizaciones sin revisión comercial</h5>
                <p class="text-muted mb-0">Solicitudes de clientes pendientes por aprobar/rechazar y validar stock.</p>
            </div>
            <a href="apps-cotizaciones.php" class="btn btn-sm btn-primary">Ir a cotizaciones</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 ad-table">
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
    </article>

    <section class="ad-grid-2">
        <div class="ad-panel ad-list">
            <h6 class="ad-section-title">Top clientes</h6>
            <ul class="list-group list-group-flush">
                <?php foreach (($data['top_clientes'] ?? []) as $cliente): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($cliente['nombre']) ?></span>
                        <strong>$<?= number_format((float) ($cliente['total_monto'] ?? 0), 0, ',', '.') ?></strong>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($data['top_clientes'])): ?>
                    <li class="list-group-item text-muted">Sin datos de clientes aún.</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="ad-panel ad-list">
            <h6 class="ad-section-title">Top productos</h6>
            <ul class="list-group list-group-flush">
                <?php foreach (($data['top_productos'] ?? []) as $producto): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($producto['nombre']) ?></span>
                        <strong><?= (int) ($producto['total_vendido'] ?? 0) ?></strong>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($data['top_productos'])): ?>
                    <li class="list-group-item text-muted">Sin datos de productos aún.</li>
                <?php endif; ?>
            </ul>
        </div>
    </section>

    <div class="ad-panel ad-list">
        <h6 class="ad-section-title">Actividad reciente</h6>
        <ul class="list-group list-group-flush">
            <?php foreach (($data['actividad'] ?? []) as $log): ?>
                <li class="list-group-item">
                    <div class="small text-muted"><?= htmlspecialchars($log['fecha']) ?></div>
                    <div><?= htmlspecialchars(($log['usuario_nombre'] ?? 'Sistema') . ' - ' . $log['descripcion']) ?></div>
                </li>
            <?php endforeach; ?>
            <?php if (empty($data['actividad'])): ?>
                <li class="list-group-item text-muted">No hay actividad registrada recientemente.</li>
            <?php endif; ?>
        </ul>
    </div>
</section>
