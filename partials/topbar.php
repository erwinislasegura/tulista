<?php
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/CompanyConfigService.php';

$user = AuthService::user();
$cliente = AuthService::cliente();
$isClientePortal = $cliente !== null && $user === null;
$companyConfig = CompanyConfigService::get();
$profileName = $isClientePortal ? ($cliente['nombre'] ?? 'Cliente') : ($user['nombre'] ?? 'Usuario');
$profileSubtitle = $isClientePortal ? ('RUT: ' . ($cliente['rut'] ?? '-')) : ('Rol: ' . ($user['rol'] ?? 'usuario'));
$logoutUrl = $isClientePortal ? 'logout-clientes.php' : 'logout-usuarios.php';
$homeUrl = $isClientePortal ? 'cotizar.php' : 'index.php';
$profileInitial = strtoupper(substr(trim((string) $profileName), 0, 1));
$currentTitle = isset($title) && trim((string) $title) !== '' ? (string) $title : 'Dashboard';
$topbarContext = $isClientePortal ? 'Portal cliente' : ($companyConfig['nombre'] ?? 'Tulista');
?>
<header class="topbar tl-topbar-clean">
     <div class="container-fluid">
          <div class="navbar-header tl-topbar-layout">
               <div class="tl-topbar-left min-w-0">
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu tl-menu-toggle" aria-label="Mostrar u ocultar menú lateral">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-22 align-middle"></iconify-icon>
                         </button>
                    </div>
                    <div class="tl-topbar-title-wrap min-w-0">
                         <span class="tl-topbar-title text-truncate"><?= htmlspecialchars($currentTitle) ?></span>
                         <span class="tl-topbar-context text-truncate"><?= htmlspecialchars($topbarContext) ?></span>
                    </div>
               </div>

               <div class="tl-topbar-right">
                    <a href="<?= htmlspecialchars($homeUrl) ?>" class="topbar-button tl-topbar-action" title="Inicio" aria-label="Ir al inicio">
                         <iconify-icon icon="solar:home-angle-broken" class="fs-20"></iconify-icon>
                    </a>
                    <?php if (!$isClientePortal): ?>
                    <a href="apps-configuracion-empresa.php" class="topbar-button tl-topbar-action" title="Configuración" aria-label="Configuración empresa">
                         <iconify-icon icon="solar:settings-broken" class="fs-20"></iconify-icon>
                    </a>
                    <?php endif; ?>
                    <button type="button" class="topbar-button tl-topbar-action" data-toggle="fullscreen" title="Pantalla completa" aria-label="Pantalla completa">
                         <iconify-icon icon="solar:maximize-square-broken" class="fs-20"></iconify-icon>
                    </button>

                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button tl-user-trigger" id="user-menu-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="tl-user-avatar"><?= htmlspecialchars($profileInitial) ?></span>
                              <span class="tl-user-meta d-none d-lg-flex">
                                   <strong class="tl-user-name text-truncate"><?= htmlspecialchars($profileName) ?></strong>
                                   <small class="tl-user-role text-truncate"><?= htmlspecialchars($profileSubtitle) ?></small>
                              </span>
                              <iconify-icon icon="solar:alt-arrow-down-broken" class="fs-16"></iconify-icon>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end tl-user-dropdown">
                              <h6 class="dropdown-header mb-0"><?= htmlspecialchars($profileName) ?></h6>
                              <p class="px-3 py-1 text-muted small mb-1"><?= htmlspecialchars($profileSubtitle) ?></p>
                              <?php if ($isClientePortal): ?>
                                   <p class="px-3 text-muted small mb-2"><?= htmlspecialchars($cliente['email'] ?? '') ?></p>
                              <?php else: ?>
                                   <a class="dropdown-item" href="apps-configuracion-empresa.php">
                                        <i class="bx bx-cog text-muted fs-18 align-middle me-1"></i><span class="align-middle">Configuración empresa</span>
                                   </a>
                              <?php endif; ?>
                              <a class="dropdown-item" href="<?= htmlspecialchars($homeUrl) ?>">
                                   <i class="bx bx-home-alt text-muted fs-18 align-middle me-1"></i><span class="align-middle">Inicio</span>
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
