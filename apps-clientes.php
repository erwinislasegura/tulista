<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/ClienteController.php';
$controller = new ClienteController();
$data = $controller->handleRequest();
?>
<head><?php $title = 'Clientes'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">Clientes</h4>
                    <p class="text-muted mb-0">Administración comercial, historial y acceso al portal cliente.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill">Módulo ERP</span>
            </div>
            <?php include __DIR__ . '/views/clientes/index.php'; ?>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
