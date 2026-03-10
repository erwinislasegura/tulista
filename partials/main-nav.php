<?php
require_once __DIR__ . '/../services/CompanyConfigService.php';
$companyConfig = CompanyConfigService::get();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$logoPath = $companyConfig['logo_path'] ?: 'assets/images/logo-dark.png';
?>
<div class="main-nav">
     <div class="logo-box py-3 px-3">
          <a href="index.php" class="logo-dark d-flex align-items-center gap-2 text-decoration-none">
               <img src="<?= htmlspecialchars($logoPath) ?>" class="logo-sm" alt="logo" style="height:32px; width:auto;">
               <span class="fw-semibold text-white"><?= htmlspecialchars($companyConfig['nombre']) ?></span>
          </a>

          <a href="index.php" class="logo-light d-flex align-items-center gap-2 text-decoration-none">
               <img src="<?= htmlspecialchars($logoPath) ?>" class="logo-sm" alt="logo" style="height:32px; width:auto;">
               <span class="fw-semibold text-white"><?= htmlspecialchars($companyConfig['nombre']) ?></span>
          </a>
     </div>

     <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <iconify-icon icon="solar:hamburger-menu-broken" class="button-sm-hover-icon"></iconify-icon>
     </button>

     <div class="scrollbar" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">
               <li class="menu-title">Panel</li>

               <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">
                         <span class="nav-icon"><iconify-icon icon="solar:widget-2-broken"></iconify-icon></span>
                         <span class="nav-text">Dashboard</span>
                    </a>
               </li>

               <li class="menu-title">Gestión</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarProductos" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebarProductos">
                         <span class="nav-icon"><iconify-icon icon="solar:box-broken"></iconify-icon></span>
                         <span class="nav-text">Productos</span>
                    </a>
                    <div class="collapse show" id="sidebarProductos">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="apps-productos.php">Ingreso de productos</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="apps-productos-categorias.php">Categorías</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="apps-productos-marcas.php">Marcas</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="apps-productos-unidades.php">Unidades</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="apps-productos-importacion.php">Importación</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-clientes.php' ? 'active' : '' ?>" href="apps-clientes.php"><span class="nav-icon"><iconify-icon icon="solar:users-group-rounded-broken"></iconify-icon></span><span class="nav-text">Clientes</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-cotizaciones.php' ? 'active' : '' ?>" href="apps-cotizaciones.php"><span class="nav-icon"><iconify-icon icon="solar:bill-list-broken"></iconify-icon></span><span class="nav-text">Cotizaciones</span></a></li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-usuarios.php' ? 'active' : '' ?>" href="apps-usuarios.php"><span class="nav-icon"><iconify-icon icon="solar:user-id-broken"></iconify-icon></span><span class="nav-text">Usuarios</span></a></li>

               <li class="menu-title">Configuración</li>
               <li class="nav-item"><a class="nav-link <?= $currentPage === 'apps-configuracion-empresa.php' ? 'active' : '' ?>" href="apps-configuracion-empresa.php"><span class="nav-icon"><iconify-icon icon="solar:settings-broken"></iconify-icon></span><span class="nav-text">Empresa</span></a></li>

               <li class="nav-item mt-2"><a class="nav-link" href="logout-usuarios.php"><span class="nav-icon"><iconify-icon icon="solar:logout-2-broken"></iconify-icon></span><span class="nav-text">Cerrar sesión</span></a></li>
          </ul>
     </div>
</div>
