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
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Módulo de clientes - Cotizar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="mx-auto" style="max-width: 1240px;">
        <h3 class="mb-3">Módulo de clientes</h3>

        <?php if (!$cliente): ?>
            <div class="card shadow-sm mx-auto" style="max-width: 460px;">
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
        <?php else: ?>
            <?php foreach ($data['flash'] as $alert): ?>
                <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
            <?php endforeach; ?>

            <div class="row g-3">
                <div class="col-lg-3">
                    <div class="card shadow-sm sticky-top" style="top: 16px;">
                        <div class="card-body p-2">
                            <div class="px-2 py-2 border-bottom mb-2">
                                <div class="small text-muted">Cliente</div>
                                <strong><?= htmlspecialchars($cliente['nombre']) ?></strong>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="#cotizar" class="list-group-item list-group-item-action">Cotizar</a>
                                <a href="#aprobar" class="list-group-item list-group-item-action">Aprobar cotización</a>
                                <a href="#seguimiento" class="list-group-item list-group-item-action">Seguimiento pedido</a>
                                <a href="#consultar" class="list-group-item list-group-item-action">Consultar producto</a>
                                <a href="#mis-pedidos" class="list-group-item list-group-item-action">Mis pedidos</a>
                            </div>
                            <form method="post" class="mt-3 px-2 pb-2">
                                <input type="hidden" name="action" value="cerrar_sesion">
                                <button class="btn btn-outline-danger w-100" type="submit">Cerrar sesión</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div id="cotizar" class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="mb-3">Cotizar</h5>
                            <form method="post" id="cotizacion-form">
                                <input type="hidden" name="action" value="crear_cotizacion">
                                <input type="hidden" name="return_url" value="cotizar.php">
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

                    <div id="aprobar" class="card shadow-sm mb-3">
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
                                                        <input type="hidden" name="return_url" value="cotizar.php">
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

                    <div id="seguimiento" class="card shadow-sm mb-3">
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

                    <div id="consultar" class="card shadow-sm mb-3">
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

                    <div id="mis-pedidos" class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h5 class="mb-3">Mis pedidos</h5>
                            <p class="mb-0 text-muted">Total pedidos registrados: <strong><?= count($data['pedidos']) ?></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(() => {
    const searchInput = document.getElementById('buscar-producto');
    const rows = Array.from(document.querySelectorAll('[data-product-row]'));
    const productCount = document.getElementById('resumen-productos');
    const totalText = document.getElementById('resumen-total');

    if (!rows.length) return;

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
})();
</script>
</body>
</html>
