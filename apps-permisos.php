<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/controllers/PermisoController.php';
require_once __DIR__ . '/services/AuthorizationService.php';
AuthorizationService::requirePermission('usuarios.manage');
$controller = new PermisoController();
$data = $controller->handleRequest();
?>
<head><?php $title = 'Permisos'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?></head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <h4 class="mb-3">Mantenedor de permisos por rol</h4>
            <?php include __DIR__ . '/views/permisos/index.php'; ?>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
