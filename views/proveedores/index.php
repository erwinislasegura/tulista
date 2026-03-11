<?php foreach (($data['flash'] ?? []) as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<?php $canManage = (bool) ($data['can_manage'] ?? false); ?>

<?php if ($canManage): ?>
<div class="card mb-4">
    <h5 class="tl-section-title">Nuevo proveedor</h5>
    <form method="post" class="row g-3 tl-minimal-form">
        <input type="hidden" name="action" value="create">
        <div class="col-md-2"><label class="form-label">RUT</label><input name="rut" class="form-control tl-compact-input" required autocomplete="off" placeholder="76.123.456-7"></div>
        <div class="col-md-4"><label class="form-label">Razón social</label><input name="razon_social" class="form-control tl-compact-input" required></div>
        <div class="col-md-3"><label class="form-label">Contacto</label><input name="nombre_contacto" class="form-control tl-compact-input" autocomplete="name"></div>
        <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" autocomplete="email"></div>
        <div class="col-md-3"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input" inputmode="tel" autocomplete="tel"></div>
        <div class="col-md-3"><label class="form-label">Comuna</label><input name="comuna" class="form-control tl-compact-input"></div>
        <div class="col-md-4"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input" autocomplete="street-address"></div>
        <div class="col-md-2"><label class="form-label">Plazo pago (días)</label><input type="number" min="0" max="365" name="plazo_pago_dias" value="30" class="form-control tl-compact-input"></div>
        <div class="col-md-12"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control tl-compact-input" placeholder="Condiciones comerciales, horario de recepción, etc."></div>
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="estado" checked><label class="form-check-label">Activo</label></div>
            <button class="btn btn-primary" type="submit">Guardar proveedor</button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="tl-section-title mb-0">Proveedores</h5>
        <input id="proveedoresSearch" type="search" class="form-control form-control-sm" style="max-width: 280px;" placeholder="Buscar por RUT, razón social o contacto">
    </div>

    <div class="table-responsive">
        <table class="table align-middle" id="proveedoresTable">
            <thead><tr><th>Proveedor</th><th>Contacto</th><th>Condiciones</th><th>Estado</th><?php if ($canManage): ?><th>Acciones</th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach (($data['proveedores'] ?? []) as $proveedor): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($proveedor['razon_social']) ?></strong><div class="small text-muted"><?= htmlspecialchars($proveedor['rut']) ?> · <?= htmlspecialchars($proveedor['comuna'] ?: '-') ?></div></td>
                    <td><?= htmlspecialchars($proveedor['nombre_contacto'] ?: 'Sin contacto') ?><div class="small text-muted"><?= htmlspecialchars($proveedor['email'] ?: '-') ?> · <?= htmlspecialchars($proveedor['telefono'] ?: '-') ?></div></td>
                    <td><span class="badge bg-light text-dark">Pago: <?= (int) $proveedor['plazo_pago_dias'] ?> días</span><div class="small text-muted mt-1"><?= htmlspecialchars($proveedor['observaciones'] ?: 'Sin observaciones') ?></div></td>
                    <td><?= (int) $proveedor['estado'] ? 'Activo' : 'Inactivo' ?></td>
                    <?php if ($canManage): ?>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" type="button">Acciones</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editProveedor<?= (int) $proveedor['id'] ?>">Editar</button></li>
                                <li><form method="post" onsubmit="return confirm('¿Eliminar proveedor?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $proveedor['id'] ?>"><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li>
                            </ul>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($data['proveedores'])): ?>
        <p class="text-muted mb-0">No hay proveedores registrados.</p>
    <?php endif; ?>
</div>

<?php if ($canManage): ?>
    <?php foreach (($data['proveedores'] ?? []) as $proveedor): ?>
        <div class="modal fade" id="editProveedor<?= (int) $proveedor['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body">
                <h6 class="mb-3">Editar proveedor #<?= (int) $proveedor['id'] ?></h6>
                <form method="post" class="row g-2 tl-minimal-form">
                    <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $proveedor['id'] ?>">
                    <div class="col-md-3"><label class="form-label">RUT</label><input name="rut" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['rut']) ?>" required></div>
                    <div class="col-md-5"><label class="form-label">Razón social</label><input name="razon_social" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['razon_social']) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Contacto</label><input name="nombre_contacto" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['nombre_contacto'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['email'] ?? '') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Comuna</label><input name="comuna" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['comuna'] ?? '') ?>"></div>
                    <div class="col-md-6"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['direccion'] ?? '') ?>"></div>
                    <div class="col-md-3"><label class="form-label">Plazo pago (días)</label><input type="number" min="0" max="365" name="plazo_pago_dias" class="form-control tl-compact-input" value="<?= (int) $proveedor['plazo_pago_dias'] ?>"></div>
                    <div class="col-md-12"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['observaciones'] ?? '') ?>"></div>
                    <div class="col-md-2 form-check mt-4 ms-2"><input type="checkbox" name="estado" class="form-check-input" <?= (int) $proveedor['estado'] ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div>
                </form>
            </div></div></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
(function () {
  const search = document.getElementById('proveedoresSearch');
  const rows = Array.from(document.querySelectorAll('#proveedoresTable tbody tr'));
  search?.addEventListener('input', function () {
    const term = this.value.toLowerCase().trim();
    rows.forEach((row) => {
      row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
  });
})();
</script>
