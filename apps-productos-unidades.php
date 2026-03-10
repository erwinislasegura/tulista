<?php include 'partials/main.php'; ?>
<?php require_once __DIR__ . '/controllers/ProductosController.php'; ?>
<?php
$controller = new ProductosController();
$data = $controller->handleRequest('apps-productos-unidades.php');
$pageTitle = 'Ingreso de unidades de medida';
$formTitle = 'Registrar unidad';
$formFile = __DIR__ . '/views/productos/forms/unidad_form.php';
$listFile = __DIR__ . '/views/productos/forms/unidad_list.php';
?>
<head>
    <?php $title = 'Unidades'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?>
</head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <?php include __DIR__ . '/views/productos/layout.php'; ?>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
