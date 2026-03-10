<?php
$assetRoot = dirname(__DIR__) . '/assets';
$hasBuiltJs = file_exists($assetRoot . '/js/vendor.js') && file_exists($assetRoot . '/js/app.js');
?>

<?php if ($hasBuiltJs): ?>
    <!-- Vendor Javascript (Require in all Page) -->
    <script src="assets/js/vendor.js"></script>

    <!-- App Javascript (Require in all Page) -->
    <script src="assets/js/app.js"></script>
<?php else: ?>
    <!-- Fallback JS when precompiled assets are not available -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/source/js/app.js"></script>
<?php endif; ?>
