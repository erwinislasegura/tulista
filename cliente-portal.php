<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/CotizacionController.php';
$controller = new CotizacionController();
$data = $controller->handlePortalRequest();
?>
<head><?php $title = 'Portal cliente'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">Portal de cotizaciones</h4>
                    <p class="text-muted mb-0">Gestiona tus cotizaciones y pedidos en un solo lugar.</p>
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
