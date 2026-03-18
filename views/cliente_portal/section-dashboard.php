<section class="md-shell">
    <article class="md-panel d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="md-title">Dashboard cliente</h5>
            <p class="md-subtitle">Resumen ejecutivo de cotizaciones, pedidos y estado de operación.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-primary md-quick" href="cliente-portal.php?view=cotizar">+ Nueva cotización</a>
            <a class="btn btn-outline-secondary md-quick" href="<?= htmlspecialchars($portalLink) ?>" target="_blank" rel="noopener">Abrir link compartible</a>
        </div>
    </article>

    <section class="md-kpis">
        <article class="md-kpi">
            <p class="md-kpi-label">Cotizaciones acumuladas</p>
            <p class="md-kpi-value">$<?= number_format($totalCotizaciones, 0, ',', '.') ?></p>
            <small class="text-muted"><?= count($cotizaciones) ?> registradas</small>
        </article>
        <article class="md-kpi">
            <p class="md-kpi-label">Pedidos acumulados</p>
            <p class="md-kpi-value">$<?= number_format($totalPedidos, 0, ',', '.') ?></p>
            <small class="text-muted"><?= count($pedidos) ?> pedidos</small>
        </article>
        <article class="md-kpi">
            <p class="md-kpi-label">Cotizaciones aprobables</p>
            <p class="md-kpi-value"><?= (int) $cotizacionesAprobables ?></p>
            <small class="text-muted">Listas para convertirse en pedido</small>
        </article>
        <article class="md-kpi">
            <p class="md-kpi-label">Pedidos activos</p>
            <p class="md-kpi-value"><?= (int) $pedidosActivos ?></p>
            <small class="text-muted"><?= (int) $pedidosEntregados ?> entregados</small>
        </article>
    </section>

    <section class="md-grid-2">
        <article class="md-panel">
            <h6 class="mb-2">Indicadores de gestión</h6>
            <div class="d-grid gap-3">
                <div>
                    <div class="d-flex justify-content-between mb-1 small"><span>Conversión cotización → pedido</span><strong><?= number_format($conversion, 1, ',', '.') ?>%</strong></div>
                    <div class="md-progress"><i style="width: <?= min(100, $conversion) ?>%; background:#2563eb;"></i></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1 small"><span>Pedidos pagados</span><strong><?= number_format($porcentajePagado, 1, ',', '.') ?>%</strong></div>
                    <div class="md-progress"><i style="width: <?= min(100, $porcentajePagado) ?>%; background:#16a34a;"></i></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1 small"><span>Pedidos en tránsito</span><strong><?= count($pedidos) > 0 ? number_format(($clienteMetrics['pedidos_en_transito'] / count($pedidos)) * 100, 1, ',', '.') : '0,0' ?>%</strong></div>
                    <div class="md-progress"><i style="width: <?= count($pedidos) > 0 ? min(100, ($clienteMetrics['pedidos_en_transito'] / count($pedidos)) * 100) : 0 ?>%; background:#f59e0b;"></i></div>
                </div>
            </div>
        </article>

        <article class="md-panel">
            <h6 class="mb-2">Comportamiento mensual cotizaciones</h6>
            <div class="md-bars" aria-label="Gráfico mensual de cotizaciones">
                <?php foreach ($ventasMensuales as $mesNumero => $monto): ?>
                    <?php $altura = max(8, ($monto / $maxMensual) * 100); ?>
                    <div class="md-bar" style="height: <?= $altura ?>%" title="<?= $meses[$mesNumero] ?>: $<?= number_format($monto, 0, ',', '.') ?>">
                        <span><?= $meses[$mesNumero] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</section>
