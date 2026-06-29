<?php
$proveedores = $data['proveedores'] ?? [];
$canManage = (bool) ($data['can_manage'] ?? false);
$totalProveedores = count($proveedores);
$activos = count(array_filter($proveedores, static fn (array $proveedor): bool => (int) ($proveedor['estado'] ?? 0) === 1));
$inactivos = max(0, $totalProveedores - $activos);
$sinContacto = count(array_filter($proveedores, static fn (array $proveedor): bool => trim((string) ($proveedor['nombre_contacto'] ?? '')) === ''));
?>

<style>
.proveedores-panel {
    --tl-primary: #2563eb;
    --tl-primary-soft: #eff6ff;
    --tl-border: #e5e7eb;
    --tl-text: #0f172a;
    --tl-muted: #64748b;
}
.proveedores-panel .tl-page-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 58%, #38bdf8 100%);
    border-radius: 18px;
    color: #fff;
    padding: 20px 22px;
    box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
}
.proveedores-panel .tl-page-hero h4 { color: #fff; letter-spacing: -.02em; }
.proveedores-panel .tl-page-hero p { color: rgba(255,255,255,.78); max-width: 720px; }
.proveedores-panel .tl-stat-card {
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 14px;
    padding: 12px 14px;
    backdrop-filter: blur(8px);
    min-width: 132px;
}
.proveedores-panel .tl-stat-card span { color: rgba(255,255,255,.72); display: block; font-size: 12px; }
.proveedores-panel .tl-stat-card strong { display: block; font-size: 24px; line-height: 1; margin-top: 5px; }
.proveedores-panel .tl-surface {
    border: 1px solid var(--tl-border);
    border-radius: 16px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
    overflow: hidden;
}
.proveedores-panel .tl-surface-header {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid var(--tl-border);
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    padding: 16px 18px;
}
.proveedores-panel .tl-surface-body { padding: 18px; }
.proveedores-panel .tl-section-title { color: var(--tl-text); font-weight: 700; letter-spacing: -.01em; }
.proveedores-panel .tl-subtitle { color: var(--tl-muted); font-size: 13px; margin: 3px 0 0; }
.proveedores-panel .tl-minimal-form .form-label { color: #475569; font-size: 12px; font-weight: 700; margin-bottom: 5px; }
.proveedores-panel .tl-compact-input,
.proveedores-panel .form-select,
.proveedores-panel .form-control {
    border-color: var(--tl-border);
    border-radius: 10px;
    min-height: 38px;
}
.proveedores-panel .tl-compact-input:focus,
.proveedores-panel .form-control:focus {
    border-color: var(--tl-primary);
    box-shadow: 0 0 0 .18rem rgba(37, 99, 235, .12);
}
.proveedores-panel .tl-toolbar-search { max-width: 340px; min-width: 260px; }
.proveedores-panel .tl-table { margin: 0; }
.proveedores-panel .tl-table thead th {
    background: #f8fafc;
    border-bottom: 1px solid var(--tl-border);
    color: #475569;
    font-size: 11px;
    letter-spacing: .06em;
    padding: 11px 16px;
    text-transform: uppercase;
    white-space: nowrap;
}
.proveedores-panel .tl-table tbody td {
    border-color: #eef2f7;
    padding: 14px 16px;
    vertical-align: middle;
}
.proveedores-panel .tl-provider-name { color: var(--tl-text); font-weight: 700; }
.proveedores-panel .tl-provider-meta,
.proveedores-panel .tl-contact-meta { color: var(--tl-muted); font-size: 12px; margin-top: 4px; }
.proveedores-panel .tl-status {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700;
    gap: 6px;
    padding: 6px 10px;
}
.proveedores-panel .tl-status::before { border-radius: 999px; content: ''; height: 7px; width: 7px; }
.proveedores-panel .tl-status.is-active { background: #ecfdf5; color: #047857; }
.proveedores-panel .tl-status.is-active::before { background: #10b981; }
.proveedores-panel .tl-status.is-inactive { background: #f1f5f9; color: #64748b; }
.proveedores-panel .tl-status.is-inactive::before { background: #94a3b8; }
.proveedores-panel .tl-pay-badge {
    background: var(--tl-primary-soft);
    border: 1px solid #dbeafe;
    border-radius: 999px;
    color: #1d4ed8;
    display: inline-flex;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 9px;
}
.proveedores-panel .tl-empty-state {
    align-items: center;
    color: var(--tl-muted);
    display: flex;
    flex-direction: column;
    gap: 8px;
    justify-content: center;
    min-height: 160px;
    text-align: center;
}
.proveedores-panel .tl-empty-state i { color: #94a3b8; font-size: 38px; }
.proveedores-panel .modal-content { border: 0; border-radius: 18px; box-shadow: 0 22px 70px rgba(15, 23, 42, .22); }
.proveedores-panel .modal-header { border-bottom-color: var(--tl-border); padding: 16px 18px; }
.proveedores-panel .modal-body { padding: 18px; }
@media (max-width: 767.98px) {
    .proveedores-panel .tl-page-hero { padding: 18px; }
    .proveedores-panel .tl-toolbar-search { max-width: 100%; min-width: 100%; }
    .proveedores-panel .tl-surface-body { padding: 14px; }
    .proveedores-panel .tl-table thead { display: none; }
    .proveedores-panel .tl-table tbody tr { border-bottom: 1px solid var(--tl-border); display: grid; gap: 8px; padding: 12px 4px; }
    .proveedores-panel .tl-table tbody td { border: 0; display: block; padding: 0 10px; }
}
</style>

<div class="proveedores-panel">
    <?php foreach (($data['flash'] ?? []) as $alert): ?>
        <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> border-0 shadow-sm py-2 mb-3"><?= htmlspecialchars($alert['message']) ?></div>
    <?php endforeach; ?>

    <div class="tl-page-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">Administración</span>
                <h4 class="mb-1">Proveedores</h4>
                <p class="mb-0">Gestiona datos comerciales, contacto y condiciones de pago desde una vista más compacta y clara.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="tl-stat-card"><span>Total</span><strong><?= $totalProveedores ?></strong></div>
                <div class="tl-stat-card"><span>Activos</span><strong><?= $activos ?></strong></div>
                <div class="tl-stat-card"><span>Inactivos</span><strong><?= $inactivos ?></strong></div>
                <div class="tl-stat-card"><span>Sin contacto</span><strong><?= $sinContacto ?></strong></div>
            </div>
        </div>
    </div>

    <?php if ($canManage): ?>
        <div class="card tl-surface mb-3">
            <div class="tl-surface-header">
                <div>
                    <h5 class="tl-section-title mb-0">Nuevo proveedor</h5>
                    <p class="tl-subtitle">Completa solo lo necesario; puedes editar los detalles después.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary">Registro rápido</span>
            </div>
            <div class="tl-surface-body">
                <form method="post" class="row g-2 tl-minimal-form">
                    <input type="hidden" name="action" value="create">
                    <div class="col-md-2"><label class="form-label">RUT</label><input name="rut" class="form-control tl-compact-input" required autocomplete="off" placeholder="76.123.456-7"></div>
                    <div class="col-md-4"><label class="form-label">Razón social</label><input name="razon_social" class="form-control tl-compact-input" required placeholder="Nombre legal del proveedor"></div>
                    <div class="col-md-3"><label class="form-label">Contacto</label><input name="nombre_contacto" class="form-control tl-compact-input" autocomplete="name" placeholder="Nombre contacto"></div>
                    <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" autocomplete="email" placeholder="correo@proveedor.cl"></div>
                    <div class="col-md-3"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input" inputmode="tel" autocomplete="tel" placeholder="+56 9..."></div>
                    <div class="col-md-3"><label class="form-label">Comuna</label><input name="comuna" class="form-control tl-compact-input" placeholder="Comuna"></div>
                    <div class="col-md-4"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input" autocomplete="street-address" placeholder="Dirección comercial"></div>
                    <div class="col-md-2"><label class="form-label">Pago (días)</label><input type="number" min="0" max="365" name="plazo_pago_dias" value="30" class="form-control tl-compact-input"></div>
                    <div class="col-md-10"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control tl-compact-input" placeholder="Condiciones comerciales, horario de recepción, etc."></div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="estado" id="proveedorActivo" checked><label class="form-check-label" for="proveedorActivo">Activo</label></div>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <button class="btn btn-primary px-4" type="submit"><i class="bx bx-save me-1"></i>Guardar proveedor</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="card tl-surface">
        <div class="tl-surface-header">
            <div>
                <h5 class="tl-section-title mb-0">Directorio de proveedores</h5>
                <p class="tl-subtitle">Busca, revisa estado y administra información comercial.</p>
            </div>
            <div class="input-group input-group-sm tl-toolbar-search">
                <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                <input id="proveedoresSearch" type="search" class="form-control" placeholder="Buscar RUT, razón social o contacto">
            </div>
        </div>

        <?php if (!empty($proveedores)): ?>
            <div class="table-responsive">
                <table class="table align-middle tl-table" id="proveedoresTable">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Contacto</th>
                            <th>Condiciones</th>
                            <th>Estado</th>
                            <?php if ($canManage): ?><th class="text-end">Acciones</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <tr>
                            <td>
                                <div class="tl-provider-name"><?= htmlspecialchars($proveedor['razon_social']) ?></div>
                                <div class="tl-provider-meta"><?= htmlspecialchars($proveedor['rut']) ?> · <?= htmlspecialchars($proveedor['comuna'] ?: 'Sin comuna') ?></div>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($proveedor['nombre_contacto'] ?: 'Sin contacto') ?></div>
                                <div class="tl-contact-meta"><?= htmlspecialchars($proveedor['email'] ?: 'Sin email') ?> · <?= htmlspecialchars($proveedor['telefono'] ?: 'Sin teléfono') ?></div>
                            </td>
                            <td>
                                <span class="tl-pay-badge">Pago <?= (int) $proveedor['plazo_pago_dias'] ?> días</span>
                                <div class="tl-contact-meta mt-1"><?= htmlspecialchars($proveedor['observaciones'] ?: 'Sin observaciones') ?></div>
                            </td>
                            <td>
                                <?php if ((int) $proveedor['estado']): ?>
                                    <span class="tl-status is-active">Activo</span>
                                <?php else: ?>
                                    <span class="tl-status is-inactive">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($canManage): ?>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown" type="button">Acciones</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editProveedor<?= (int) $proveedor['id'] ?>"><i class="bx bx-edit-alt me-2"></i>Editar</button></li>
                                            <li><form method="post" onsubmit="return confirm('¿Eliminar proveedor?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $proveedor['id'] ?>"><button class="dropdown-item text-danger" type="submit"><i class="bx bx-trash me-2"></i>Eliminar</button></form></li>
                                        </ul>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="tl-empty-state">
                <i class="bx bx-package"></i>
                <strong>No hay proveedores registrados</strong>
                <span>Cuando agregues proveedores, aparecerán en este directorio.</span>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($canManage): ?>
        <?php foreach ($proveedores as $proveedor): ?>
            <div class="modal fade" id="editProveedor<?= (int) $proveedor['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h6 class="modal-title mb-0">Editar proveedor #<?= (int) $proveedor['id'] ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($proveedor['razon_social']) ?></small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" class="row g-2 tl-minimal-form">
                                <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $proveedor['id'] ?>">
                                <div class="col-md-3"><label class="form-label">RUT</label><input name="rut" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['rut']) ?>" required></div>
                                <div class="col-md-5"><label class="form-label">Razón social</label><input name="razon_social" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['razon_social']) ?>" required></div>
                                <div class="col-md-4"><label class="form-label">Contacto</label><input name="nombre_contacto" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['nombre_contacto'] ?? '') ?>"></div>
                                <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['email'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Comuna</label><input name="comuna" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['comuna'] ?? '') ?>"></div>
                                <div class="col-md-6"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['direccion'] ?? '') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Pago (días)</label><input type="number" min="0" max="365" name="plazo_pago_dias" class="form-control tl-compact-input" value="<?= (int) $proveedor['plazo_pago_dias'] ?>"></div>
                                <div class="col-md-12"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control tl-compact-input" value="<?= htmlspecialchars($proveedor['observaciones'] ?? '') ?>"></div>
                                <div class="col-md-2 form-check mt-4 ms-2"><input type="checkbox" name="estado" id="estadoProveedor<?= (int) $proveedor['id'] ?>" class="form-check-input" <?= (int) $proveedor['estado'] ? 'checked' : '' ?>><label class="form-check-label" for="estadoProveedor<?= (int) $proveedor['id'] ?>">Activo</label></div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary px-4" type="submit">Guardar cambios</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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
