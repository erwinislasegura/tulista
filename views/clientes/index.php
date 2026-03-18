<?php foreach ($data['flash'] as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>



<div class="card mb-4">
    <h5 class="tl-section-title">Nuevo cliente</h5>
    <form method="post" class="row g-3 tl-minimal-form">
        <input type="hidden" name="action" value="create">
        <div class="col-md-2"><label class="form-label">RUT</label><input name="rut" class="form-control tl-compact-input" required autocomplete="off" placeholder="76.123.456-7"></div>
        <div class="col-md-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control tl-compact-input" required autocomplete="name"></div>
        <div class="col-md-3"><label class="form-label">Empresa</label><input name="empresa" class="form-control tl-compact-input"></div>
        <div class="col-md-2"><label class="form-label">Giro</label><input name="giro" class="form-control tl-compact-input"></div>
        <div class="col-md-2"><label class="form-label">Comuna</label><input name="comuna" class="form-control tl-compact-input"></div>
        <div class="col-md-2"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input" inputmode="tel" autocomplete="tel"></div>
        <div class="col-md-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" required autocomplete="email"></div>
        <div class="col-md-4"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input" autocomplete="street-address"></div>
        <div class="col-md-2"><label class="form-label">Tipo cliente</label><select name="tipo_cliente" class="form-select tl-compact-input"><option value="mayorista">Mayorista</option><option value="minorista">Minorista</option><option value="institucional">Institucional</option></select></div>
        <div class="col-md-2"><label class="form-label">Clave portal</label><input type="password" name="password" class="form-control tl-compact-input" required></div>
        <div class="col-md-2"><label class="form-label">Token acceso</label><input id="cliente_token" name="token" class="form-control tl-compact-input" value="<?= bin2hex(random_bytes(5)) ?>"><small class="text-muted">Se autogenera según nombre/empresa.</small></div>
        <div class="col-md-2 form-check mt-4 ms-2"><input type="checkbox" name="estado" class="form-check-input" checked><label class="form-check-label">Activo</label></div>
        <div class="col-12 d-flex justify-content-end"><button class="btn btn-primary" type="submit">Crear cliente</button></div>
    </form>
</div>

<div class="card mb-4 tl-clientes-list-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="tl-section-title mb-0">Clientes</h5>
        <span class="text-muted small">Selecciona un cliente para ver su historial</span>
    </div>
    <div class="table-responsive tl-clientes-table-wrap">
        <table class="table align-middle">
            <thead><tr><th>Cliente</th><th>Contacto</th><th>Portal cliente</th><th>Cotizaciones</th><th>Pedidos</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($data['clientes'] as $cliente): ?>
                <?php $portalUrl = 'cliente-portal.php?token=' . urlencode($cliente['token']); ?>
                <tr>
                    <td><strong><?= htmlspecialchars($cliente['nombre']) ?></strong><div class="small text-muted"><?= htmlspecialchars($cliente['rut']) ?> · <?= htmlspecialchars($cliente['empresa'] ?: '-') ?> · <?= htmlspecialchars($cliente['comuna'] ?: '-') ?></div></td>
                    <td><?= htmlspecialchars($cliente['email']) ?><div class="small text-muted"><?= htmlspecialchars($cliente['telefono'] ?: '-') ?> · <?= htmlspecialchars($cliente['tipo_cliente'] ?: '-') ?></div></td>
                    <td><div class="input-group input-group-sm"><input class="form-control" readonly value="<?= htmlspecialchars($portalUrl) ?>"><button class="btn btn-outline-secondary js-copy-portal" type="button" data-url="<?= htmlspecialchars($portalUrl) ?>">Copiar</button></div></td>
                    <td><?= (int) $cliente['total_cotizaciones'] ?></td>
                    <td><?= (int) $cliente['total_pedidos'] ?></td>
                    <td><?= (int) $cliente['estado'] ? 'Activo' : 'Inactivo' ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="static" type="button">Acciones</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="apps-clientes.php?cliente_id=<?= (int) $cliente['id'] ?>">Ver</a></li>
                                <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editCliente<?= (int) $cliente['id'] ?>">Editar</button></li>
                                <li><a class="dropdown-item" target="_blank" href="<?= htmlspecialchars($portalUrl) ?>">Abrir portal</a></li>
                                <li><form method="post" onsubmit="return confirm('¿Eliminar cliente?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>"><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li>
                            </ul>
                        </div>
                    </td>
                </tr>

                <div class="modal fade" id="editCliente<?= (int) $cliente['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-body">
                        <h6 class="mb-3">Editar cliente #<?= (int) $cliente['id'] ?></h6>
                        <form method="post" class="row g-3 tl-minimal-form">
                            <input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>">
                            <div class="col-12"><div class="tl-form-card"><h6 class="tl-form-card-title">Editar información</h6><div class="row g-2 tl-minimal-form"><div class="col-md-3"><label class="form-label">RUT</label><input name="rut" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['rut']) ?>" required></div>
                            <div class="col-md-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['nombre']) ?>" required></div>
                            <div class="col-md-3"><label class="form-label">Empresa</label><input name="empresa" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['empresa'] ?? '') ?>"></div>
                            <div class="col-md-3"><label class="form-label">Giro</label><input name="giro" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['giro'] ?? '') ?>"></div>
                            <div class="col-md-3"><label class="form-label">Comuna</label><input name="comuna" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['comuna'] ?? '') ?>"></div>
                            <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['email']) ?>" required></div>
                            <div class="col-md-3"><label class="form-label">Teléfono</label><input name="telefono" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['telefono'] ?? '') ?>"></div>
                            <div class="col-md-4"><label class="form-label">Dirección</label><input name="direccion" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['direccion'] ?? '') ?>"></div>
                            <div class="col-md-3"><label class="form-label">Tipo cliente</label><select name="tipo_cliente" class="form-select tl-compact-input"><?php foreach (['mayorista', 'minorista', 'institucional'] as $tipo): ?><option value="<?= $tipo ?>" <?= ($cliente['tipo_cliente'] ?? 'mayorista') === $tipo ? 'selected' : '' ?>><?= ucfirst($tipo) ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-4"><label class="form-label">Token acceso</label><input name="token" class="form-control tl-compact-input" value="<?= htmlspecialchars($cliente['token']) ?>"></div>
                            <div class="col-md-3"><label class="form-label">Nueva clave</label><input type="password" name="password" class="form-control tl-compact-input" placeholder="Opcional"></div>
                            <div class="col-md-2 form-check mt-4 ms-2"><input type="checkbox" name="estado" class="form-check-input" <?= (int) $cliente['estado'] ? 'checked' : '' ?>><label class="form-check-label">Activo</label></div></div><div class="tl-form-actions d-flex justify-content-end gap-2 mt-3"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" type="submit">Guardar</button></div></div></div>
                        </form>
                    </div></div></div>
                </div>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($data['cliente_seleccionado'])): ?>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <h5 class="tl-section-title">Cotizaciones de <?= htmlspecialchars($data['cliente_seleccionado']['nombre']) ?></h5>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead><tbody>
            <?php foreach ($data['cotizaciones_cliente'] as $cotizacion): ?>
                <tr><td>#<?= (int) $cotizacion['id'] ?></td><td class="text-capitalize"><?= htmlspecialchars($cotizacion['estado']) ?></td><td>$<?= number_format((float) $cotizacion['total'], 0, ',', '.') ?></td><td><?= htmlspecialchars($cotizacion['fecha']) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <h5 class="tl-section-title">Pedidos de <?= htmlspecialchars($data['cliente_seleccionado']['nombre']) ?></h5>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead><tbody>
            <?php foreach ($data['pedidos_cliente'] as $pedido): ?>
                <tr><td>#<?= (int) $pedido['id'] ?></td><td class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td><td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td><td><?= htmlspecialchars($pedido['fecha']) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
  const nombre = document.querySelector('input[name="nombre"]');
  const empresa = document.querySelector('input[name="empresa"]');
  const token = document.getElementById('cliente_token');
  const slugifyToken = (v) => v.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '').slice(0, 10);
  function refreshToken() {
    if (!token || token.dataset.manual === '1') return;
    const base = slugifyToken((nombre?.value || '') + (empresa?.value || ''));
    if (base.length >= 6) token.value = base;
  }
  token?.addEventListener('input', () => token.dataset.manual = '1');
  nombre?.addEventListener('input', refreshToken);
  empresa?.addEventListener('input', refreshToken);
})();

  document.querySelectorAll('.js-copy-portal').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      const url = this.dataset.url || '';
      try {
        await navigator.clipboard.writeText(url);
        this.textContent = 'Copiado';
        setTimeout(() => this.textContent = 'Copiar', 1200);
      } catch (e) {
        window.prompt('Copia el enlace del portal:', url);
      }
    });
  });
</script>
