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
        :root {
            --tl-bg: #f6f7fb;
            --tl-card: #ffffff;
            --tl-border: #e9ecf5;
            --tl-title: #222a3f;
            --tl-text: #5b6378;
            --tl-primary: #3a136a;
        }

        body {
            margin: 0;
            background: var(--tl-bg);
            color: var(--tl-text);
        }

        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 12px;
        }

        .login-card {
            width: 100%;
            max-width: 560px;
            background: var(--tl-card);
            border: 1px solid var(--tl-border);
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(32, 41, 74, 0.08);
        }

        .login-head {
            text-align: center;
            padding: 28px 28px 18px;
            border-bottom: 1px solid var(--tl-border);
        }

        .login-head h1 {
            margin: 14px 0 6px;
            font-size: 1.25rem;
            color: var(--tl-title);
        }

        .login-head p {
            margin: 0;
            font-size: .95rem;
        }

        .login-body {
            padding: 22px 28px 28px;
        }

        .nav-minimal {
            border: 1px solid var(--tl-border);
            border-radius: 10px;
            background: #fafbfe;
            padding: 4px;
            gap: 4px;
        }

        .nav-minimal .nav-link {
            border: 0;
            border-radius: 8px;
            color: #616985;
            font-weight: 500;
            padding: 8px 10px;
            font-size: .92rem;
        }

        .nav-minimal .nav-link.active {
            background: #fff;
            color: var(--tl-title);
            box-shadow: 0 2px 8px rgba(34, 42, 63, .08);
        }

        .form-label {
            color: #3b4256;
            font-size: .9rem;
            margin-bottom: .35rem;
        }

        .form-control {
            border-color: var(--tl-border);
            min-height: 42px;
        }

        .form-control:focus {
            border-color: #b9abd6;
            box-shadow: 0 0 0 .2rem rgba(58, 19, 106, .12);
        }

        .btn-primary {
            background: var(--tl-primary);
            border-color: var(--tl-primary);
            min-height: 42px;
            font-weight: 500;
        }

        .login-footer {
            margin-top: 18px;
            text-align: center;
            font-size: .88rem;
        }

        .login-footer a {
            color: var(--tl-primary);
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-head">
            <img src="<?= htmlspecialchars($company['logo_path']) ?>" alt="Logo" style="max-height:54px;width:auto;">
            <h1>Portal de clientes</h1>
            <p>Accede, crea tu cuenta o recupera tu contraseña.</p>
        </div>

        <div class="login-body">
            <ul class="nav nav-pills nav-fill nav-minimal mb-4" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-login" type="button">Ingresar</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-register" type="button">Registro</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-reset" type="button">Recuperar clave</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-login">
                    <?php if (!empty($view['login_error'])): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($view['login_error']) ?></div><?php endif; ?>
                    <?php if (!empty($view['register_success'])): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($view['register_success']) ?></div><?php endif; ?>
                    <?php if (!empty($view['reset_success'])): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($view['reset_success']) ?></div><?php endif; ?>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="action" value="login_cliente">
                        <div class="col-12">
                            <label class="form-label">RUT</label>
                            <input name="rut" class="form-control" placeholder="12.345.678-9" value="<?= htmlspecialchars($old['login_rut'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-12 d-grid"><button class="btn btn-primary" type="submit">Ingresar al portal</button></div>
                    </form>
                </div>

                <div class="tab-pane fade" id="tab-register">
                    <?php if (!empty($view['register_error'])): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($view['register_error']) ?></div><?php endif; ?>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="action" value="register_cliente">
                        <div class="col-md-6"><label class="form-label">RUT</label><input name="register_rut" class="form-control" value="<?= htmlspecialchars($old['register_rut'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Nombre</label><input name="register_nombre" class="form-control" value="<?= htmlspecialchars($old['register_nombre'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="register_email" class="form-control" value="<?= htmlspecialchars($old['register_email'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Teléfono</label><input name="register_telefono" class="form-control" value="<?= htmlspecialchars($old['register_telefono'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" name="register_password" class="form-control" minlength="6" required></div>
                        <div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input type="password" name="register_password_confirm" class="form-control" minlength="6" required></div>
                        <div class="col-12 d-grid"><button class="btn btn-primary" type="submit">Crear cuenta</button></div>
                    </form>
                </div>

                <div class="tab-pane fade" id="tab-reset">
                    <?php if (!empty($view['reset_error'])): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($view['reset_error']) ?></div><?php endif; ?>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="action" value="reset_password_cliente">
                        <div class="col-md-6"><label class="form-label">RUT</label><input name="reset_rut" class="form-control" value="<?= htmlspecialchars($old['reset_rut'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="reset_email" class="form-control" value="<?= htmlspecialchars($old['reset_email'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Nueva contraseña</label><input type="password" name="reset_password" class="form-control" minlength="6" required></div>
                        <div class="col-md-6"><label class="form-label">Confirmar nueva contraseña</label><input type="password" name="reset_password_confirm" class="form-control" minlength="6" required></div>
                        <div class="col-12 d-grid"><button class="btn btn-primary" type="submit">Actualizar contraseña</button></div>
                    </form>
                </div>
            </div>

            <div class="login-footer">
                ¿Eres administrador? <a href="auth-login-usuarios.php">Ir al acceso de administración</a>
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
