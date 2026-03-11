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
$profileInitial = strtoupper(substr(trim((string) $profileName), 0, 1));
?>
<header class="topbar">
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu tl-menu-toggle" aria-label="Mostrar u ocultar menú lateral">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>
                    <div class="tl-brand-block tl-topbar-brand">
                         <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo empresa" class="tl-brand-logo">
                         <div class="d-flex flex-column min-w-0">
                              <span class="tl-brand-name text-truncate"><?= htmlspecialchars($companyConfig['nombre']) ?></span>
                              <small class="tl-brand-subtitle text-truncate">Panel Tulista</small>
                         </div>
                    </div>
               </div>

               <div class="d-flex align-items-center gap-2 ms-2">
                    <?php if (!$isClientePortal): ?>
                    <a href="apps-configuracion-empresa.php" class="btn btn-sm btn-light tl-topbar-shortcut d-none d-md-inline-flex align-items-center gap-1">
                         <i class="bx bx-cog fs-18"></i>
                         <span>Empresa</span>
                    </a>
                    <?php endif; ?>
                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button tl-user-trigger" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center gap-2 min-w-0">
                                   <span class="tl-user-avatar"><?= htmlspecialchars($profileInitial) ?></span>
                                   <span class="d-none d-sm-flex flex-column text-start min-w-0">
                                        <strong class="tl-user-name text-truncate"><?= htmlspecialchars($profileName) ?></strong>
                                        <small class="tl-user-role text-truncate"><?= htmlspecialchars($profileSubtitle) ?></small>
                                   </span>
                                   <iconify-icon icon="solar:alt-arrow-down-broken" class="fs-16 text-muted"></iconify-icon>
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end tl-user-dropdown">
                              <h6 class="dropdown-header mb-0"><?= htmlspecialchars($profileName) ?></h6>
                              <p class="px-3 py-1 text-muted small mb-0"><?= htmlspecialchars($profileSubtitle) ?></p>
                              <?php if ($isClientePortal): ?>
                                   <p class="px-3 text-muted small mb-1"><?= htmlspecialchars($cliente['email'] ?? '') ?></p>
                              <?php else: ?>
                              <a class="dropdown-item" href="apps-configuracion-empresa.php">
                                   <i class="bx bx-cog text-muted fs-18 align-middle me-1"></i><span class="align-middle">Configuración empresa</span>
                              </a>
                              <?php endif; ?>
                              <a class="dropdown-item" href="<?= htmlspecialchars($isClientePortal ? 'cotizar.php' : 'index.php') ?>">
                                   <i class="bx bx-home-alt text-muted fs-18 align-middle me-1"></i><span class="align-middle">Ir al inicio</span>
                              </a>
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
