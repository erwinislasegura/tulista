<?php
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/CompanyConfigService.php';
$companyConfig = CompanyConfigService::get();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$logoPath = $companyConfig['logo_path'] ?: 'assets/source/images/logo-tulista-mark.svg';
$cliente = AuthService::cliente();
$user = AuthService::user();
$isClientePortal = $cliente !== null && $user === null;

$menu = [
    'Principal' => [
        ['page' => 'index.php', 'text' => 'Dashboard', 'icon' => 'solar:widget-2-broken', 'perm' => 'dashboard.view'],
    ],
    'Flujo comercial' => [
        ['page' => 'apps-cotizaciones.php', 'text' => '1. Cotizaciones', 'icon' => 'solar:bill-list-broken', 'perm' => 'cotizaciones.manage'],
        ['page' => 'apps-pedidos.php', 'text' => '2. Pedidos', 'icon' => 'solar:cart-large-broken', 'perm' => 'pedidos.view'],
        ['page' => 'apps-clientes.php', 'text' => '3. Clientes', 'icon' => 'solar:users-group-rounded-broken', 'perm' => 'clientes.manage'],
    ],
    'Operación' => [
        ['page' => 'apps-productos.php', 'text' => 'Productos', 'icon' => 'solar:box-broken', 'perm' => 'productos.view', 'pages' => ['apps-productos.php','apps-productos-categorias.php','apps-productos-marcas.php','apps-productos-unidades.php','apps-productos-importacion.php']],
        ['page' => 'apps-inventario.php', 'text' => 'Inventario', 'icon' => 'solar:archive-broken', 'perm' => 'inventario.view'],
        ['page' => 'apps-bodega.php', 'text' => 'Bodega', 'icon' => 'solar:box-minimalistic-broken', 'perm' => 'bodega.view'],
    ],
    'Control' => [
        ['page' => 'apps-reportes.php', 'text' => 'Reportes', 'icon' => 'solar:chart-square-broken', 'perm' => 'reportes.view'],
        ['page' => 'apps-auditoria.php', 'text' => 'Auditoría', 'icon' => 'solar:document-text-broken', 'perm' => 'auditoria.view'],
    ],
    'Sistema' => [
        ['page' => 'apps-usuarios.php', 'text' => 'Usuarios y permisos', 'icon' => 'solar:user-id-broken', 'perm' => 'usuarios.manage'],
        ['page' => 'apps-mantenedores.php', 'text' => 'Mantenedores', 'icon' => 'solar:slider-horizontal-broken', 'perm' => 'usuarios.manage'],
        ['page' => 'apps-configuracion-empresa.php', 'text' => 'Configuración', 'icon' => 'solar:settings-broken', 'perm' => 'configuracion.view'],
    ],
];
?>
<div class="main-nav">
     <div class="logo-box py-3 px-3">
          <a href="<?= $isClientePortal ? 'cotizar.php' : 'index.php' ?>" class="logo-dark d-flex align-items-center gap-2 text-decoration-none tl-brand-block">
               <img src="<?= htmlspecialchars($logoPath) ?>" class="logo-sm tl-brand-logo" alt="logo" style="height:34px; width:34px;">
               <span class="fw-semibold tl-brand-name text-white"><?= htmlspecialchars($companyConfig['nombre']) ?></span>
          </a>
     </div>

     <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <iconify-icon icon="solar:hamburger-menu-broken" class="button-sm-hover-icon"></iconify-icon>
     </button>

     <div class="scrollbar" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">
               <?php if ($isClientePortal): ?>
                    <li class="menu-title">Portal cliente</li>
                    <li class="nav-item"><a class="nav-link" href="<?= $currentPage ?>#hacer-cotizacion"><span class="nav-icon"><iconify-icon icon="solar:bill-list-broken"></iconify-icon></span><span class="nav-text">1. Hacer cotización</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $currentPage ?>#aprobar-cotizacion"><span class="nav-icon"><iconify-icon icon="solar:check-circle-broken"></iconify-icon></span><span class="nav-text">2. Aprobar cotización</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $currentPage ?>#seguimiento-pedido"><span class="nav-icon"><iconify-icon icon="solar:delivery-broken"></iconify-icon></span><span class="nav-text">3. Seguimiento pedido</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $currentPage ?>#estado-pagos"><span class="nav-icon"><iconify-icon icon="solar:wallet-money-broken"></iconify-icon></span><span class="nav-text">4. Estado de pagos</span></a></li>
                    <li class="nav-item mt-2"><a class="nav-link" href="logout-clientes.php"><span class="nav-icon"><iconify-icon icon="solar:logout-2-broken"></iconify-icon></span><span class="nav-text">Cerrar sesión</span></a></li>
               <?php else: ?>
                    <?php foreach ($menu as $section => $items): ?>
                         <?php
                         $visible = array_filter($items, static fn ($item) => AuthorizationService::can($item['perm']));
                         if (empty($visible)) {
                             continue;
                         }
                         ?>
                         <li class="menu-title"><?= htmlspecialchars($section) ?></li>
                         <?php foreach ($visible as $item): ?>
                              <?php $activePages = $item['pages'] ?? [$item['page']]; ?>
                              <li class="nav-item"><a class="nav-link <?= in_array($currentPage, $activePages, true) ? 'active' : '' ?>" href="<?= htmlspecialchars($item['page']) ?>"><span class="nav-icon"><iconify-icon icon="<?= htmlspecialchars($item['icon']) ?>"></iconify-icon></span><span class="nav-text"><?= htmlspecialchars($item['text']) ?></span></a></li>
                         <?php endforeach; ?>
                    <?php endforeach; ?>
                    <li class="nav-item mt-2"><a class="nav-link" href="logout-usuarios.php"><span class="nav-icon"><iconify-icon icon="solar:logout-2-broken"></iconify-icon></span><span class="nav-text">Cerrar sesión</span></a></li>
               <?php endif; ?>
          </ul>
     </div>
</div>
