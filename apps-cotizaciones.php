<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/CotizacionController.php';
$controller = new CotizacionController();
$data = $controller->handleAdminRequest();
?>
<head><?php $title = 'Cotizaciones'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body><div class="wrapper"><?php include 'partials/menu.php'; ?><div class="page-content"><div class="container-fluid"><?php include __DIR__ . '/views/cotizaciones/admin.php'; ?></div><?php include 'partials/footer.php'; ?></div></div><?php include 'partials/vendor-scripts.php'; ?></body></html>
