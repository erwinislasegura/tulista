<?php include 'partials/main.php'; ?>
<?php require_once __DIR__ . '/controllers/ProductosController.php'; ?>
<?php
$controller = new ProductosController();
$data = $controller->handleRequest('apps-productos-importacion.php');
$pageTitle = 'Importación de productos';
$formTitle = 'Importar planilla';
$formFile = __DIR__ . '/views/productos/forms/import_form.php';
$listFile = __DIR__ . '/views/productos/forms/import_list.php';
?>
<head>
    <?php $title = 'Importación'; include 'partials/title-meta.php'; include 'partials/head-css.php'; ?>
</head>
<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>
    <?php include __DIR__ . '/views/productos/layout.php'; ?>
</div>
<?php include 'partials/vendor-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
(function () {
    const form = document.getElementById('import-form');
    const templateButton = document.getElementById('download-import-template');

    if (templateButton) {
        templateButton.addEventListener('click', function () {
            const headers = [
                'Categoria',
                'Marca',
                'Nombre',
                'SKU',
                'Modelo',
                'Unidad',
                'Código de barras',
                'Producto / Servicio',
                'Costo neto',
                'Venta: Precio neto',
                'Venta: Precio total',
                'Stock mínimo',
                'Comisión vendedor',
                'Existencia',
            ];
            const worksheet = XLSX.utils.json_to_sheet([], {header: headers});
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Importación');
            XLSX.writeFile(workbook, 'formato-importacion-productos.xlsx');
        });
    }

    if (!form) return;

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const input = document.getElementById('excel-file');
        const hidden = document.getElementById('import_payload');
        const file = input.files[0];
        if (!file) {
            form.submit();
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            const workbook = XLSX.read(e.target.result, {type: 'array'});
            const firstSheet = workbook.SheetNames[0];
            const rows = XLSX.utils.sheet_to_json(workbook.Sheets[firstSheet], {defval: ''});
            hidden.value = JSON.stringify(rows);
            form.submit();
        };
        reader.readAsArrayBuffer(file);
    });
})();
</script>
</body>
</html>
