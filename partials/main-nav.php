<?php
require_once __DIR__ . '/../services/CompanyConfigService.php';
$companyConfig = CompanyConfigService::get();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$logoPath = $companyConfig['logo_path'] ?: 'assets/source/images/logo-tulista-mark.svg';
?>
<div class="main-nav">
     <div class="logo-box py-3 px-3">
          <a href="index.php" class="logo-dark d-flex align-items-center gap-2 text-decoration-none tl-brand-block">
               <img src="<?= htmlspecialchars($logoPath) ?>" class="logo-sm tl-brand-logo" alt="logo" style="height:34px; width:34px;">
               <span class="fw-semibold tl-brand-name text-white"><?= htmlspecialchars($companyConfig['nombre']) ?></span>
          </a>
     </div>

     <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <iconify-icon icon="solar:hamburger-menu-broken" class="button-sm-hover-icon"></iconify-icon>
     </button>

     <div class="scrollbar" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">
               <li class="menu-title">Principal</li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php"><span class="nav-icon"><iconify-icon icon="solar:widget-2-broken"></iconify-icon></span><span class="nav-text">Dashboard</span></a></li>

               <li class="menu-title">Ventas</li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-cotizaciones.php' ? 'active' : '' ?>" href="apps-cotizaciones.php"><span class="nav-icon"><iconify-icon icon="solar:bill-list-broken"></iconify-icon></span><span class="nav-text">Cotizaciones</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-pedidos.php' ? 'active' : '' ?>" href="apps-pedidos.php"><span class="nav-icon"><iconify-icon icon="solar:cart-large-broken"></iconify-icon></span><span class="nav-text">Pedidos</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-clientes.php' ? 'active' : '' ?>" href="apps-clientes.php"><span class="nav-icon"><iconify-icon icon="solar:users-group-rounded-broken"></iconify-icon></span><span class="nav-text">Clientes</span></a></li>

               <li class="menu-title">Operación</li>
               <li class="nav-item"><a class="nav-link <?= in_array($currentPage, ['apps-productos.php','apps-productos-categorias.php','apps-productos-marcas.php','apps-productos-unidades.php','apps-productos-importacion.php'], true) ? 'active' : '' ?>" href="apps-productos.php"><span class="nav-icon"><iconify-icon icon="solar:box-broken"></iconify-icon></span><span class="nav-text">Productos</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-inventario.php' ? 'active' : '' ?>" href="apps-inventario.php"><span class="nav-icon"><iconify-icon icon="solar:archive-broken"></iconify-icon></span><span class="nav-text">Inventario</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-reportes.php' ? 'active' : '' ?>" href="apps-reportes.php"><span class="nav-icon"><iconify-icon icon="solar:chart-square-broken"></iconify-icon></span><span class="nav-text">Reportes</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-auditoria.php' ? 'active' : '' ?>" href="apps-auditoria.php"><span class="nav-icon"><iconify-icon icon="solar:document-text-broken"></iconify-icon></span><span class="nav-text">Auditoría</span></a></li>

               <li class="menu-title">Sistema</li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-usuarios.php' ? 'active' : '' ?>" href="apps-usuarios.php"><span class="nav-icon"><iconify-icon icon="solar:user-id-broken"></iconify-icon></span><span class="nav-text">Usuarios</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-mantenedores.php' ? 'active' : '' ?>" href="apps-mantenedores.php"><span class="nav-icon"><iconify-icon icon="solar:slider-horizontal-broken"></iconify-icon></span><span class="nav-text">Mantenedores</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-configuracion-empresa.php' ? 'active' : '' ?>" href="apps-configuracion-empresa.php"><span class="nav-icon"><iconify-icon icon="solar:settings-broken"></iconify-icon></span><span class="nav-text">Configuración</span></a></li>
               <li class="nav-item mt-2"><a class="nav-link" href="logout-usuarios.php"><span class="nav-icon"><iconify-icon icon="solar:logout-2-broken"></iconify-icon></span><span class="nav-text">Cerrar sesión</span></a></li>
          </ul>
     </div>
</div>
