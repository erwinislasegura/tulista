<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/ClienteAuthController.php';
require_once __DIR__ . '/controllers/CotizacionController.php';
require_once __DIR__ . '/services/AuthService.php';
$auth = new ClienteAuthController();
$token = $_GET['token'] ?? '';
$cliente = $auth->loginByToken($token);
if (!$cliente) {
    http_response_code(404);
    exit('Token inválido.');
}
AuthService::loginCliente($cliente);
$controller = new CotizacionController();
$data = $controller->handlePortalRequest((int) $cliente['id']);
?>
<head><?php $title = 'Cotizar'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">Portal de cotización</h4>
                    <p class="text-muted mb-0">Flujo ERP para cotizar, aprobar y seguir pedidos.</p>
                </div>
            </div>
            <?php include __DIR__ . '/views/cliente_portal/index.php'; ?>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
