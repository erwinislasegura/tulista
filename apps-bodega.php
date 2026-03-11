<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/BodegaController.php';
$controller = new BodegaController();
$data = $controller->handleRequest();
?>
<head><?php $title = 'Bodega'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <?php include __DIR__ . '/views/bodega/index.php'; ?>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
