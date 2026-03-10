<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/CompanyController.php';
$controller = new CompanyController();
$data = $controller->handleRequest();
?>
<head><?php $title = 'Configuración empresa'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-1">Configuración de empresa</h4>
                    <p class="text-muted mb-0">Identidad corporativa y datos fiscales para documentos ERP.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill">Módulo ERP</span>
            </div>
            <?php include __DIR__ . '/views/empresa/index.php'; ?>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
