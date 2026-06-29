<?php
require_once __DIR__ . '/controllers/DashboardController.php';

try {
    $controller = new DashboardController();
    $data = $controller->handleRequest();
} catch (Throwable $e) {
    error_log('[index.php] Dashboard bootstrap error: ' . $e->getMessage());
    http_response_code(500);
    $errorId = date('YmdHis');
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Error de carga</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f7f7f9; margin: 0; }
            .wrap { max-width: 680px; margin: 72px auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 24px; }
            .code { font-family: Consolas, monospace; background: #f1f3f5; padding: 2px 6px; border-radius: 4px; }
        </style>
    </head>
    <body>
    <div class="wrap">
        <h1>No se pudo cargar el panel</h1>
        <p>La aplicación no alcanzó a inicializar correctamente.</p>
        <p>Este error suele deberse a conexión de base de datos o rutas/includes en el hosting.</p>
        <p>ID de referencia: <span class="code"><?= htmlspecialchars($errorId) ?></span></p>
    </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<?php include 'partials/main.php'; ?>
<head>
    <?php $title = 'Dashboard ERP'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?>
</head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <div class="page-content">
        <div class="container-fluid">
            <?php include __DIR__ . '/views/dashboard/index.php'; ?>
        </div>
        <?php include 'partials/footer.php'; ?>
    </div>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
