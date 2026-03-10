<?php include 'partials/main.php'; ?>
<?php require_once __DIR__ . '/controllers/ProductosController.php'; ?>
<?php
$controller = new ProductosController();
$data = $controller->handleRequest('apps-productos.php');
$pageTitle = 'Ingreso de productos';
$formTitle = 'Registrar producto';
$formFile = __DIR__ . '/views/productos/forms/producto_form.php';
$listFile = __DIR__ . '/views/productos/forms/producto_list.php';
?>
<head>
    <?php $title = 'Productos'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?>
</head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <?php include __DIR__ . '/views/productos/layout.php'; ?>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
</body>
</html>
