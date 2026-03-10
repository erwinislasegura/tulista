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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php $title = 'Portal cliente'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?>
</head>
<body>
<div class="wrapper">
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
                        <h5 class="mb-0">Portal de clientes</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Hola, <?= htmlspecialchars($cliente['nombre']) ?></span>
                    </div>
                </div>
            </div>
        </header>

        <div class="main-nav">
            <div class="logo-box py-3 px-3">
                <a href="#cotizar" class="logo-dark d-flex align-items-center gap-2 text-decoration-none tl-brand-block">
                    <img src="assets/source/images/logo-tulista-mark.svg" class="logo-sm tl-brand-logo" alt="logo" style="height:34px; width:34px;">
                    <span class="fw-semibold tl-brand-name text-white">Portal cliente</span>
                </a>
            </div>

            <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
                <iconify-icon icon="solar:hamburger-menu-broken" class="button-sm-hover-icon"></iconify-icon>
            </button>

            <div class="scrollbar" data-simplebar>
                <ul class="navbar-nav" id="navbar-nav-cliente">
                    <li class="menu-title">Menú cliente</li>
                    <li class="nav-item"><a class="nav-link" href="#cotizar"><span class="nav-icon"><iconify-icon icon="solar:bill-list-broken"></iconify-icon></span><span class="nav-text">Cotizar</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="#aprobar"><span class="nav-icon"><iconify-icon icon="solar:check-square-broken"></iconify-icon></span><span class="nav-text">Aprobar cotización</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="#seguimiento"><span class="nav-icon"><iconify-icon icon="solar:cart-check-broken"></iconify-icon></span><span class="nav-text">Seguimiento pedido</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="#consultar"><span class="nav-icon"><iconify-icon icon="solar:magnifer-broken"></iconify-icon></span><span class="nav-text">Consultar producto</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="#mis-pedidos"><span class="nav-icon"><iconify-icon icon="solar:archive-broken"></iconify-icon></span><span class="nav-text">Mis pedidos</span></a></li>
                    <li class="nav-item mt-2 px-2">
                        <form method="post" class="m-0">
                            <input type="hidden" name="action" value="cerrar_sesion">
                            <button class="nav-link text-danger border-0 bg-transparent w-100 text-start" type="submit">
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

                <div id="cotizar" class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Cotizar</h5>
                        <form method="post" id="cotizacion-form">
                            <input type="hidden" name="action" value="crear_cotizacion">
                            <input type="hidden" name="return_url" value="cotizar.php#cotizar">
                            <div class="row g-2 mb-3">
                                <div class="col-md-8">
                                    <input type="search" id="buscar-producto" class="form-control" placeholder="Buscar producto...">
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div><strong id="resumen-productos">0 productos</strong></div>
                                    <small>Total: <strong id="resumen-total">$0</strong></small>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="tabla-productos">
                                    <thead><tr><th>Producto</th><th>Precio</th><th style="width:220px;">Cantidad</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($data['productos'] as $producto): ?>
                                        <?php $precio = (float) $producto['precio_venta_total']; ?>
                                        <tr data-product-row data-name="<?= htmlspecialchars(strtolower($producto['nombre'])) ?>">
                                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                            <td data-precio="<?= $precio ?>"><?= htmlspecialchars($formatCurrency($precio)) ?></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary" type="button" data-minus>-</button>
                                                    <input type="number" min="0" step="1" class="form-control text-center" data-cantidad name="items[<?= (int) $producto['id'] ?>]" value="0">
                                                    <button class="btn btn-outline-secondary" type="button" data-plus>+</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button class="btn btn-primary" type="submit">Enviar cotización</button>
                        </form>
                    </div>
                </div>

                <div id="aprobar" class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Aprobar cotización</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Acción</th></tr></thead>
                                <tbody>
                                <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
                                    <tr>
                                        <td>#<?= (int) $cotizacion['id'] ?></td>
                                        <td class="text-capitalize"><?= htmlspecialchars($cotizacion['estado']) ?></td>
                                        <td><?= htmlspecialchars($formatCurrency((float) $cotizacion['total'])) ?></td>
                                        <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                                        <td>
                                            <?php if (in_array($cotizacion['estado'], ['aprobada', 'enviada'], true)): ?>
                                                <form method="post" class="m-0">
                                                    <input type="hidden" name="action" value="crear_pedido">
                                                    <input type="hidden" name="return_url" value="cotizar.php#aprobar">
                                                    <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                                                    <button class="btn btn-success btn-sm" type="submit">Aprobar cotización</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">Esperando revisión</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="seguimiento" class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Seguimiento pedido</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Pedido</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
                                <tbody>
                                <?php foreach ($data['pedidos'] as $pedido): ?>
                                    <tr>
                                        <td>#<?= (int) $pedido['id'] ?></td>
                                        <td class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td>
                                        <td><?= htmlspecialchars($formatCurrency((float) $pedido['total'])) ?></td>
                                        <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="consultar" class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Consultar producto</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Producto</th><th>Precio</th></tr></thead>
                                <tbody>
                                <?php foreach ($data['productos'] as $producto): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                        <td><?= htmlspecialchars($formatCurrency((float) $producto['precio_venta_total'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="mis-pedidos" class="card mb-3">
                    <div class="card-body">
                        <h5 class="mb-3">Mis pedidos</h5>
                        <p class="mb-0 text-muted">Total pedidos registrados: <strong><?= count($data['pedidos']) ?></strong></p>
                    </div>
                </div>
            </div>
            <?php include 'partials/footer.php'; ?>
        </div>
    <?php else: ?>
        <div class="page-content" style="margin-left:0;">
            <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height:100vh;">
                <div class="card shadow-sm" style="max-width: 460px; width:100%;">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Acceso con RUT y clave</h5>
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

    if (rows.length) {
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

    const navLinks = Array.from(document.querySelectorAll('#navbar-nav-cliente .nav-link[href^="#"]'));
    if (navLinks.length) {
        const applyActive = () => {
            const hash = window.location.hash || '#cotizar';
            navLinks.forEach((link) => {
                link.classList.toggle('active', link.getAttribute('href') === hash);
            });
        };

        navLinks.forEach((link) => {
            link.addEventListener('click', () => {
                navLinks.forEach((item) => item.classList.remove('active'));
                link.classList.add('active');
            });
        });

        window.addEventListener('hashchange', applyActive);
        applyActive();
    }
})();
</script>
</body>
</html>
