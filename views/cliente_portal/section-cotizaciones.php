<?php
$estadoFiltro = (string) ($_GET['estado'] ?? 'todas');
$estados = [
    'todas' => 'Todas',
    'sin_revision' => 'Sin revisión',
    'enviada' => 'Enviada',
    'aprobada' => 'Aprobada',
    'rechazada' => 'Rechazada',
];

$normalizarEstado = static function (string $estado): string {
    return $estado === 'borrador' ? 'sin_revision' : $estado;
};

$estadoTexto = static function (string $estado): string {
    return $estado === 'borrador' ? 'sin revisión' : $estado;
};

$estadoBadge = static function (string $estado): string {
    return match ($estado) {
        'borrador' => 'bg-secondary-subtle text-secondary',
        'enviada' => 'bg-warning-subtle text-warning',
        'aprobada' => 'bg-success-subtle text-success',
        'rechazada' => 'bg-danger-subtle text-danger',
        default => 'bg-light text-dark',
    };
};

$cotizaciones = array_values(array_filter($data['cotizaciones'], static function ($cotizacion) use ($estadoFiltro, $normalizarEstado) {
    if ($estadoFiltro === 'todas') {
        return true;
    }

    return $normalizarEstado((string) ($cotizacion['estado'] ?? '')) === $estadoFiltro;
}));

$detallesPorCotizacion = $data['detalles_por_cotizacion'] ?? [];
$resumenCotizaciones = [
    'total' => count($data['cotizaciones']),
    'pendientes' => count(array_filter($data['cotizaciones'], static fn ($c) => in_array((string) ($c['estado'] ?? ''), ['borrador', 'enviada'], true))),
    'aprobadas' => count(array_filter($data['cotizaciones'], static fn ($c) => ((string) ($c['estado'] ?? '')) === 'aprobada')),
];
?>

<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-1">Cotizaciones registradas</h5>
                <p class="text-muted mb-0">Gestiona tus cotizaciones con acciones rápidas: ver detalle, editar y eliminar.</p>
            </div>
            <span class="badge rounded-pill text-bg-primary">Historial cliente</span>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach ($estados as $key => $label): ?>
                <a class="btn btn-sm <?= $estadoFiltro === $key ? 'btn-primary' : 'btn-light' ?>" href="cotizar.php?view=cotizaciones&estado=<?= urlencode($key) ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-sm-4"><div class="alert alert-light border mb-0 py-2"><strong><?= (int) $resumenCotizaciones['total'] ?></strong> cotizaciones</div></div>
            <div class="col-sm-4"><div class="alert alert-warning-subtle border mb-0 py-2"><strong><?= (int) $resumenCotizaciones['pendientes'] ?></strong> pendientes</div></div>
            <div class="col-sm-4"><div class="alert alert-success-subtle border mb-0 py-2"><strong><?= (int) $resumenCotizaciones['aprobadas'] ?></strong> aprobadas</div></div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 tl-portal-list-table">
                <thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Items</th><th>Fecha</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($cotizaciones as $cotizacion): ?>
                    <?php
                        $cotizacionId = (int) $cotizacion['id'];
                        $estadoRaw = (string) ($cotizacion['estado'] ?? '');
                        $permiteEdicion = in_array($estadoRaw, ['borrador', 'enviada'], true);
                        $detalles = $detallesPorCotizacion[$cotizacionId] ?? [];
                        $detallesJson = htmlspecialchars(json_encode($detalles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
                        $cantidadItems = count($detalles);
                        $promedioItem = $cantidadItems > 0 ? ((float) $cotizacion['total'] / $cantidadItems) : 0;
                    ?>
                    <tr>
                        <td>#<?= $cotizacionId ?></td>
                        <td><span class="badge <?= $estadoBadge($estadoRaw) ?> text-capitalize"><?= htmlspecialchars($estadoTexto($estadoRaw)) ?></span></td>
                        <td><?= htmlspecialchars($formatCurrency((float) $cotizacion['total'])) ?></td>
                        <td>
                            <div class="d-flex flex-column lh-sm">
                                <strong><?= $cantidadItems ?></strong>
                                <small class="text-muted">Prom: <?= htmlspecialchars($formatCurrency($promedioItem)) ?></small>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Opciones
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button
                                            class="dropdown-item js-ver-cotizacion"
                                            type="button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-detalle-cotizacion"
                                            data-id="<?= $cotizacionId ?>"
                                            data-estado="<?= htmlspecialchars($estadoTexto($estadoRaw)) ?>"
                                            data-total="<?= htmlspecialchars($formatCurrency((float) $cotizacion['total'])) ?>"
                                            data-detalles="<?= $detallesJson ?>"
                                        >Ver detalle</button>
                                    </li>
                                    <li><a class="dropdown-item" href="cotizar.php?view=cotizaciones&estado=<?= urlencode($estadoFiltro) ?>&download_pdf=<?= $cotizacionId ?>">Descargar PDF</a></li>
                                    <?php if ($permiteEdicion): ?>
                                        <li>
                                            <button
                                                class="dropdown-item js-editar-cotizacion"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modal-editar-cotizacion"
                                                data-id="<?= $cotizacionId ?>"
                                                data-detalles="<?= $detallesJson ?>"
                                            >Editar cantidades</button>
                                        </li>
                                        <li>
                                            <form method="post" class="m-0" onsubmit="return confirm('¿Eliminar cotización #<?= $cotizacionId ?>?');">
                                                <input type="hidden" name="action" value="eliminar_cotizacion_cliente">
                                                <input type="hidden" name="return_url" value="cotizar.php?view=cotizaciones&estado=<?= urlencode($estadoFiltro) ?>">
                                                <input type="hidden" name="cotizacion_id" value="<?= $cotizacionId ?>">
                                                <button class="dropdown-item text-danger" type="submit">Eliminar</button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                    <?php if ($estadoRaw === 'enviada'): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="post" class="m-0">
                                                <input type="hidden" name="action" value="aprobar_cotizacion_cliente">
                                                <input type="hidden" name="return_url" value="cotizar.php?view=cotizaciones&estado=<?= urlencode($estadoFiltro) ?>">
                                                <input type="hidden" name="cotizacion_id" value="<?= $cotizacionId ?>">
                                                <button class="dropdown-item text-success" type="submit">Aprobar y generar pedido</button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($cotizaciones)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No hay cotizaciones para este estado.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-detalle-cotizacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle cotización <span id="detalle-cotizacion-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-dark text-capitalize" id="detalle-cotizacion-estado">-</span>
                    <strong id="detalle-cotizacion-total">$0</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>SKU</th><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead>
                        <tbody id="detalle-cotizacion-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-editar-cotizacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar cotización <span id="editar-cotizacion-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="post" id="editar-cotizacion-form">
                <div class="modal-body">
                    <input type="hidden" name="action" value="editar_cotizacion_cliente">
                    <input type="hidden" name="return_url" value="cotizar.php?view=cotizaciones&estado=<?= urlencode($estadoFiltro) ?>">
                    <input type="hidden" name="cotizacion_id" id="editar-cotizacion-hidden-id" value="0">
                    <p class="text-muted mb-2">Modifica cantidades. Si un ítem queda en 0, se elimina de la cotización.</p>
                    <div id="editar-cotizacion-items" class="d-grid gap-2"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const detalleBody = document.getElementById('detalle-cotizacion-body');
    const detalleId = document.getElementById('detalle-cotizacion-id');
    const detalleEstado = document.getElementById('detalle-cotizacion-estado');
    const detalleTotal = document.getElementById('detalle-cotizacion-total');

    const editarIdLabel = document.getElementById('editar-cotizacion-id');
    const editarIdInput = document.getElementById('editar-cotizacion-hidden-id');
    const editarItemsWrap = document.getElementById('editar-cotizacion-items');

    document.querySelectorAll('.js-ver-cotizacion').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id || '-';
            const estado = btn.dataset.estado || '-';
            const total = btn.dataset.total || '$0';
            const detalles = JSON.parse(btn.dataset.detalles || '[]');

            detalleId.textContent = `#${id}`;
            detalleEstado.textContent = estado;
            detalleTotal.textContent = total;
            detalleBody.innerHTML = '';

            if (!detalles.length) {
                detalleBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Sin detalles en esta cotización.</td></tr>';
                return;
            }

            detalles.forEach((item) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.sku || '-'}</td>
                    <td>${item.producto_nombre || '-'}</td>
                    <td>${item.cantidad || 0}</td>
                    <td>$${new Intl.NumberFormat('es-CL').format(Number(item.precio || 0))}</td>
                    <td>$${new Intl.NumberFormat('es-CL').format(Number(item.subtotal || 0))}</td>
                `;
                detalleBody.appendChild(tr);
            });
        });
    });

    document.querySelectorAll('.js-editar-cotizacion').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id || '0';
            const detalles = JSON.parse(btn.dataset.detalles || '[]');

            editarIdLabel.textContent = `#${id}`;
            editarIdInput.value = id;
            editarItemsWrap.innerHTML = '';

            if (!detalles.length) {
                editarItemsWrap.innerHTML = '<div class="alert alert-warning mb-0">No hay ítems disponibles para editar.</div>';
                return;
            }

            detalles.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'border rounded p-2';
                row.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <strong class="d-block">${item.producto_nombre || '-'}</strong>
                            <small class="text-muted">SKU: ${item.sku || '-'} · Precio: $${new Intl.NumberFormat('es-CL').format(Number(item.precio || 0))}</small>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mb-1">Cantidad</label>
                            <input type="number" min="0" step="1" class="form-control form-control-sm" name="cantidades[${Number(item.producto_id || 0)}]" value="${Number(item.cantidad || 0)}">
                        </div>
                    </div>
                `;
                editarItemsWrap.appendChild(row);
            });
        });
    });
})();
</script>
