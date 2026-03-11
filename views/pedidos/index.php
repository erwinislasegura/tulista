<?php foreach ($data['flash'] as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Crear pedido</h5>
    <form method="post" class="row g-2">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3">
            <label class="form-label">Cliente</label>
            <select id="pedido_cliente" name="cliente_id" class="form-select tl-compact-input" required>
                <option value="">Selecciona cliente...</option>
                <?php foreach ($data['clientes'] as $cliente): ?>
                    <option value="<?= (int) $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?> (<?= htmlspecialchars($cliente['rut']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Cotización (opcional)</label>
            <select id="pedido_cotizacion" name="cotizacion_id" class="form-select tl-compact-input">
                <option value="">Sin cotización</option>
                <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
                    <option value="<?= (int) $cotizacion['id'] ?>">#<?= (int) $cotizacion['id'] ?> · <?= htmlspecialchars($cotizacion['cliente_nombre']) ?> · $<?= number_format((float) $cotizacion['total'], 0, ',', '.') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Vendedor</label>
            <select name="usuario_id" class="form-select tl-compact-input">
                <option value="">Sin asignar</option>
                <?php foreach ($data['vendedores'] as $vendedor): ?>
                    <option value="<?= (int) $vendedor['id'] ?>"><?= htmlspecialchars($vendedor['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Total</label><input type="number" step="0.01" min="0" name="total" class="form-control tl-compact-input" required></div>
        <div class="col-md-2"><label class="form-label">Estado</label><select name="estado" class="form-select tl-compact-input"><?php foreach (($data['estados_operacion'] ?? []) as $estado): ?><option value="<?= $estado ?>"><?= ucfirst($estado) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label">Fecha</label><input type="datetime-local" name="fecha" class="form-control tl-compact-input"></div>

        <div class="col-md-3"><label class="form-label">Contacto</label><input id="pedido_contacto" name="contacto_nombre" class="form-control tl-compact-input"></div>
        <div class="col-md-3"><label class="form-label">Email contacto</label><input id="pedido_email" type="email" name="contacto_email" class="form-control tl-compact-input"></div>
        <div class="col-md-2"><label class="form-label">Teléfono</label><input id="pedido_telefono" name="contacto_telefono" class="form-control tl-compact-input"></div>
        <div class="col-md-4"><label class="form-label">Dirección entrega</label><input id="pedido_direccion" name="direccion_entrega" class="form-control tl-compact-input"></div>
        <div class="col-md-9"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control tl-compact-input"></div>

        <div class="col-12"><button class="btn btn-primary" type="submit">Guardar pedido</button></div>
    </form>
</div>


<div class="card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="tl-section-title mb-0">Cotizaciones aceptadas para bodega</h5>
        <span class="badge bg-light text-dark">Total: <?= count($data['cotizaciones_aprobadas'] ?? []) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Cotización</th><th>Cliente</th><th>Total</th><th>Estado cotización</th><th>Pedido asociado</th><th>Estado pedido</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php foreach (($data['cotizaciones_aprobadas'] ?? []) as $cot): ?>
                <tr>
                    <td>#<?= (int) $cot['id'] ?></td>
                    <td><?= htmlspecialchars($cot['cliente_nombre']) ?></td>
                    <td>$<?= number_format((float) $cot['total'], 0, ',', '.') ?></td>
                    <td><span class="badge bg-success-subtle text-success text-capitalize"><?= htmlspecialchars($cot['estado']) ?></span></td>
                    <td><?= !empty($cot['pedido_id']) ? ('#' . (int) $cot['pedido_id']) : 'Sin generar' ?></td>
                    <td><?= !empty($cot['pedido_estado']) ? htmlspecialchars($cot['pedido_estado']) : '-' ?></td>
                    <td><?= htmlspecialchars($cot['pedido_fecha'] ?: $cot['fecha']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['cotizaciones_aprobadas'])): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">No hay cotizaciones aprobadas pendientes para bodega.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="tl-section-title mb-0">Pedidos en operación</h5>
        <span class="badge bg-light text-dark">Total: <?= count($data['pedidos']) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>#</th><th>Cliente</th><th>Vendedor</th><th>Cotización</th><th>Total</th><th>Fecha</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($data['pedidos'] as $pedido): ?>
                <tr>
                    <td><?= (int) $pedido['id'] ?></td>
                    <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($pedido['vendedor'] ?? 'Sin asignar') ?></td>
                    <td><?= $pedido['cotizacion_id'] ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                    <td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($pedido['fecha']) ?></td>
                    <td><span class="badge bg-light text-dark text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></span></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="static" type="button">Acciones</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editPedido<?= (int) $pedido['id'] ?>">Editar</button></li>
                                <li>
                                    <form method="post" class="px-2 py-1 tl-minimal-form">
                                        <input type="hidden" name="action" value="estado">
                                        <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                                        <label class="form-label small mb-1">Editar estado</label>
                                        <select name="estado" class="form-select form-select-sm tl-compact-input mb-2">
                                            <?php foreach (($data['estados_operacion'] ?? []) as $estado): ?>
                                                <option value="<?= $estado ?>" <?= $estado === $pedido['estado'] ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-primary btn-sm w-100" type="submit">Guardar</button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form method="post" onsubmit="return confirm('¿Eliminar pedido?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>"><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="editPedido<?= (int) $pedido['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body">
                        <h6 class="mb-3">Editar pedido #<?= (int) $pedido['id'] ?></h6>
                        <form method="post" class="row g-2">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                            <div class="col-md-4"><label class="form-label">Cliente</label><select id="pedido_cliente" name="cliente_id" class="form-select tl-compact-input" required><?php foreach ($data['clientes'] as $cliente): ?><option value="<?= (int) $cliente['id'] ?>" <?= (int) $cliente['id'] === (int) $pedido['cliente_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cliente['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-3"><label class="form-label">Cotización</label><select id="pedido_cotizacion" name="cotizacion_id" class="form-select tl-compact-input"><option value="">Sin cotización</option><?php foreach ($data['cotizaciones'] as $cotizacion): ?><option value="<?= (int) $cotizacion['id'] ?>" <?= (int) $cotizacion['id'] === (int) ($pedido['cotizacion_id'] ?? 0) ? 'selected' : '' ?>>#<?= (int) $cotizacion['id'] ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-3"><label class="form-label">Vendedor</label><select name="usuario_id" class="form-select tl-compact-input"><option value="">Sin asignar</option><?php foreach ($data['vendedores'] as $vendedor): ?><option value="<?= (int) $vendedor['id'] ?>" <?= (int) $vendedor['id'] === (int) ($pedido['usuario_id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($vendedor['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-2"><label class="form-label">Estado</label><select name="estado" class="form-select tl-compact-input"><?php foreach (($data['estados_operacion'] ?? []) as $estado): ?><option value="<?= $estado ?>" <?= $estado === $pedido['estado'] ? 'selected' : '' ?>><?= ucfirst($estado) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-3"><label class="form-label">Total</label><input type="number" step="0.01" min="0" name="total" class="form-control tl-compact-input" value="<?= htmlspecialchars((string) $pedido['total']) ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Fecha</label><input type="datetime-local" name="fecha" class="form-control tl-compact-input" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($pedido['fecha']))) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Contacto</label><input name="contacto_nombre" class="form-control tl-compact-input" value="<?= htmlspecialchars($pedido['contacto_nombre'] ?? '') ?>"></div>
                            <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="contacto_email" class="form-control tl-compact-input" value="<?= htmlspecialchars($pedido['contacto_email'] ?? '') ?>"></div>
                            <div class="col-md-2"><label class="form-label">Teléfono</label><input name="contacto_telefono" class="form-control tl-compact-input" value="<?= htmlspecialchars($pedido['contacto_telefono'] ?? '') ?>"></div>
                            <div class="col-md-4"><label class="form-label">Dirección entrega</label><input name="direccion_entrega" class="form-control tl-compact-input" value="<?= htmlspecialchars($pedido['direccion_entrega'] ?? '') ?>"></div>
                            <div class="col-12"><label class="form-label">Observaciones</label><input name="observaciones" class="form-control tl-compact-input" value="<?= htmlspecialchars($pedido['observaciones'] ?? '') ?>"></div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar cambios</button></div>
                        </form>
                    </div></div></div>
                </div>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function(){
 const clientes = <?= json_encode($data['clientes'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
 const byId = Object.fromEntries(clientes.map(c => [String(c.id), c]));
 const clienteSel = document.getElementById('pedido_cliente');
 const cotSel = document.getElementById('pedido_cotizacion');
 const set = (id,v)=>{const el=document.getElementById(id); if(el&&!el.value) el.value=v||'';};
 clienteSel?.addEventListener('change', function(){ const c=byId[this.value]||{}; set('pedido_contacto', c.nombre); set('pedido_email', c.email); set('pedido_telefono', c.telefono); set('pedido_direccion', c.direccion); });
 cotSel?.addEventListener('change', function(){ const o=this.options[this.selectedIndex]; if(!o) return; if(o.dataset.cliente&&clienteSel) clienteSel.value=o.dataset.cliente; set('pedido_contacto', o.dataset.contacto); set('pedido_email', o.dataset.email); set('pedido_telefono', o.dataset.telefono); set('pedido_direccion', o.dataset.direccion); });
})();
</script>
