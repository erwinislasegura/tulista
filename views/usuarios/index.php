<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>
<form method="post" class="row g-2 mb-3">
    <input type="hidden" name="action" value="create">
    <div class="col"><input name="nombre" class="form-control" placeholder="Nombre" required></div>
    <div class="col"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
    <div class="col"><input type="password" name="password" class="form-control" placeholder="Contraseña" required></div>
    <div class="col"><select name="rol" class="form-select"><option value="vendedor">Vendedor</option><option value="admin">Admin</option></select></div>
    <div class="col"><button class="btn btn-primary">Crear usuario</button></div>
</form>
<table class="table table-sm">
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
                    <button class="btn btn-sm btn-danger">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
