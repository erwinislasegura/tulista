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
            --tl-bg: #eceef4;
            --tl-panel: #7569e7;
            --tl-panel-dark: #6559d8;
            --tl-card: #ffffff;
            --tl-text: #646b7d;
            --tl-title: #1e2436;
            --tl-line: #e7e9f2;
        }
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--tl-text);
            background: linear-gradient(160deg, #f3f4f9 0%, var(--tl-bg) 55%, #e7e9f1 100%);
        }
        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            justify-content: stretch;
            padding: 0;
        }
        .auth-device {
            width: 100vw;
            min-height: 100vh;
            border-radius: 0;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr;
            background: #e4e7ef;
            box-shadow: none;
        }
        .auth-hero {
            background: linear-gradient(145deg, var(--tl-panel) 0%, var(--tl-panel-dark) 100%);
            color: #fff;
            padding: 24px 22px;
            min-height: 210px;
        }
        .auth-hero h1 {
            font-size: 1.9rem;
            margin: 16px 0 8px;
            font-weight: 700;
            letter-spacing: .2px;
        }
        .auth-hero p {
            margin: 0;
            max-width: 320px;
            font-size: .94rem;
            color: rgba(255, 255, 255, .88);
        }
        .auth-card {
            background: var(--tl-card);
            margin: 0;
            border-top: 1px solid var(--tl-line);
            padding: 18px 16px 22px;
        }
        .nav-soft {
            background: #f6f7fb;
            border: 1px solid var(--tl-line);
            border-radius: 10px;
            padding: 4px;
            gap: 4px;
        }
        .nav-soft .nav-link {
            border: 0;
            border-radius: 8px;
            font-size: .86rem;
            color: #6a7084;
            font-weight: 600;
            padding: 8px 6px;
        }
        .nav-soft .nav-link.active {
            color: var(--tl-title);
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
        }
        .form-label { color: #3f475e; font-size: .86rem; margin-bottom: .3rem; }
        .form-control { min-height: 42px; border-color: var(--tl-line); }
        .form-control:focus { border-color: #b5abff; box-shadow: 0 0 0 .15rem rgba(117, 105, 231, .2); }
        .btn-primary {
            background: linear-gradient(120deg, var(--tl-panel) 0%, var(--tl-panel-dark) 100%);
            border: 0;
            min-height: 42px;
            font-weight: 600;
        }
        .auth-links {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid var(--tl-line);
            font-size: .85rem;
        }
        .auth-links a { color: #5e56c9; text-decoration: none; font-weight: 600; }
        .social-row {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .social-btn {
            border: 1px solid var(--tl-line);
            background: #fff;
            border-radius: 10px;
            min-height: 40px;
            font-weight: 600;
            color: #3d445a;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .social-btn:hover { background: #f8f9ff; }
        .social-icon {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
        }
        .facebook .social-icon { background: #1877F2; }
        .instagram .social-icon { background: linear-gradient(135deg, #f58529, #dd2a7b, #8134af, #515bd4); }

        @media (min-width: 992px) {
            .auth-device {
                grid-template-columns: 1fr 1.15fr;
                min-height: 100vh;
            }
            .auth-hero {
                padding: 48px;
                min-height: auto;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .auth-hero h1 { font-size: 2rem; }
            .auth-hero p { max-width: 390px; font-size: 1rem; }
            .auth-card {
                margin: 34px;
                border-radius: 18px;
                border: 1px solid var(--tl-line);
                box-shadow: 0 16px 34px rgba(40, 49, 85, .14);
                padding: 26px;
                align-self: center;
            }
        }
    </style>
</head>
<body>
<div class="auth-shell">
    <div class="auth-device">
        <section class="auth-hero">
            <div>
                <img src="<?= htmlspecialchars($company['logo_path']) ?>" alt="Logo" style="max-height:54px;width:auto;filter:brightness(0) invert(1);">
                <h1>Portal clientes</h1>
                <p>Inicia sesión, registra tu cuenta o recupera tu contraseña con una experiencia rápida y clara.</p>
            </div>
            <small class="opacity-75">TuLista · acceso seguro</small>
        </section>

        <section class="auth-card">
            <ul class="nav nav-pills nav-fill nav-soft mb-3" role="tablist">
                <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-login" type="button">Ingresar</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-register" type="button">Registro</button></li>
                <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-reset" type="button">Recuperar</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-login">
                    <?php if (!empty($view['login_error'])): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($view['login_error']) ?></div><?php endif; ?>
                    <?php if (!empty($view['register_success'])): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($view['register_success']) ?></div><?php endif; ?>
                    <?php if (!empty($view['reset_success'])): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($view['reset_success']) ?></div><?php endif; ?>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="login_cliente">
                        <div class="col-12"><label class="form-label">RUT</label><input name="rut" class="form-control" placeholder="12.345.678-9" value="<?= htmlspecialchars($old['login_rut'] ?? '') ?>" required></div>
                        <div class="col-12"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" placeholder="••••••••" required></div>
                        <div class="col-12 mt-3 d-grid"><button class="btn btn-primary" type="submit">Ingresar al portal</button></div>
                    </form>
                </div>

                <div class="tab-pane fade" id="tab-register">
                    <?php if (!empty($view['register_error'])): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($view['register_error']) ?></div><?php endif; ?>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="register_cliente">
                        <div class="col-md-6"><label class="form-label">RUT</label><input name="register_rut" class="form-control" value="<?= htmlspecialchars($old['register_rut'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Nombre</label><input name="register_nombre" class="form-control" value="<?= htmlspecialchars($old['register_nombre'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="register_email" class="form-control" value="<?= htmlspecialchars($old['register_email'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Teléfono</label><input name="register_telefono" class="form-control" value="<?= htmlspecialchars($old['register_telefono'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" name="register_password" class="form-control" minlength="6" required></div>
                        <div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input type="password" name="register_password_confirm" class="form-control" minlength="6" required></div>
                        <div class="col-12 mt-3 d-grid"><button class="btn btn-primary" type="submit">Crear cuenta</button></div>
                    </form>
                </div>

                <div class="tab-pane fade" id="tab-reset">
                    <?php if (!empty($view['reset_error'])): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($view['reset_error']) ?></div><?php endif; ?>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="reset_password_cliente">
                        <div class="col-md-6"><label class="form-label">RUT</label><input name="reset_rut" class="form-control" value="<?= htmlspecialchars($old['reset_rut'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="reset_email" class="form-control" value="<?= htmlspecialchars($old['reset_email'] ?? '') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Nueva contraseña</label><input type="password" name="reset_password" class="form-control" minlength="6" required></div>
                        <div class="col-md-6"><label class="form-label">Confirmar nueva contraseña</label><input type="password" name="reset_password_confirm" class="form-control" minlength="6" required></div>
                        <div class="col-12 mt-3 d-grid"><button class="btn btn-primary" type="submit">Actualizar contraseña</button></div>
                    </form>
                </div>
            </div>

            <div class="auth-links">
                <span>¿Eres administrador?</span>
                <a href="auth-login-usuarios.php">Ir al acceso de administración</a>
            </div>

            <div class="social-row">
                <a class="social-btn facebook" href="https://www.facebook.com" target="_blank" rel="noopener noreferrer">
                    <span class="social-icon">f</span>
                    Facebook
                </a>
                <a class="social-btn instagram" href="https://www.instagram.com" target="_blank" rel="noopener noreferrer">
                    <span class="social-icon">IG</span>
                    Instagram
                </a>
            </div>
        </section>
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
