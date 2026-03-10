<?php include 'partials/main.php'; ?>
<?php
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/services/AuthService.php';

AuthService::startSession();

if (AuthService::user()) {
    header('Location: index.php');
    exit;
}

$error = null;
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Debes ingresar email y contraseña válidos.';
    } else {
        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByEmail($email);

        if (!$user || (int) $user['estado'] !== 1 || !password_verify($password, $user['password'])) {
            $error = 'Credenciales inválidas.';
        } else {
            AuthService::loginUser($user);
            $_SESSION['user'] = true; // compatibilidad con páginas existentes
            header('Location: index.php');
            exit;
        }
    }
}
?>

<head>
    <?php $title = 'Sign In'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>

<body class="authentication-bg">
<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="card auth-card">
                    <div class="card-body px-3 py-5">
                        <div class="mx-auto mb-4 text-center auth-logo">
                            <a href="index.php" class="logo-dark">
                                <img src="assets/images/logo-sm.png" height="30" class="me-1" alt="logo sm">
                                <img src="assets/images/logo-dark.png" height="24" alt="logo dark">
                            </a>
                            <a href="index.php" class="logo-light">
                                <img src="assets/images/logo-sm.png" height="30" class="me-1" alt="logo sm">
                                <img src="assets/images/logo-light.png" height="24" alt="logo light">
                            </a>
                        </div>

                        <h2 class="fw-bold text-center fs-18">Sign In</h2>
                        <p class="text-muted text-center mt-1 mb-4">Ingresa tu usuario y contraseña para acceder.</p>

                        <div class="px-4">
                            <form method="POST" class="authentication-form" novalidate>
                                <div class="mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="admin@tulista.local" value="<?= htmlspecialchars($email) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password">Password</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>

                                <div class="mb-1 text-center d-grid">
                                    <button class="btn btn-primary" type="submit">Sign In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <p class="mb-0 text-center">New here? <a href="auth-signup.php" class="text-reset fw-bold ms-1">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/vendor-scripts.php' ?>
</body>
</html>
