<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <h5 class="tl-section-title">Nuevo cliente</h5>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="create">
        <div class="col-md-3"><label class="form-label">RUT</label><input name="rut" class="form-control" placeholder="RUT" required></div>
        <div class="col-md-3"><label class="form-label">Nombre</label><input name="nombre" class="form-control" placeholder="Nombre" required></div>
        <div class="col-md-3"><label class="form-label">Empresa</label><input name="empresa" class="form-control" placeholder="Empresa"></div>
        <div class="col-md-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="Email" required></div>
        <div class="col-md-3"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" placeholder="Teléfono"></div>
        <div class="col-md-3"><label class="form-label">Dirección</label><input name="direccion" class="form-control" placeholder="Dirección"></div>
        <div class="col-md-3"><label class="form-label">Contraseña</label><input type="password" name="password" class="form-control" placeholder="Contraseña" required></div>
        <div class="col-md-3"><label class="form-label">Token URL</label><input name="url_token" class="form-control" placeholder="Token URL"></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Crear cliente</button></div>
    </form>
</div>

<div class="card">
    <h5 class="tl-section-title">Clientes registrados</h5>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>RUT</th><th>Nombre</th><th>Empresa</th><th>Email</th><th>Token</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($data['clientes'] as $cliente): ?>
                <tr>
                    <td><?= htmlspecialchars($cliente['rut']) ?></td><td><?= htmlspecialchars($cliente['nombre']) ?></td><td><?= htmlspecialchars($cliente['empresa']) ?></td>
                    <td><?= htmlspecialchars($cliente['email']) ?></td><td><?= htmlspecialchars($cliente['url_token']) ?></td>
                    <td>
                        <form method="post"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Eliminar</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
