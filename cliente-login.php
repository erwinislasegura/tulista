<?php
require_once __DIR__ . '/controllers/ClienteAuthController.php';
$controller = new ClienteAuthController();
$error = $controller->login();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Login cliente</title></head><body>
<h3>Acceso clientes</h3>
<?php if ($error): ?><p style="color:red"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post"><input name="rut" placeholder="RUT" required><input type="password" name="password" placeholder="Contraseña" required><button>Ingresar</button></form>
</body></html>
