<?php
$assetRoot = dirname(__DIR__) . '/assets';
$hasBuiltCss = file_exists($assetRoot . '/css/vendor.min.css')
    && file_exists($assetRoot . '/css/icons.min.css')
    && file_exists($assetRoot . '/css/app.min.css');
?>

<?php if ($hasBuiltCss): ?>
    <!-- Vendor css (Require in all Page) -->
    <link href="assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- Icons css (Require in all Page) -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- App css (Require in all Page) -->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
<?php else: ?>
    <!-- Fallback CSS when precompiled assets are not available -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

<!-- Theme Config js (Require in all Page) -->
<script src="assets/source/js/config.js"></script>
