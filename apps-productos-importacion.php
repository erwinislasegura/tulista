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
    const existingBrands = <?= json_encode(array_values(array_map(static function ($brand) {
        return trim((string) ($brand['nombre'] ?? ''));
    }, $data['brands'] ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const brandIndex = new Set(existingBrands.map(function (brand) {
        return brand.toLowerCase();
    }));
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
        const createMissingBrandsInput = document.getElementById('create_missing_brands');
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
            const missingBrands = [];

            rows.forEach(function (row) {
                const brandName = String(row['Marca'] ?? '').trim();
                if (!brandName) {
                    return;
                }
                const normalized = brandName.toLowerCase();
                if (!brandIndex.has(normalized) && !missingBrands.includes(brandName)) {
                    missingBrands.push(brandName);
                }
            });

            if (createMissingBrandsInput) {
                createMissingBrandsInput.value = '0';
                if (missingBrands.length > 0) {
                    const promptMessage = 'No existen estas marcas: ' + missingBrands.join(', ') + '. ¿Deseas crearlas automáticamente antes de importar?';
                    if (window.confirm(promptMessage)) {
                        createMissingBrandsInput.value = '1';
                    }
                }
            }

            hidden.value = JSON.stringify(rows);
            form.submit();
        };
        reader.readAsArrayBuffer(file);
    });
})();
</script>
</body>
</html>
