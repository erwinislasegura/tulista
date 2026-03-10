<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/ClienteController.php';
$controller = new ClienteController();
$data = $controller->handleRequest();
?>
<head><?php $title = 'Clientes'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body><div class="wrapper"><?php include 'partials/menu.php'; ?><div class="page-content"><div class="container-fluid"><h4>Clientes</h4><?php include __DIR__ . '/views/clientes/index.php'; ?></div><?php include 'partials/footer.php'; ?></div></div><?php include 'partials/vendor-scripts.php'; ?></body></html>
