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

$formatCurrency = static function (float $value): string {
    return '$' . number_format($value, 0, ',', '.');
};

$sections = [
    'cotizar' => [
        'label' => 'Cotizar',
        'icon' => 'solar:bill-list-broken',
        'file' => __DIR__ . '/views/cliente_portal/section-cotizar.php',
    ],
    'aprobar' => [
        'label' => 'Aprobar cotización',
        'icon' => 'solar:check-square-broken',
        'file' => __DIR__ . '/views/cliente_portal/section-aprobar.php',
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
            --tl-cliente-primary: #0d9488;
            --tl-cliente-secondary: #14b8a6;
            --tl-cliente-soft: #ccfbf1;
        }

        .tl-cliente .main-nav,
        .tl-cliente .topbar {
            background: linear-gradient(145deg, var(--tl-cliente-primary), var(--tl-cliente-secondary));
        }

        .tl-cliente .main-nav .nav-link.active,
        .tl-cliente .main-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .tl-cliente .section-chip {
            background: var(--tl-cliente-soft);
            color: #0f766e;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .8rem;
            font-size: .85rem;
            font-weight: 600;
        }

        .tl-login-gradient {
            background: radial-gradient(circle at top left, #d1fae5 0%, #f8fafc 45%, #cffafe 100%);
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

    if (rows.length && productCount && totalText) {
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
                row.classList.toggle('d-none', !row.dataset.name.includes(term));
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
    }
})();
</script>
</body>
</html>
