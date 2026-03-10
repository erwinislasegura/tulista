<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Nuevo usuario interno</h5>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" placeholder="Nombre" required></div>
        <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="Email" required></div>
        <div class="col-md-3"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" placeholder="Contraseña" required></div>
        <div class="col-md-3"><label class="form-label">Rol</label><select name="rol" class="form-select"><option value="vendedor">Vendedor</option><option value="admin">Admin</option></select></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Crear usuario</button></div>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Usuarios registrados</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($data['usuarios'] as $usuario): ?>
                <tr>
                    <td><?= (int) $usuario['id'] ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars($usuario['rol']) ?></td>
                    <td><?= (int) $usuario['estado'] ? 'Activo' : 'Inactivo' ?></td>
                    <td>
                        <form method="post" style="display:inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
