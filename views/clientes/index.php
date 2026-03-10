<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>
<form method="post" class="row g-2 mb-3">
    <input type="hidden" name="action" value="create">
    <div class="col-md-2"><input name="rut" class="form-control" placeholder="RUT" required></div>
    <div class="col-md-2"><input name="nombre" class="form-control" placeholder="Nombre" required></div>
    <div class="col-md-2"><input name="empresa" class="form-control" placeholder="Empresa"></div>
    <div class="col-md-2"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
    <div class="col-md-2"><input name="telefono" class="form-control" placeholder="Teléfono"></div>
    <div class="col-md-2"><input name="direccion" class="form-control" placeholder="Dirección"></div>
    <div class="col-md-2"><input type="password" name="password" class="form-control" placeholder="Contraseña" required></div>
    <div class="col-md-2"><input name="url_token" class="form-control" placeholder="Token URL"></div>
    <div class="col-md-2"><button class="btn btn-primary">Crear cliente</button></div>
</form>
<table class="table table-sm">
    <thead><tr><th>RUT</th><th>Nombre</th><th>Empresa</th><th>Email</th><th>Token</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($data['clientes'] as $cliente): ?>
        <tr>
            <td><?= htmlspecialchars($cliente['rut']) ?></td><td><?= htmlspecialchars($cliente['nombre']) ?></td><td><?= htmlspecialchars($cliente['empresa']) ?></td>
            <td><?= htmlspecialchars($cliente['email']) ?></td><td><?= htmlspecialchars($cliente['url_token']) ?></td>
            <td>
                <form method="post"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>"><button class="btn btn-sm btn-danger">Eliminar</button></form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
