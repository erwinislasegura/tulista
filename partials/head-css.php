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

    <link href="assets/source/css/dashboard.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config js (Require in all Page) -->
    <script src="assets/js/config.min.js"></script>
<?php else: ?>
    <!-- Fallback CSS stack when precompiled assets are not available -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" type="text/css" />

    <!-- Plugin CSS used across root views -->
    <link href="https://cdn.jsdelivr.net/npm/simplebar@6.2.7/dist/simplebar.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/dropzone@6.0.0-beta.2/dist/dropzone.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/nano.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet" type="text/css" />

    <!-- Local fallback theme for app-like layout -->
    <link href="assets/source/css/fallback-theme.css" rel="stylesheet" type="text/css" />
    <link href="assets/source/css/dashboard.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config js (fallback path) -->
    <script src="assets/source/js/config.js"></script>
<?php endif; ?>
