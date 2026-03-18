<?php
require_once __DIR__ . '/controllers/ClienteAuthController.php';
require_once __DIR__ . '/controllers/CotizacionController.php';
require_once __DIR__ . '/services/AuthService.php';

$auth = new ClienteAuthController();
AuthService::startSession();

if (($_POST['action'] ?? '') === 'cerrar_sesion') {
    $auth->logoutTo('cotizar.php');
}

$cliente = AuthService::cliente();
$error = null;

if (!$cliente && $_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'login_cliente')) {
    $error = $auth->attemptLogin('cotizar.php');
}

$cliente = AuthService::cliente();
$data = null;
if ($cliente) {
    $controller = new CotizacionController();
    $data = $controller->handlePortalRequest();
}

$clienteMetrics = [
    'cotizaciones_pendientes' => 0,
    'pedidos_no_pagados' => 0,
    'pedidos_en_transito' => 0,
    'pedidos_activos' => 0,
    'monto_no_pagado' => 0.0,
];

if ($data) {
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
}

$formatCurrency = static function (float $value): string {
    return '$' . number_format($value, 0, ',', '.');
};

$sections = [
    'cotizar' => [
        'label' => 'Cotizar',
        'icon' => 'solar:bill-list-broken',
        'file' => __DIR__ . '/views/cliente_portal/section-cotizar.php',
    ],
    'cotizaciones' => [
        'label' => 'Cotizaciones registradas',
        'icon' => 'solar:bill-list-broken',
        'file' => __DIR__ . '/views/cliente_portal/section-cotizaciones.php',
    ],
    'seguimiento' => [
        'label' => 'Seguimiento pedido',
        'icon' => 'solar:cart-check-broken',
        'file' => __DIR__ . '/views/cliente_portal/section-seguimiento.php',
    ],
    'consultar' => [
        'label' => 'Consultar producto',
        'icon' => 'solar:magnifer-broken',
        'file' => __DIR__ . '/views/cliente_portal/section-consultar.php',
    ],
    'mis-pedidos' => [
        'label' => 'Mis pedidos',
        'icon' => 'solar:archive-broken',
        'file' => __DIR__ . '/views/cliente_portal/section-mis-pedidos.php',
    ],
];

$currentView = (string) ($_GET['view'] ?? 'cotizar');
if (!isset($sections[$currentView])) {
    $currentView = 'cotizar';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php $title = 'Portal cliente'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?>
    <style>
        :root {
            --tl-cliente-primary: #18253b;
            --tl-cliente-secondary: #243855;
            --tl-cliente-accent: #2fa6b9;
            --tl-cliente-accent-soft: #e6f4f7;
            --tl-cliente-success: #37b24d;
            --tl-cliente-danger: #d6455d;
            --tl-cliente-surface: #f3f5f9;
        }

        .tl-cliente {
            font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
            color: #1f2a3d;
        }

        .tl-cliente .main-nav,
        .tl-cliente .topbar {
            background: linear-gradient(145deg, var(--tl-cliente-primary), var(--tl-cliente-secondary));
        }

        .tl-cliente .topbar {
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.18);
        }

        .tl-cliente .page-content {
            padding-top: calc(var(--rsk-topbar-height, 70px) + 1.25rem);
            background: var(--tl-cliente-surface) !important;
        }

        .tl-cliente .main-nav .nav-link,
        .tl-cliente .main-nav .menu-title,
        .tl-cliente .main-nav .nav-text,
        .tl-cliente .main-nav iconify-icon {
            font-size: 0.88rem;
            letter-spacing: 0.01em;
            color: rgba(255, 255, 255, 0.92) !important;
        }

        .tl-cliente .main-nav .nav-link.active,
        .tl-cliente .main-nav .nav-link:hover {
            background: rgba(47, 166, 185, 0.22) !important;
            color: #fff !important;
        }

        .tl-cliente .section-chip {
            background: var(--tl-cliente-accent-soft);
            color: #1f5662;
            border: 1px solid rgba(47, 166, 185, 0.18);
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .8rem;
            font-size: .78rem;
            font-weight: 600;
        }

        .tl-cliente .card {
            border: 1px solid #e6eaf2;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .tl-cliente h4,
        .tl-cliente h5,
        .tl-cliente .h4,
        .tl-cliente .h5 {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .tl-cliente p,
        .tl-cliente .form-label,
        .tl-cliente .text-muted {
            font-size: 0.86rem;
        }

        .tl-cliente .card {
            border: 1px solid #e6eaf2;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .tl-cliente h4,
        .tl-cliente h5,
        .tl-cliente .h4,
        .tl-cliente .h5 {
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .tl-cliente p,
        .tl-cliente .form-label,
        .tl-cliente .text-muted {
            font-size: 0.86rem;
        }

        .tl-login-gradient {
            background: radial-gradient(circle at top left, #dbe8f7 0%, #f8fafc 48%, #ecf8fb 100%);
        }

        .tl-portal-dashboard {
            background: linear-gradient(135deg, #ffffff 0%, #f7fafc 100%);
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
            font-size: 0.74rem;
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

        .tl-portal-kpi--danger .tl-portal-kpi__value {
            color: var(--tl-cliente-danger);
        }

        .tl-portal-kpi--accent .tl-portal-kpi__value {
            color: var(--tl-cliente-accent);
        }

        .tl-portal-kpi--success .tl-portal-kpi__value {
            color: var(--tl-cliente-success);
        }

        .tl-portal-shortcuts .btn {
            font-size: 0.82rem;
            border-radius: 10px;
            padding-inline: 0.9rem;
        }

        .tl-portal-shortcuts .btn-primary {
            background: var(--tl-cliente-accent);
            border-color: var(--tl-cliente-accent);
        }

        .tl-portal-shortcuts .btn-success {
            background: var(--tl-cliente-success);
            border-color: var(--tl-cliente-success);
        }

        .tl-portal-shortcuts .btn-outline-dark {
            border-color: #c7d0e0;
            color: #334155;
        }

        .tl-product-name {
            font-size: 0.84rem;
            font-weight: 500;
            color: #334155;
        }
    </style>
</head>
<body>
<div class="wrapper tl-cliente">
    <?php if ($cliente): ?>
        <header class="topbar">
            <div class="container-fluid">
                <div class="navbar-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="topbar-item">
                            <button type="button" class="button-toggle-menu me-2">
                                <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                            </button>
                        </div>
                        <h5 class="mb-0 text-white">Portal de clientes</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-white small">Hola, <?= htmlspecialchars($cliente['nombre']) ?></span>
                    </div>
                </div>
            </div>
        </header>

        <div class="main-nav">
            <div class="logo-box py-3 px-3">
                <a href="cotizar.php?view=cotizar" class="logo-dark d-flex align-items-center gap-2 text-decoration-none tl-brand-block">
                    <img src="assets/source/images/logo-tulista-mark.svg" class="logo-sm tl-brand-logo" alt="logo" style="height:34px; width:34px;">
                    <span class="fw-semibold tl-brand-name text-white">Portal cliente</span>
                </a>
            </div>

            <button type="button" class="button-sm-hover" aria-label="Mostrar sidebar completo">
                <iconify-icon icon="solar:hamburger-menu-broken" class="button-sm-hover-icon"></iconify-icon>
            </button>

            <div class="scrollbar" data-simplebar>
                <ul class="navbar-nav" id="navbar-nav-cliente">
                    <li class="menu-title">Menú cliente</li>
                    <?php foreach ($sections as $key => $section): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $currentView === $key ? 'active' : '' ?>" href="cotizar.php?view=<?= urlencode($key) ?>">
                                <span class="nav-icon"><iconify-icon icon="<?= htmlspecialchars($section['icon']) ?>"></iconify-icon></span>
                                <span class="nav-text"><?= htmlspecialchars($section['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-item mt-2 px-2">
                        <form method="post" class="m-0">
                            <input type="hidden" name="action" value="cerrar_sesion">
                            <button class="nav-link text-white border-0 bg-transparent w-100 text-start" type="submit">
                                <span class="nav-icon"><iconify-icon icon="solar:logout-2-broken"></iconify-icon></span>
                                <span class="nav-text">Cerrar sesión</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="page-content">
            <div class="container-fluid">
                <?php foreach ($data['flash'] as $alert): ?>
                    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
                <?php endforeach; ?>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($sections[$currentView]['label']) ?></h4>
                        <p class="text-muted mb-0">Cada opción del menú ahora se muestra en su propia vista para facilitar la navegación.</p>
                    </div>
                    <div class="section-chip">
                        <iconify-icon icon="solar:widget-5-broken"></iconify-icon>
                        Vista independiente
                    </div>
                </div>

                <section class="tl-portal-dashboard">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Dashboard cliente</h5>
                            <p class="text-muted mb-0">Accesos rápidos e indicadores claves para gestionar cotizaciones y pedidos.</p>
                        </div>
                        <div class="tl-portal-shortcuts d-flex flex-wrap gap-2">
                            <a class="btn btn-sm btn-primary" href="cotizar.php?view=cotizaciones">Cotizaciones</a>
                            <a class="btn btn-sm btn-success" href="cotizar.php?view=seguimiento">Pedidos</a>
                            <a class="btn btn-sm btn-outline-dark" href="cotizar.php?view=consultar">Consultar productos</a>
                        </div>
                    </div>
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
    <?php else: ?>
        <div class="page-content tl-login-gradient" style="margin-left:0;">
            <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height:100vh;">
                <div class="card shadow-sm border-0" style="max-width: 460px; width:100%;">
                    <div class="card-body p-4">
                        <h5 class="mb-1">Acceso clientes</h5>
                        <p class="text-muted mb-3">Ingresa con tu RUT y clave para cotizar y revisar pedidos.</p>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post" class="row g-3">
                            <input type="hidden" name="action" value="login_cliente">
                            <div class="col-12">
                                <label class="form-label">RUT</label>
                                <input name="rut" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Clave</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-primary" type="submit">Ingresar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
                row.classList.toggle('d-none', !(nameMatches && stockMatches));
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
            });
            row.querySelector('[data-minus]')?.addEventListener('click', () => {
                input.value = Math.max(0, parseInt(input.value || '0', 10) - 1);
                updateSummary();
            });
            input.addEventListener('input', updateSummary);
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
