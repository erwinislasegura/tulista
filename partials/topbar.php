<?php
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/CompanyConfigService.php';

$user = AuthService::user();
$companyConfig = CompanyConfigService::get();
$logoPath = $companyConfig['logo_path'] ?: 'assets/images/logo-dark.png';
?>
<header class="topbar">
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center gap-2">
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu me-2">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>
                    <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo empresa" style="height:28px; width:auto;">
                    <span class="fw-semibold"><?= htmlspecialchars($companyConfig['nombre']) ?></span>
               </div>

               <div class="d-flex align-items-center gap-1">
                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center gap-2">
                                   <span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                                        <iconify-icon icon="solar:user-circle-broken" class="fs-20"></iconify-icon>
                                   </span>
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end">
                              <h6 class="dropdown-header">Hola <?= htmlspecialchars($user['nombre'] ?? 'Usuario') ?></h6>
                              <a class="dropdown-item" href="apps-configuracion-empresa.php">
                                   <i class="bx bx-cog text-muted fs-18 align-middle me-1"></i><span class="align-middle">Configuración empresa</span>
                              </a>
                              <div class="dropdown-divider my-1"></div>
                              <a class="dropdown-item text-danger" href="logout-usuarios.php">
                                   <i class="bx bx-log-out fs-18 align-middle me-1"></i><span class="align-middle">Cerrar sesión</span>
                              </a>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</header>
