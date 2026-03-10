<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/ReporteController.php';
$controller = new ReporteController();
$data = $controller->handleRequest();
?>
<head><?php $title = 'Reportes'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body><div class="wrapper"><?php include 'partials/menu.php'; ?><div class="page-content"><div class="container-fluid"><h4>Reportes</h4><?php include __DIR__ . '/views/reportes/index.php'; ?></div><?php include 'partials/footer.php'; ?></div></div><?php include 'partials/vendor-scripts.php'; ?></body></html>
