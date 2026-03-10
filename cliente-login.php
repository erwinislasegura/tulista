<?php
require_once __DIR__ . '/controllers/ClienteAuthController.php';
$controller = new ClienteAuthController();
$error = $controller->login();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso clientes | TU LISTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/source/css/dashboard.css" rel="stylesheet">
</head>
<body>
<div class="auth-shell">
    <div class="card auth-box">
        <h3 class="tl-section-title text-center">Acceso de clientes</h3>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-12">
                <label class="form-label">RUT</label>
                <input name="rut" class="form-control" placeholder="12.345.678-9" required>
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
