<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/CotizacionController.php';

$controller = new CotizacionController();
$data = $controller->handlePortalRequest();

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

        .tl-portal-dashboard {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e4e9f2;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .tl-portal-kpi {
            border-radius: 12px;
            border: 1px solid #e5ebf3;
            background: #ffffff;
            padding: 0.85rem;
            height: 100%;
        }

        .tl-portal-kpi__label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .tl-portal-kpi__value {
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.15;
            color: #0f172a;
        }

        .tl-portal-kpi--danger .tl-portal-kpi__value { color: #d6455d; }
        .tl-portal-kpi--accent .tl-portal-kpi__value { color: #2fa6b9; }
        .tl-portal-kpi--success .tl-portal-kpi__value { color: #37b24d; }
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

            <section class="tl-portal-dashboard">
                <div class="row g-2">
                    <div class="col-sm-6 col-xl-3">
                        <article class="tl-portal-kpi tl-portal-kpi--accent">
                            <p class="tl-portal-kpi__label">Cotizaciones pendientes</p>
                            <p class="tl-portal-kpi__value mb-0"><?= (int) $clienteMetrics['cotizaciones_pendientes'] ?></p>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="tl-portal-kpi tl-portal-kpi--danger">
                            <p class="tl-portal-kpi__label">Pedidos no pagados</p>
                            <p class="tl-portal-kpi__value mb-0"><?= (int) $clienteMetrics['pedidos_no_pagados'] ?></p>
                            <small class="text-muted">Total: <?= htmlspecialchars($formatCurrency((float) $clienteMetrics['monto_no_pagado'])) ?></small>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="tl-portal-kpi tl-portal-kpi--success">
                            <p class="tl-portal-kpi__label">Pedidos en tránsito</p>
                            <p class="tl-portal-kpi__value mb-0"><?= (int) $clienteMetrics['pedidos_en_transito'] ?></p>
                        </article>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <article class="tl-portal-kpi">
                            <p class="tl-portal-kpi__label">Pedidos activos</p>
                            <p class="tl-portal-kpi__value mb-0"><?= (int) $clienteMetrics['pedidos_activos'] ?></p>
                        </article>
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
