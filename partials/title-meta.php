<!-- Title Meta -->
<meta charset="utf-8" />
<title><?php echo $title ?>| Rasket - Responsive Admin Dashboard Template</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="A fully responsive premium admin dashboard template" />
<meta name="author" content="Techzaa" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<!-- App favicon -->
<link rel="shortcut icon" href="assets/images/favicon.ico">

<?php
$portalApp = $portalApp ?? 'admin';
$isClientePortal = $portalApp === 'cliente';
$manifestFile = $isClientePortal ? 'manifest-cliente.webmanifest' : 'manifest-admin.webmanifest';
$themeColor = $isClientePortal ? '#0D6EFD' : '#3A136A';
?>
<link rel="manifest" href="<?= htmlspecialchars($manifestFile, ENT_QUOTES, 'UTF-8') ?>">
<meta name="theme-color" content="<?= htmlspecialchars($themeColor, ENT_QUOTES, 'UTF-8') ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
