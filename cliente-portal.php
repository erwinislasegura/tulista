<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/CotizacionController.php';

$controller = new CotizacionController();
$data = $controller->handlePortalRequest();
$cotizaciones = $data['cotizaciones'] ?? [];
$pedidos = $data['pedidos'] ?? [];

$clienteMetrics = [
    'cotizaciones_pendientes' => 0,
    'pedidos_no_pagados' => 0,
    'pedidos_en_transito' => 0,
    'pedidos_activos' => 0,
    'monto_no_pagado' => 0.0,
];

foreach (($data['cotizaciones'] ?? []) as $cotizacion) {
    $estado = (string) ($cotizacion['estado'] ?? '');
    if (in_array($estado, ['borrador', 'enviada'], true)) {
        $clienteMetrics['cotizaciones_pendientes']++;
    }
}

foreach (($data['pedidos'] ?? []) as $pedido) {
    $estadoOperacion = strtolower((string) ($pedido['estado'] ?? ''));
    $estadoPago = strtolower((string) ($pedido['estado_pago'] ?? 'pendiente'));
    $totalPedido = (float) ($pedido['total'] ?? 0);

    if (!in_array($estadoOperacion, ['entregado', 'cancelado'], true)) {
        $clienteMetrics['pedidos_activos']++;
    }

    if (in_array($estadoOperacion, ['en_transito', 'en tránsito', 'despachado'], true)) {
        $clienteMetrics['pedidos_en_transito']++;
    }

    if (!in_array($estadoPago, ['pagado', 'paid'], true)) {
        $clienteMetrics['pedidos_no_pagados']++;
        $clienteMetrics['monto_no_pagado'] += $totalPedido;
    }
}

$portalLink = 'cliente-portal.php?token=' . urlencode($data['cliente']['token'] ?? '');
$totalCotizaciones = 0.0;
$totalPedidos = 0.0;
$cotizacionesAprobables = 0;
$pedidosActivos = 0;
$ventasMensuales = array_fill(1, 12, 0.0);
$meses = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];

foreach ($cotizaciones as $cotizacion) {
    $total = (float) ($cotizacion['total'] ?? 0);
    $totalCotizaciones += $total;

    if (in_array(($cotizacion['estado'] ?? ''), ['aprobada', 'enviada'], true)) {
        $cotizacionesAprobables++;
    }

    $ts = strtotime((string) ($cotizacion['fecha'] ?? ''));
    if ($ts) {
        $ventasMensuales[(int) date('n', $ts)] += $total;
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

$notificationItems = [];
if (($clienteMetrics['cotizaciones_pendientes'] ?? 0) > 0) {
    $notificationItems[] = [
        'label' => 'cotizaciones pendientes por revisar',
        'count' => (int) $clienteMetrics['cotizaciones_pendientes'],
        'href' => 'cliente-portal.php?view=cotizaciones&estado=sin_revision',
    ];
}
if (($clienteMetrics['pedidos_activos'] ?? 0) > 0) {
    $notificationItems[] = [
        'label' => 'pedidos activos en curso',
        'count' => (int) $clienteMetrics['pedidos_activos'],
        'href' => 'cliente-portal.php?view=seguimiento',
    ];
}
if (($clienteMetrics['pedidos_no_pagados'] ?? 0) > 0) {
    $notificationItems[] = [
        'label' => 'pedidos pendientes de pago',
        'count' => (int) $clienteMetrics['pedidos_no_pagados'],
        'href' => 'cliente-portal.php?view=mis-pedidos',
    ];
}

$formatCurrency = static function (float $value): string {
    return '$' . number_format($value, 0, ',', '.');
};

$sections = [
    'cotizar' => [
        'label' => 'Cotizar',
        'file' => __DIR__ . '/views/cliente_portal/section-cotizar.php',
    ],
    'cotizaciones' => [
        'label' => 'Cotizaciones registradas',
        'file' => __DIR__ . '/views/cliente_portal/section-cotizaciones.php',
    ],
    'seguimiento' => [
        'label' => 'Seguimiento pedido',
        'file' => __DIR__ . '/views/cliente_portal/section-seguimiento.php',
    ],
    'consultar' => [
        'label' => 'Consultar producto',
        'file' => __DIR__ . '/views/cliente_portal/section-consultar.php',
    ],
    'mis-pedidos' => [
        'label' => 'Mis pedidos',
        'file' => __DIR__ . '/views/cliente_portal/section-mis-pedidos.php',
    ],
];

$currentView = (string) ($_GET['view'] ?? 'cotizar');
if (!isset($sections[$currentView])) {
    $currentView = 'cotizar';
}
?>
<head>
    <?php $title = 'Portal cliente'; $portalApp = 'cliente'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?>
    <style>
        .tl-cliente,
        .tl-cliente .card,
        .tl-cliente .table,
        .tl-cliente .form-control,
        .tl-cliente .form-select,
        .tl-cliente .btn,
        .tl-cliente .badge,
        .tl-cliente .text-muted,
        .tl-cliente .form-label {
            font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
            font-size: 0.78rem;
        }

        .tl-cliente h4,
        .tl-cliente h5,
        .tl-cliente .h4,
        .tl-cliente .h5 {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .tl-cliente .table th {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .section-chip {
            background: #e6f4f7;
            color: #1f5662;
            border: 1px solid rgba(47, 166, 185, 0.18);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .8rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .cp-hero,
        .cp-indicators {
            border: 1px solid #d7e1ed;
            border-radius: 16px;
            background: #ffffff;
            padding: 1rem;
            margin-bottom: 0.85rem;
        }

        .cp-hero-title {
            font-size: 1.3rem;
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
            margin-bottom: 0.85rem;
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
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.1;
            margin: 0;
        }

        .cp-kpi-note { font-size: 0.78rem; opacity: 0.9; }
        .cp-progress-track { height: 10px; border-radius: 999px; background: #e9eef5; overflow: hidden; }
        .cp-progress-value { height: 100%; border-radius: 999px; }
        .cp-progress-value--primary { background: #2878ff; }
        .cp-progress-value--success { background: #1fa968; }
        .cp-mini-chart { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 0.35rem; align-items: end; min-height: 128px; padding-top: 0.6rem; }
        .cp-mini-bar { background: #8db6ff; border-radius: 6px 6px 4px 4px; min-height: 8px; position: relative; }
        .cp-mini-bar span { position: absolute; bottom: -1.25rem; left: 50%; transform: translateX(-50%); font-size: 0.62rem; color: #677892; font-weight: 600; }

        @media (max-width: 1199.98px) {
            .cp-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 767.98px) {
            .cp-kpis { grid-template-columns: 1fr; gap: 0.65rem; }
            .cp-kpi { min-height: 98px; padding: 0.75rem; }
            .cp-kpi-value { font-size: 1.35rem; }
            .cp-mini-chart { min-height: 102px; gap: 0.25rem; }
        }
    </style>
</head>
<body>
<div class="wrapper tl-cliente">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <?php foreach ($data['flash'] as $alert): ?>
                <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
            <?php endforeach; ?>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($sections[$currentView]['label']) ?></h4>
                    <p class="text-muted mb-0">Cada opción del menú se muestra en su propia vista individual.</p>
                </div>
                <div class="section-chip">
                    <iconify-icon icon="solar:widget-5-broken"></iconify-icon>
                    Vista independiente
                </div>
            </div>

            <?php if (!empty($notificationItems)): ?>
                <div class="alert alert-warning d-flex flex-wrap gap-2 align-items-center mb-3">
                    <strong class="me-1">Pendientes:</strong>
                    <?php foreach ($notificationItems as $item): ?>
                        <a class="badge text-bg-light text-decoration-none" href="<?= htmlspecialchars($item['href']) ?>">
                            <?= (int) $item['count'] ?> <?= htmlspecialchars($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

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

            <section class="cp-indicators mb-3">
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

            <?php include $sections[$currentView]['file']; ?>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
<script>
(() => {
    const searchInput = document.getElementById('buscar-producto');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    const productCount = document.getElementById('resumen-productos');
    const totalText = document.getElementById('resumen-total');
    const stockToggle = document.getElementById('mostrar-sin-stock');
    const form = document.getElementById('cotizacion-form');
    const sinStockModalEl = document.getElementById('sin-stock-modal');
    const sinStockModalBody = document.getElementById('sin-stock-modal-body');
    const sinStockConfirmBtn = document.getElementById('confirmar-cotizacion-sin-stock');
    let allowSubmitWithNoStock = false;

    if (rows.length && productCount && totalText) {
        const formatCurrency = (value) => new Intl.NumberFormat('es-CL').format(value);

        const applyFilters = () => {
            const term = (searchInput?.value || '').trim().toLowerCase();
            const showWithoutStock = !!stockToggle?.checked;

            rows.forEach((row) => {
                const nameMatches = row.dataset.name.includes(term);
                const stock = parseInt(row.dataset.stock || '0', 10);
                const stockMatches = showWithoutStock || stock > 0;
                const qty = parseInt(row.querySelector('[data-cantidad]')?.value || '0', 10);
                const hasSelection = qty > 0;
                row.classList.toggle('d-none', !(nameMatches && stockMatches) && !hasSelection);
            });
        };

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

        const showNoStockWarning = (outOfStockItems) => {
            const cantidadProductos = outOfStockItems.length;
            const mensaje = `Estás cotizando ${cantidadProductos} producto${cantidadProductos === 1 ? '' : 's'} sin existencia: <strong>${outOfStockItems.join(', ')}</strong>. La solicitud quedará sujeta a revisión de la empresa vendedora.`;
            if (sinStockModalBody) {
                sinStockModalBody.innerHTML = mensaje;
            }

            if (window.bootstrap?.Modal && sinStockModalEl) {
                const modal = window.bootstrap.Modal.getOrCreateInstance(sinStockModalEl);
                modal.show();
                return;
            }

            const confirmed = window.confirm(
                `Estás cotizando ${cantidadProductos} producto(s) sin existencia (${outOfStockItems.join(', ')}). ` +
                'La solicitud quedará sujeta a revisión de la empresa vendedora. ¿Deseas continuar?'
            );
            if (confirmed) {
                allowSubmitWithNoStock = true;
                form?.requestSubmit();
            }
        };

        searchInput?.addEventListener('input', applyFilters);
        stockToggle?.addEventListener('change', applyFilters);

        rows.forEach((row) => {
            const input = row.querySelector('[data-cantidad]');
            row.querySelector('[data-plus]')?.addEventListener('click', () => {
                input.value = Math.max(0, parseInt(input.value || '0', 10)) + 1;
                updateSummary();
                applyFilters();
            });
            row.querySelector('[data-minus]')?.addEventListener('click', () => {
                input.value = Math.max(0, parseInt(input.value || '0', 10) - 1);
                updateSummary();
                applyFilters();
            });
            input?.addEventListener('input', () => {
                updateSummary();
                applyFilters();
            });
        });

        form?.addEventListener('submit', (event) => {
            if (allowSubmitWithNoStock) {
                allowSubmitWithNoStock = false;
                return;
            }

            const outOfStockItems = [];
            rows.forEach((row) => {
                const stock = parseInt(row.dataset.stock || '0', 10);
                const qty = parseInt(row.querySelector('[data-cantidad]')?.value || '0', 10);
                if (stock <= 0 && qty > 0) {
                    const name = row.querySelector('.tl-product-name')?.textContent?.trim() || 'producto';
                    outOfStockItems.push(name);
                }
            });

            if (outOfStockItems.length > 0) {
                event.preventDefault();
                showNoStockWarning(outOfStockItems);
            }
        });

        sinStockConfirmBtn?.addEventListener('click', () => {
            allowSubmitWithNoStock = true;
            if (window.bootstrap?.Modal && sinStockModalEl) {
                window.bootstrap.Modal.getOrCreateInstance(sinStockModalEl).hide();
            }
            form?.requestSubmit();
        });

        applyFilters();
        updateSummary();
    }
})();
</script>
</body>
</html>
