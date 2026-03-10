<?php
require_once __DIR__ . '/controllers/UsuarioController.php';
$controller = new UsuarioController();
$error = $controller->login();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Login usuarios</title></head><body>
<h3>Acceso de usuarios</h3>
<?php if ($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post"><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Contraseña" required><button>Ingresar</button></form>
</body></html>
