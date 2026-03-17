<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/DashboardController.php';

try {
    $controller = new DashboardController();
    $data = $controller->handleRequest();
} catch (Throwable $e) {
    http_response_code(500);
    exit('Error cargando el panel principal. Revisa configuración de base de datos y migraciones en hosting.');
}
?>
<head><?php $title = 'Dashboard ERP'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body><div class="wrapper"><?php include 'partials/menu.php'; ?><div class="page-content"><div class="container-fluid"><?php include __DIR__ . '/views/dashboard/index.php'; ?></div><?php include 'partials/footer.php'; ?></div></div><?php include 'partials/vendor-scripts.php'; ?></body></html>
