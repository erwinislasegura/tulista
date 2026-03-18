<?php
require_once __DIR__ . '/controllers/ClienteAuthController.php';
$controller = new ClienteAuthController();
$view = $controller->processPublicAccess();
require_once __DIR__ . '/services/CompanyConfigService.php';
$company = CompanyConfigService::get();
$old = $view['old'] ?? [];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso clientes | <?= htmlspecialchars($company['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/source/css/dashboard.css" rel="stylesheet">
    <style>
        .auth-page { min-height: 100vh; background: linear-gradient(135deg, #f4f6fb 0%, #eef2ff 100%); display: flex; align-items: center; }
        .auth-main-card { border: 0; border-radius: 1rem; box-shadow: 0 18px 45px rgba(58, 19, 106, .12); }
        .auth-side { background: #3A136A; color: #fff; border-radius: 1rem 0 0 1rem; }
        .auth-side .btn-outline-light:hover { color: #3A136A; }
        @media (max-width: 991.98px) { .auth-side { border-radius: 1rem 1rem 0 0; } }
    </style>
</head>
<body>
<div class="auth-page py-4">
    <div class="container">
        <div class="card auth-main-card overflow-hidden">
            <div class="row g-0">
                <div class="col-lg-4 p-4 p-lg-5 auth-side d-flex flex-column justify-content-between">
                    <div>
                        <img src="<?= htmlspecialchars($company['logo_path']) ?>" alt="Logo" style="max-height:56px;width:auto;filter:brightness(0) invert(1);">
                        <h2 class="h4 mt-4 mb-3">Portal de clientes</h2>
                        <p class="mb-0 opacity-75">Cotiza, revisa pedidos y administra tu cuenta desde una sola app.</p>
                    </div>
                    <div class="mt-4">
                        <div class="small text-white-50 mb-2">¿Eres administrador?</div>
                        <a href="auth-login-usuarios.php" class="btn btn-outline-light btn-sm">Ir a login de administración</a>
                    </div>
                </div>

                <div class="col-lg-8 p-4 p-lg-5 bg-white">
                    <ul class="nav nav-pills mb-4" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-login" type="button">Iniciar sesión</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-register" type="button">Registrarme</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-reset" type="button">Olvidé mi contraseña</button></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-login">
                            <h3 class="h5 mb-3">Bienvenido</h3>
                            <?php if (!empty($view['login_error'])): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($view['login_error']) ?></div><?php endif; ?>
                            <?php if (!empty($view['register_success'])): ?><div class="alert alert-success py-2"><?= htmlspecialchars($view['register_success']) ?></div><?php endif; ?>
                            <?php if (!empty($view['reset_success'])): ?><div class="alert alert-success py-2"><?= htmlspecialchars($view['reset_success']) ?></div><?php endif; ?>
                            <form method="post" class="row g-3">
                                <input type="hidden" name="action" value="login_cliente">
                                <div class="col-md-6">
                                    <label class="form-label">RUT</label>
                                    <input name="rut" class="form-control" placeholder="12.345.678-9" value="<?= htmlspecialchars($old['login_rut'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>
                                <div class="col-12 d-grid d-md-flex justify-content-md-end">
                                    <button class="btn btn-primary px-4" type="submit">Ingresar al portal</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-register">
                            <h3 class="h5 mb-3">Registro de nuevo cliente</h3>
                            <?php if (!empty($view['register_error'])): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($view['register_error']) ?></div><?php endif; ?>
                            <form method="post" class="row g-3">
                                <input type="hidden" name="action" value="register_cliente">
                                <div class="col-md-6"><label class="form-label">RUT</label><input name="register_rut" class="form-control" value="<?= htmlspecialchars($old['register_rut'] ?? '') ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Nombre completo</label><input name="register_nombre" class="form-control" value="<?= htmlspecialchars($old['register_nombre'] ?? '') ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="register_email" class="form-control" value="<?= htmlspecialchars($old['register_email'] ?? '') ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Teléfono</label><input name="register_telefono" class="form-control" value="<?= htmlspecialchars($old['register_telefono'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" name="register_password" class="form-control" minlength="6" required></div>
                                <div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input type="password" name="register_password_confirm" class="form-control" minlength="6" required></div>
                                <div class="col-12 d-grid d-md-flex justify-content-md-end"><button class="btn btn-primary px-4" type="submit">Crear cuenta</button></div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="tab-reset">
                            <h3 class="h5 mb-3">Recuperar contraseña</h3>
                            <p class="text-muted small">Por seguridad validamos tu RUT + email para definir una nueva contraseña.</p>
                            <?php if (!empty($view['reset_error'])): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($view['reset_error']) ?></div><?php endif; ?>
                            <form method="post" class="row g-3">
                                <input type="hidden" name="action" value="reset_password_cliente">
                                <div class="col-md-6"><label class="form-label">RUT</label><input name="reset_rut" class="form-control" value="<?= htmlspecialchars($old['reset_rut'] ?? '') ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="reset_email" class="form-control" value="<?= htmlspecialchars($old['reset_email'] ?? '') ?>" required></div>
                                <div class="col-md-6"><label class="form-label">Nueva contraseña</label><input type="password" name="reset_password" class="form-control" minlength="6" required></div>
                                <div class="col-md-6"><label class="form-label">Confirmar nueva contraseña</label><input type="password" name="reset_password_confirm" class="form-control" minlength="6" required></div>
                                <div class="col-12 d-grid d-md-flex justify-content-md-end"><button class="btn btn-primary px-4" type="submit">Actualizar contraseña</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
$autoTab = '';
if (!empty($view['register_error'])) {
    $autoTab = 'tab-register';
} elseif (!empty($view['reset_error'])) {
    $autoTab = 'tab-reset';
}
?>
<?php if ($autoTab !== ''): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabTrigger = document.querySelector('[data-bs-target="#<?= $autoTab ?>"]');
        if (tabTrigger) {
            bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
        }
    });
</script>
<?php endif; ?>
</body>
</html>
