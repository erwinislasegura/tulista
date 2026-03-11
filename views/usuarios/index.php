<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Nuevo usuario interno</h5>
    <form method="post" class="row g-2 tl-minimal-form">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control tl-compact-input" required></div>
        <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" required></div>
        <div class="col-md-2"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input"></div>
        <div class="col-md-2"><label class="form-label">Cargo</label><input name="cargo" class="form-control tl-compact-input"></div>
        <div class="col-md-2"><label class="form-label">Rol</label><select name="rol" class="form-select tl-compact-input"><?php foreach ($data['roles'] as $rol): ?><option value="<?= htmlspecialchars($rol['codigo']) ?>"><?= htmlspecialchars($rol['nombre']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><label class="form-label">Comisión %</label><input type="number" step="0.01" min="0" max="100" name="porcentaje_comision" class="form-control tl-compact-input" value="0"></div>
        <div class="col-md-4"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input"></div>
        <div class="col-md-4"><label class="form-label">Notas</label><input name="notas" class="form-control tl-compact-input"></div>
        <div class="col-md-2"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control tl-compact-input" required></div>
        <div class="col-md-2 form-check mt-4 ms-2"><input type="checkbox" name="estado" class="form-check-input" checked><label class="form-check-label">Activo</label></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Crear usuario</button></div>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Usuarios registrados</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Nombre</th><th>Contacto</th><th>Cargo</th><th>Rol</th><th>Comisión</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($data['usuarios'] as $usuario): ?>
                <tr>
                    <td><?= (int) $usuario['id'] ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?><div class="small text-muted"><?= htmlspecialchars($usuario['email']) ?></div></td>
                    <td><?= htmlspecialchars($usuario['telefono'] ?: '-') ?><div class="small text-muted"><?= htmlspecialchars($usuario['direccion'] ?: '-') ?></div></td>
                    <td><?= htmlspecialchars($usuario['cargo'] ?: '-') ?></td>
                    <td class="text-capitalize"><?= htmlspecialchars($usuario['rol']) ?></td>
                    <td><?= number_format((float) $usuario['porcentaje_comision'], 2, ',', '.') ?>%</td>
                    <td><?= (int) $usuario['estado'] ? 'Activo' : 'Inactivo' ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="static" type="button">Acciones</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editUsuario<?= (int) $usuario['id'] ?>">Editar</button></li>
                                <li><form method="post" onsubmit="return confirm('¿Eliminar usuario?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>"><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="editUsuario<?= (int) $usuario['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body">
                        <h6 class="mb-3">Editar usuario #<?= (int) $usuario['id'] ?></h6>
                        <form method="post" class="row g-2 tl-minimal-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <div class="col-md-4"><label class="form-label">Nombre</label><input name="nombre" class="form-control tl-compact-input" value="<?= htmlspecialchars($usuario['nombre']) ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" value="<?= htmlspecialchars($usuario['email']) ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"></div>
                            <div class="col-md-4"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>"></div>
                            <div class="col-md-3"><label class="form-label">Cargo</label><input name="cargo" class="form-control tl-compact-input" value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>"></div>
                            <div class="col-md-3"><label class="form-label">Rol</label><select name="rol" class="form-select tl-compact-input"><?php foreach ($data['roles'] as $rol): ?><option value="<?= htmlspecialchars($rol['codigo']) ?>" <?= $rol['codigo'] === $usuario['rol'] ? 'selected' : '' ?>><?= htmlspecialchars($rol['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-2"><label class="form-label">Comisión %</label><input type="number" step="0.01" min="0" max="100" name="porcentaje_comision" class="form-control tl-compact-input" value="<?= htmlspecialchars((string) $usuario['porcentaje_comision']) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Nueva clave</label><input type="password" name="password" class="form-control tl-compact-input" placeholder="Opcional"></div>
                            <div class="col-md-9"><label class="form-label">Notas</label><input name="notas" class="form-control tl-compact-input" value="<?= htmlspecialchars($usuario['notas'] ?? '') ?>"></div>
                            <div class="col-md-3 form-check mt-4 ms-2"><input type="checkbox" name="estado" class="form-check-input" <?= (int) $usuario['estado'] ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
                        </form>
                    </div></div></div>
                </div>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
