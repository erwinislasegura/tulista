<?php include 'partials/main.php'; ?>
<?php require_once __DIR__ . '/controllers/ProductosController.php'; ?>
<?php
$controller = new ProductosController();
$data = $controller->handleRequest();
?>

<head>
    <?php
    $title = 'Productos';
    include 'partials/title-meta.php';
    include 'partials/head-css.php';
    ?>
</head>

<body>
<div class="wrapper">
    <?php include 'partials/menu.php'; ?>

    <?php include __DIR__ . '/views/productos/index.php'; ?>
</div>

<?php include 'partials/vendor-scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    (function () {
        const form = document.getElementById('import-form');
        if (!form) {
            return;
        }

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

        const activateTabFromHash = function () {
            if (!location.hash) {
                return;
            }

            const trigger = document.querySelector('[data-bs-target="' + location.hash + '"]');
            if (!trigger) {
                return;
            }

            const tab = new bootstrap.Tab(trigger);
            tab.show();
        };

        activateTabFromHash();
    })();
</script>
</body>
</html>
