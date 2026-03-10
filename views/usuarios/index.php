<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Nuevo usuario interno</h5>
    <form method="post" class="row g-2">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control tl-compact-input" placeholder="Nombre" required></div>
        <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" placeholder="Email" required></div>
        <div class="col-md-2"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control tl-compact-input" placeholder="******" required></div>
        <div class="col-md-2"><label class="form-label">Rol</label><select name="rol" class="form-select tl-compact-input"><option value="admin">Administrador</option><option value="supervisor">Supervisor</option><option value="vendedor">Vendedor</option><option value="bodega">Bodega</option></select></div>
        <div class="col-md-2"><label class="form-label">Comisión %</label><input type="number" step="0.01" min="0" max="100" name="porcentaje_comision" class="form-control tl-compact-input" value="0"></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Crear usuario</button></div>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Usuarios registrados</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Comisión</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($data['usuarios'] as $usuario): ?>
                <tr>
                    <td><?= (int) $usuario['id'] ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars($usuario['rol']) ?></td>
                    <td><?= number_format((float) $usuario['porcentaje_comision'], 2, ',', '.') ?>%</td>
                    <td><?= (int) $usuario['estado'] ? 'Activo' : 'Inactivo' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
