<?php
require_once __DIR__ . '/controllers/UsuarioController.php';
$controller = new UsuarioController();
$error = $controller->login();
require_once __DIR__ . '/services/CompanyConfigService.php';
$company = CompanyConfigService::get();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso de usuarios | <?= htmlspecialchars($company['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/source/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="auth-shell">
    <div class="card auth-box">
        <div class="text-center mb-3"><img src="<?= htmlspecialchars($company['logo_path']) ?>" alt="Logo" style="max-height:54px;width:auto;"></div>
        <h3 class="tl-section-title text-center">Acceso de usuarios</h3>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-12">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="usuario@tulista.cl" required>
            </div>
            <div class="col-12">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="col-12 d-grid"><button class="btn btn-primary" type="submit">Ingresar</button></div>
        </form>
    </div>
</div>
</body>
</html>
