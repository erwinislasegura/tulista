<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Selecciona rol</label>
                <select class="form-select" name="rol" onchange="this.form.submit()">
                    <?php foreach ($data['roles'] as $role): ?>
                        <option value="<?= htmlspecialchars($role['codigo']) ?>" <?= $data['selected_role'] === $role['codigo'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['nombre']) ?> (<?= htmlspecialchars($role['codigo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php $assigned = $data['permissions_by_role'][$data['selected_role']] ?? []; ?>
<div class="card">
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="rol" value="<?= htmlspecialchars($data['selected_role']) ?>">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th>Menú</th><th>Ver</th><th>Editar</th><th>Eliminar</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['catalog'] as $menuKey => $menu): ?>
                            <tr>
                                <td><?= htmlspecialchars($menu['label']) ?></td>
                                <?php foreach (['view', 'edit', 'delete'] as $actionKey): ?>
                                    <?php $perm = AuthorizationService::permissionForMenuAction($menuKey, $actionKey); ?>
                                    <td>
                                        <input type="checkbox" class="form-check-input" name="permissions[<?= htmlspecialchars($menuKey) ?>][<?= htmlspecialchars($actionKey) ?>]" value="1" <?= in_array($perm, $assigned, true) || in_array('*', $assigned, true) ? 'checked' : '' ?>>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary" type="submit">Guardar permisos</button>
        </form>
    </div>
</div>
