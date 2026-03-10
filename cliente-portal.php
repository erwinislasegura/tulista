<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/CotizacionController.php';
$controller = new CotizacionController();
$data = $controller->handlePortalRequest();
?>
<head><?php $title = 'Portal cliente'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body><div class="wrapper"><div class="page-content"><div class="container-fluid"><h4>Portal de cotizaciones</h4><?php include __DIR__ . '/views/cliente_portal/index.php'; ?></div><?php include 'partials/footer.php'; ?></div></div><?php include 'partials/vendor-scripts.php'; ?></body></html>
