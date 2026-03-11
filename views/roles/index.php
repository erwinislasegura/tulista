<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Crear rol</h5>
    <form method="post" class="row g-2 tl-minimal-form">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><label class="form-label">Código</label><input name="codigo" class="form-control tl-compact-input" required placeholder="ej: soporte"></div>
        <div class="col-md-5"><label class="form-label">Nombre</label><input name="nombre" class="form-control tl-compact-input" required placeholder="Nombre visible"></div>
        <div class="col-md-2 form-check mt-4 ms-2"><input type="checkbox" name="estado" class="form-check-input" checked><label class="form-check-label">Activo</label></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Crear rol</button></div>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Roles existentes</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Código</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($data['roles'] as $rol): ?>
                    <tr>
                        <td><?= (int) $rol['id'] ?></td>
                        <td><code><?= htmlspecialchars($rol['codigo']) ?></code></td>
                        <td><?= htmlspecialchars($rol['nombre']) ?></td>
                        <td><?= (int) $rol['estado'] ? 'Activo' : 'Inactivo' ?></td>
                        <td>
                            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#editRole<?= (int) $rol['id'] ?>">Editar</button>
                        </td>
                    </tr>

                    <div class="modal fade" id="editRole<?= (int) $rol['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body">
                            <h6 class="mb-3">Editar rol <?= htmlspecialchars($rol['codigo']) ?></h6>
                            <form method="post" class="row g-2 tl-minimal-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= (int) $rol['id'] ?>">
                                <div class="col-12"><label class="form-label">Nombre</label><input name="nombre" class="form-control tl-compact-input" value="<?= htmlspecialchars($rol['nombre']) ?>" required></div>
                                <div class="col-12 form-check mt-2 ms-2"><input type="checkbox" name="estado" class="form-check-input" <?= (int) $rol['estado'] ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
                            </form>
                        </div></div></div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
