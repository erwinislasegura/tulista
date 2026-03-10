<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/ClienteAuthController.php';
require_once __DIR__ . '/controllers/CotizacionController.php';
$auth = new ClienteAuthController();
$token = $_GET['token'] ?? '';
$cliente = $auth->loginByToken($token);
if (!$cliente) {
    http_response_code(404);
    exit('Token inválido.');
}
$controller = new CotizacionController();
$data = $controller->handlePortalRequest((int) $cliente['id']);
?>
<head><?php $title = 'Cotizar'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body><div class="wrapper"><div class="page-content"><div class="container-fluid"><h4>Cotización rápida</h4><?php include __DIR__ . '/views/cliente_portal/index.php'; ?></div><?php include 'partials/footer.php'; ?></div></div><?php include 'partials/vendor-scripts.php'; ?></body></html>
