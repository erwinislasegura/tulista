<?php foreach (($data['flash'] ?? []) as $alert): ?>
<div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<?php
$estadoFiltro = (string) ($data['estado_filtro'] ?? 'todas');
$estados = [
    'todas' => 'Todas',
    'sin_revision' => 'Sin revisión',
    'enviada' => 'Enviada',
    'aprobada' => 'Aprobada',
    'rechazada' => 'Rechazada',
];
$estadoLabel = static function (string $estado): string {
    return $estado === 'borrador' ? 'sin revisión' : $estado;
};
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="tl-section-title mb-1">Cotizaciones registradas</h5>
                <p class="text-muted mb-0">Gestiona revisión comercial, aprobación y descarga de documentos.</p>
            </div>
            <a href="apps-cotizaciones.php" class="btn btn-primary">Nueva cotización</a>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($estados as $key => $label): ?>
                <a class="btn btn-sm <?= $estadoFiltro === $key ? 'btn-primary' : 'btn-light' ?>" href="apps-cotizaciones-registradas.php?estado=<?= urlencode($key) ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>ID</th><th>Cliente</th><th>Vendedor</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
                    <tr>
                        <td>#<?= (int) $cotizacion['id'] ?></td>
                        <td><?= htmlspecialchars($cotizacion['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($cotizacion['vendedor'] ?? 'Sin asignar') ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($estadoLabel((string) $cotizacion['estado'])) ?></td>
                        <td>$<?= number_format((float) $cotizacion['total'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-display="static" type="button">Acciones</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="apps-cotizaciones.php?download_pdf=<?= (int) $cotizacion['id'] ?>">Descargar PDF</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="post" class="px-2 py-1">
                                            <input type="hidden" name="action" value="cambiar_estado">
                                            <input type="hidden" name="return_url" value="apps-cotizaciones-registradas.php?estado=<?= urlencode($estadoFiltro) ?>">
                                            <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                                            <label class="form-label small mb-1">Cambiar estado</label>
                                            <select name="estado" class="form-select form-select-sm tl-compact-input mb-2">
                                                <?php foreach (['borrador' => 'Sin revisión','enviada' => 'Enviada','aprobada' => 'Aprobada','rechazada' => 'Rechazada'] as $estadoValue => $estadoTexto): ?>
                                                    <option value="<?= $estadoValue ?>" <?= $estadoValue === $cotizacion['estado'] ? 'selected' : '' ?>><?= $estadoTexto ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-primary btn-sm w-100" type="submit">Guardar</button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form method="post" onsubmit="return confirm('¿Eliminar cotización?');"><input type="hidden" name="action" value="eliminar"><input type="hidden" name="return_url" value="apps-cotizaciones-registradas.php?estado=<?= urlencode($estadoFiltro) ?>"><input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>"><button class="dropdown-item text-danger" type="submit">Eliminar</button></form></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['cotizaciones'])): ?>
                    <tr><td colspan="7" class="text-center text-muted py-3">No hay cotizaciones para este estado.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
