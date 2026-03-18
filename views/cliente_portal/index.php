<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<?php
$portalLink = 'cotizar.php?token=' . urlencode($data['cliente']['token'] ?? '');
$cotizaciones = $data['cotizaciones'] ?? [];
$pedidos = $data['pedidos'] ?? [];
$productos = $data['productos'] ?? [];

$totalCotizaciones = 0.0;
$totalPedidos = 0.0;
$cotizacionesAprobables = 0;
$pedidosActivos = 0;
$ventasMensuales = array_fill(1, 12, 0.0);

foreach ($cotizaciones as $cotizacion) {
    $total = (float) ($cotizacion['total'] ?? 0);
    $totalCotizaciones += $total;

    if (in_array(($cotizacion['estado'] ?? ''), ['aprobada', 'enviada'], true)) {
        $cotizacionesAprobables++;
    }

    $fecha = $cotizacion['fecha'] ?? '';
    $ts = strtotime($fecha);
    if ($ts) {
        $mes = (int) date('n', $ts);
        $ventasMensuales[$mes] += $total;
    }
}

foreach ($pedidos as $pedido) {
    $totalPedidos += (float) ($pedido['total'] ?? 0);
    if (!in_array(($pedido['estado'] ?? ''), ['entregado', 'cancelado'], true)) {
        $pedidosActivos++;
    }
}

$conversion = count($cotizaciones) > 0 ? (count($pedidos) / count($cotizaciones)) * 100 : 0;
$maxMensual = max($ventasMensuales) > 0 ? max($ventasMensuales) : 1;
$meses = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
?>

<style>
    .cp-shell {
        display: grid;
        gap: 1rem;
    }

    .cp-hero {
        border: 1px solid #d7e1ed;
        border-radius: 16px;
        background: #ffffff;
        padding: 1rem;
    }

    .cp-hero-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e2d41;
        margin-bottom: 0.25rem;
    }

    .cp-hero-subtitle {
        color: #66758c;
        margin-bottom: 0;
    }

    .cp-link-box {
        border: 1px solid #d9e3ef;
        border-radius: 12px;
        padding: 0.85rem;
        background: #f8fbff;
    }

    .cp-kpis {
        display: grid;
        gap: 0.85rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .cp-kpi {
        color: #fff;
        border-radius: 14px;
        padding: 0.9rem;
        min-height: 118px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 0;
    }

    .cp-kpi--teal { background: #1f97b3; }
    .cp-kpi--purple { background: #6b46c1; }
    .cp-kpi--green { background: #1fa968; }
    .cp-kpi--red { background: #de3f54; }

    .cp-kpi-label {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.95;
        margin-bottom: 0.35rem;
    }

    .cp-kpi-value {
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        margin: 0;
    }

    .cp-kpi-note {
        font-size: 0.78rem;
        opacity: 0.9;
    }

    .cp-indicators {
        border: 1px solid #d7e1ed;
        border-radius: 16px;
        background: #fff;
        padding: 1rem;
    }

    .cp-progress-track {
        height: 10px;
        border-radius: 999px;
        background: #e9eef5;
        overflow: hidden;
    }

    .cp-progress-value {
        height: 100%;
        border-radius: 999px;
    }

    .cp-progress-value--primary { background: #2878ff; }
    .cp-progress-value--success { background: #1fa968; }

    .cp-mini-chart {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 0.35rem;
        align-items: end;
        min-height: 128px;
        padding-top: 0.6rem;
    }

    .cp-mini-bar {
        background: #8db6ff;
        border-radius: 6px 6px 4px 4px;
        min-height: 8px;
        position: relative;
    }

    .cp-mini-bar span {
        position: absolute;
        bottom: -1.25rem;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.62rem;
        color: #677892;
        font-weight: 600;
    }

    .cp-products-table td {
        vertical-align: middle;
    }

    .cp-sticky-actions {
        background: #ffffff;
        border: 1px solid #d9e3ef;
        border-radius: 12px;
        padding: 0.65rem 0.8rem;
    }

    @media (max-width: 1199.98px) {
        .cp-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .cp-shell {
            gap: 0.8rem;
        }

        .cp-hero,
        .cp-indicators,
        .cp-kpi,
        .card {
            border-radius: 12px;
        }

        .cp-hero-title {
            font-size: 1.1rem;
        }

        .cp-kpis {
            grid-template-columns: 1fr;
            gap: 0.65rem;
        }

        .cp-kpi {
            min-height: 98px;
            padding: 0.75rem;
        }

        .cp-kpi-value {
            font-size: 1.38rem;
        }

        .cp-mobile-table thead {
            display: none;
        }

        .cp-mobile-table,
        .cp-mobile-table tbody,
        .cp-mobile-table tr,
        .cp-mobile-table td {
            display: block;
            width: 100%;
        }

        .cp-mobile-table tr {
            border: 1px solid #dbe5f0;
            border-radius: 10px;
            margin-bottom: 0.65rem;
            padding: 0.35rem 0.6rem;
            background: #fff;
        }

        .cp-mobile-table td {
            border: 0;
            padding: 0.38rem 0;
            font-size: 0.82rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.6rem;
        }

        .cp-mobile-table td::before {
            content: attr(data-label);
            color: #66758c;
            font-size: 0.69rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }

        .cp-mobile-table td .input-group {
            max-width: 190px;
            margin-left: auto;
        }

        .cp-sticky-actions {
            position: sticky;
            bottom: 0.45rem;
            z-index: 6;
            box-shadow: 0 8px 24px rgba(11, 23, 39, 0.12);
        }

        .cp-mini-chart {
            min-height: 102px;
            gap: 0.25rem;
        }
    }
</style>

<div class="cp-shell">
    <section class="cp-hero">
        <div class="row g-3 align-items-center">
            <div class="col-lg-6">
                <h5 class="cp-hero-title">Dashboard de cliente</h5>
                <p class="cp-hero-subtitle">Flujo moderno para cotizar, aprobar y seguir pedidos desde cualquier celular.</p>
            </div>
            <div class="col-lg-6">
                <div class="cp-link-box">
                    <label class="form-label">Link para compartir (cotizar y seguimiento)</label>
                    <div class="input-group">
                        <input class="form-control" readonly value="<?= htmlspecialchars($portalLink) ?>">
                        <a href="<?= htmlspecialchars($portalLink) ?>" target="_blank" class="btn btn-primary">Abrir</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cp-kpis">
        <article class="cp-kpi cp-kpi--teal">
            <div>
                <p class="cp-kpi-label">Cotizaciones acumuladas</p>
                <p class="cp-kpi-value">$<?= number_format($totalCotizaciones, 0, ',', '.') ?></p>
            </div>
            <span class="cp-kpi-note"><?= count($cotizaciones) ?> cotizaciones registradas</span>
        </article>
        <article class="cp-kpi cp-kpi--purple">
            <div>
                <p class="cp-kpi-label">Pedidos acumulados</p>
                <p class="cp-kpi-value">$<?= number_format($totalPedidos, 0, ',', '.') ?></p>
            </div>
            <span class="cp-kpi-note"><?= count($pedidos) ?> pedidos totales</span>
        </article>
        <article class="cp-kpi cp-kpi--green">
            <div>
                <p class="cp-kpi-label">Aprobables ahora</p>
                <p class="cp-kpi-value"><?= $cotizacionesAprobables ?></p>
            </div>
            <span class="cp-kpi-note">Cotizaciones listas para pedido</span>
        </article>
        <article class="cp-kpi cp-kpi--red">
            <div>
                <p class="cp-kpi-label">Pedidos activos</p>
                <p class="cp-kpi-value"><?= $pedidosActivos ?></p>
            </div>
            <span class="cp-kpi-note">Pendientes de cierre o entrega</span>
        </article>
    </section>

    <section class="cp-indicators">
        <div class="row g-4">
            <div class="col-lg-5">
                <h5 class="tl-section-title mb-2">Indicadores clave</h5>
                <div class="d-grid gap-3">
                    <div>
                        <div class="d-flex justify-content-between small mb-1"><span>Conversión cotización → pedido</span><strong><?= number_format($conversion, 1, ',', '.') ?>%</strong></div>
                        <div class="cp-progress-track"><div class="cp-progress-value cp-progress-value--primary" style="width: <?= min(100, $conversion) ?>%"></div></div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between small mb-1"><span>Pedidos en estado activo</span><strong><?= count($pedidos) > 0 ? number_format(($pedidosActivos / max(1, count($pedidos))) * 100, 1, ',', '.') : '0,0' ?>%</strong></div>
                        <div class="cp-progress-track"><div class="cp-progress-value cp-progress-value--success" style="width: <?= count($pedidos) > 0 ? min(100, ($pedidosActivos / count($pedidos)) * 100) : 0 ?>%"></div></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <h5 class="tl-section-title mb-2">Comportamiento mensual de cotizaciones</h5>
                <div class="cp-mini-chart" aria-label="Gráfico mensual de cotizaciones">
                    <?php foreach ($ventasMensuales as $mesNumero => $monto): ?>
                        <?php $altura = max(8, ($monto / $maxMensual) * 100); ?>
                        <div class="cp-mini-bar" style="height: <?= $altura ?>%" title="<?= $meses[$mesNumero] ?>: $<?= number_format($monto, 0, ',', '.') ?>">
                            <span><?= $meses[$mesNumero] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="card mb-0">
        <h5 class="tl-section-title">Nueva cotización</h5>
        <form method="post" class="tl-minimal-form" id="cotizacion-form">
            <input type="hidden" name="action" value="crear_cotizacion">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'cliente-portal.php') ?>">

            <div class="row g-3 mb-3">
                <div class="col-lg-7">
                    <label for="buscar-producto" class="form-label">Buscar productos</label>
                    <input type="search" id="buscar-producto" class="form-control" placeholder="Escribe nombre del producto...">
                </div>
                <div class="col-lg-5 d-flex align-items-end">
                    <div class="alert alert-light border w-100 mb-0 py-2">
                        <strong id="resumen-productos">0 productos</strong>
                        <span class="text-muted"> seleccionados</span>
                        <span class="d-block small mt-1">Total estimado: <strong id="resumen-total">$0</strong></span>
                    </div>
                </div>
            </div>

            <div class="table-responsive border rounded">
                <table class="table align-middle mb-0 cp-mobile-table cp-products-table" id="tabla-productos">
                    <thead class="table-light"><tr><th>Producto</th><th>Precio</th><th style="min-width: 210px;">Cantidad</th></tr></thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <?php $precio = (float) $producto['precio_venta_total']; ?>
                            <tr data-product-row data-name="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                                <td data-label="Producto"><strong><?= htmlspecialchars($producto['nombre']) ?></strong></td>
                                <td data-label="Precio" data-precio="<?= $precio ?>">$<?= number_format($precio, 0, ',', '.') ?></td>
                                <td data-label="Cantidad">
                                    <div class="input-group input-group-sm">
                                        <button class="btn btn-outline-secondary" type="button" data-minus>-</button>
                                        <input
                                            type="number"
                                            min="0"
                                            step="1"
                                            class="form-control text-center"
                                            data-cantidad
                                            name="items[<?= (int) $producto['id'] ?>]"
                                            value="0"
                                        >
                                        <button class="btn btn-outline-secondary" type="button" data-plus>+</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 gap-2 flex-wrap cp-sticky-actions">
                <small class="text-muted">Tip: usa el buscador y +/- para armar tu cotización más rápido.</small>
                <button class="btn btn-primary" type="submit">Enviar cotización</button>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-lg-7" id="aprobar-cotizacion">
            <div class="card h-100">
                <h5 class="tl-section-title">Aprobar cotización (transforma en pedido)</h5>
                <div class="table-responsive">
                    <table class="table align-middle cp-mobile-table">
                        <thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Acción</th></tr></thead>
                        <tbody>
                        <?php foreach ($cotizaciones as $cotizacion): ?>
                        <tr>
                            <td data-label="ID">#<?= (int) $cotizacion['id'] ?></td>
                            <td data-label="Estado" class="text-capitalize"><?= htmlspecialchars($cotizacion['estado']) ?></td>
                            <td data-label="Total">$<?= number_format((float) $cotizacion['total'], 0, ',', '.') ?></td>
                            <td data-label="Fecha"><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                            <td data-label="Acción">
                                <?php if (in_array($cotizacion['estado'], ['aprobada', 'enviada'], true)): ?>
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="action" value="crear_pedido">
                                        <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'cliente-portal.php') ?>">
                                        <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                                        <button class="btn btn-sm btn-success" type="submit">Generar pedido</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">Esperando aprobación</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5" id="seguimiento-pedido">
            <div class="card h-100">
                <h5 class="tl-section-title">Seguimiento de pedido</h5>
                <div class="table-responsive">
                    <table class="table align-middle cp-mobile-table">
                        <thead><tr><th>ID</th><th>Cotización</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
                        <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td data-label="ID">#<?= (int) $pedido['id'] ?></td>
                                <td data-label="Cotización"><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                                <td data-label="Estado" class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td>
                                <td data-label="Total">$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                                <td data-label="Fecha"><?= htmlspecialchars($pedido['fecha']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('buscar-producto');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    const productCount = document.getElementById('resumen-productos');
    const totalText = document.getElementById('resumen-total');

    const formatCurrency = (value) => new Intl.NumberFormat('es-CL').format(value);

    const updateSummary = () => {
        let selected = 0;
        let total = 0;

        rows.forEach((row) => {
            const qtyInput = row.querySelector('[data-cantidad]');
            const qty = Math.max(0, parseInt(qtyInput.value || '0', 10));
            const price = parseFloat(row.querySelector('[data-precio]').dataset.precio || '0');
            if (qty > 0) {
                selected += 1;
                total += qty * price;
            }
            qtyInput.value = qty;
        });

        productCount.textContent = `${selected} producto${selected === 1 ? '' : 's'}`;
        totalText.textContent = `$${formatCurrency(total)}`;
    };

    searchInput?.addEventListener('input', () => {
        const term = searchInput.value.trim().toLowerCase();
        rows.forEach((row) => {
            const matches = row.dataset.name.includes(term);
            row.classList.toggle('d-none', !matches);
        });
    });

    rows.forEach((row) => {
        const input = row.querySelector('[data-cantidad]');
        row.querySelector('[data-plus]')?.addEventListener('click', () => {
            input.value = Math.max(0, parseInt(input.value || '0', 10)) + 1;
            updateSummary();
        });
        row.querySelector('[data-minus]')?.addEventListener('click', () => {
            input.value = Math.max(0, parseInt(input.value || '0', 10) - 1);
            updateSummary();
        });
        input.addEventListener('input', updateSummary);
    });

    updateSummary();
})();
</script>
