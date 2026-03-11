<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/AuditoriaController.php';
$controller = new AuditoriaController();
$data = $controller->handleRequest();
?>
<head><?php $title = 'Auditoría'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body><div class="wrapper"><?php include 'partials/menu.php'; ?><div class="page-content"><div class="container-fluid"><?php include __DIR__ . '/views/auditoria/index.php'; ?></div><?php include 'partials/footer.php'; ?></div></div><?php include 'partials/vendor-scripts.php'; ?></body></html>
