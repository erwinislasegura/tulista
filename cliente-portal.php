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
$pedidosMensuales = array_fill(1, 12, 0.0);
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
    $tsPedido = strtotime((string) ($pedido['fecha'] ?? ''));
    if ($tsPedido) {
        $pedidosMensuales[(int) date('n', $tsPedido)] += (float) ($pedido['total'] ?? 0);
    }
}

$conversion = count($cotizaciones) > 0 ? (count($pedidos) / count($cotizaciones)) * 100 : 0;
$maxMensual = max($ventasMensuales) > 0 ? max($ventasMensuales) : 1;
$maxMensualPedido = max($pedidosMensuales) > 0 ? max($pedidosMensuales) : 1;
$pedidosEntregados = count(array_filter($pedidos, static fn ($pedido) => in_array((string) ($pedido['estado'] ?? ''), ['entregado'], true)));
$porcentajePagado = count($pedidos) > 0 ? ((count($pedidos) - $clienteMetrics['pedidos_no_pagados']) / count($pedidos)) * 100 : 0;

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
    'dashboard' => [
        'label' => 'Dashboard',
        'file' => __DIR__ . '/views/cliente_portal/section-dashboard.php',
    ],
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
        'label' => 'Historial de pedidos',
        'file' => __DIR__ . '/views/cliente_portal/section-mis-pedidos.php',
    ],
];

$currentView = (string) ($_GET['view'] ?? 'dashboard');
if (!isset($sections[$currentView])) {
    $currentView = 'dashboard';
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

        .tl-cliente .page-content {
            padding-top: calc(var(--rsk-topbar-height, 70px) + 1rem);
            background: #f4f7fb;
        }

        .tl-cliente .main-nav {
            background: #0f1f35;
            border-right: 1px solid rgba(148, 163, 184, 0.16);
        }

        .tl-cliente .main-nav .menu-title {
            color: rgba(241, 245, 249, 0.72);
            font-size: 0.68rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-top: .45rem;
            margin-bottom: .35rem;
        }

        .tl-cliente .main-nav .nav-link {
            min-height: 42px;
            border-radius: 10px;
            margin: 0.12rem 0.45rem;
            padding-inline: .7rem;
            color: rgba(241, 245, 249, 0.9);
            transition: all .18s ease;
        }

        .tl-cliente .main-nav .nav-link .nav-icon {
            width: 1.2rem;
            color: rgba(148, 163, 184, 0.95);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .tl-cliente .main-nav .nav-link .nav-icon iconify-icon {
            font-size: 1.05rem;
            color: inherit;
        }

        .tl-cliente .main-nav .nav-link:hover {
            background: rgba(59, 130, 246, 0.2);
            color: #fff;
            transform: translateX(2px);
        }

        .tl-cliente .main-nav .nav-link.active {
            background: linear-gradient(90deg, rgba(37, 99, 235, 0.92), rgba(14, 116, 144, 0.9));
            color: #fff;
            box-shadow: 0 8px 18px rgba(2, 6, 23, 0.24);
        }

        .tl-cliente .main-nav .nav-link.active .nav-icon {
            color: #fff;
        }

        .tl-cliente .topbar iconify-icon,
        .tl-cliente .topbar .bx {
            color: #334155 !important;
            opacity: 1 !important;
        }

        .tl-cliente .topbar .tl-menu-toggle iconify-icon,
        .tl-cliente .topbar .tl-topbar-action iconify-icon {
            color: #0f172a !important;
        }

        .tl-cliente .table th {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            padding: 0.42rem 0.55rem;
            vertical-align: middle;
        }

        .tl-cliente .table td {
            padding: 0.38rem 0.55rem;
            vertical-align: middle;
            line-height: 1.2;
        }

        .tl-cliente .table.table-sm th,
        .tl-cliente .table.table-sm td {
            padding-top: 0.34rem;
            padding-bottom: 0.34rem;
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

        .md-shell { display: grid; gap: 0.85rem; }
        .md-panel { border: 1px solid #e3e8f2; border-radius: 14px; background: #fff; padding: 1rem; }
        .md-title { font-size: 1.05rem; font-weight: 700; color: #12233d; margin-bottom: 0.2rem; }
        .md-subtitle { color: #61748f; margin-bottom: 0; }
        .md-kpis { display: grid; gap: 0.75rem; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .md-kpi { border: 1px solid #e5eaf3; border-radius: 12px; padding: 0.8rem; background: #f8fafc; color: #fff; }
        .md-kpi--teal { background: linear-gradient(135deg, #1f97b3 0%, #16758d 100%); border-color: transparent; }
        .md-kpi--purple { background: linear-gradient(135deg, #6b46c1 0%, #4c1d95 100%); border-color: transparent; }
        .md-kpi--green { background: linear-gradient(135deg, #1fa968 0%, #15803d 100%); border-color: transparent; }
        .md-kpi--red { background: linear-gradient(135deg, #de3f54 0%, #be123c 100%); border-color: transparent; }
        .md-kpi-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: .2rem; }
        .md-kpi-value { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 0; }
        .md-kpi small { color: rgba(255, 255, 255, 0.85) !important; }
        .md-kpi--teal .md-kpi-label,
        .md-kpi--purple .md-kpi-label,
        .md-kpi--green .md-kpi-label,
        .md-kpi--red .md-kpi-label { color: rgba(255, 255, 255, 0.82); }
        .md-quick { border-radius: 10px; padding: .55rem .9rem; font-weight: 600; }
        .md-bars { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: .35rem; align-items: end; min-height: 135px; }
        .md-bar { background: #d7e5ff; border-radius: 8px 8px 4px 4px; position: relative; min-height: 8px; }
        .md-bar span { position: absolute; bottom: -1.1rem; left: 50%; transform: translateX(-50%); font-size: .62rem; color: #6b7b93; }
        .md-double-bars { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: .45rem; align-items: end; min-height: 150px; }
        .md-double-col { display: grid; grid-template-columns: 1fr 1fr; gap: .12rem; align-items: end; }
        .md-double-col i { display: block; border-radius: 4px 4px 2px 2px; min-height: 6px; }
        .md-double-col i:first-child { background: #60a5fa; }
        .md-double-col i:last-child { background: #34d399; }
        .md-legend { display: flex; gap: .8rem; font-size: .68rem; color: #64748b; }
        .md-legend span::before { content: ''; width: 10px; height: 10px; border-radius: 2px; display: inline-block; margin-right: .35rem; }
        .md-legend .l-cot::before { background: #60a5fa; }
        .md-legend .l-ped::before { background: #34d399; }
        .md-progress { height: 8px; border-radius: 999px; background: #edf2f7; overflow: hidden; }
        .md-progress > i { display: block; height: 100%; border-radius: inherit; }
        .md-grid-2 { display: grid; gap: .75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (max-width: 1199.98px) { .md-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 767.98px) { .md-kpis, .md-grid-2 { grid-template-columns: 1fr; } }
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
