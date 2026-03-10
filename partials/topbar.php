<?php
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/CompanyConfigService.php';

$user = AuthService::user();
$cliente = AuthService::cliente();
$isClientePortal = $cliente !== null && $user === null;
$companyConfig = CompanyConfigService::get();
$logoPath = $companyConfig['logo_path'] ?: 'assets/source/images/logo-tulista-mark.svg';
$profileName = $isClientePortal ? ($cliente['nombre'] ?? 'Cliente') : ($user['nombre'] ?? 'Usuario');
$profileSubtitle = $isClientePortal ? ('RUT: ' . ($cliente['rut'] ?? '-')) : ('Rol: ' . ($user['rol'] ?? 'usuario'));
$logoutUrl = $isClientePortal ? 'logout-clientes.php' : 'logout-usuarios.php';
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
                    <div class="tl-brand-block">
                         <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo empresa" class="tl-brand-logo">
                         <span class="tl-brand-name"><?= htmlspecialchars($companyConfig['nombre']) ?></span>
                    </div>
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
                              <h6 class="dropdown-header mb-0"><?= htmlspecialchars($profileName) ?></h6>
                              <p class="px-3 py-1 text-muted small mb-0"><?= htmlspecialchars($profileSubtitle) ?></p>
                              <?php if ($isClientePortal): ?>
                                   <p class="px-3 text-muted small mb-1"><?= htmlspecialchars($cliente['email'] ?? '') ?></p>
                              <?php else: ?>
                              <a class="dropdown-item" href="apps-configuracion-empresa.php">
                                   <i class="bx bx-cog text-muted fs-18 align-middle me-1"></i><span class="align-middle">Configuración empresa</span>
                              </a>
                              <?php endif; ?>
                              <div class="dropdown-divider my-1"></div>
                              <a class="dropdown-item text-danger" href="<?= htmlspecialchars($logoutUrl) ?>">
                                   <i class="bx bx-log-out fs-18 align-middle me-1"></i><span class="align-middle">Cerrar sesión</span>
                              </a>
                         </div>
                    </div>
               </div>
          </div>
     </div>
</header>
