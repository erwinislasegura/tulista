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
                <p class="text-muted mb-0">Consulta estados y, cuando esté enviada, apruébala y descarga su PDF.</p>
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
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($cotizaciones as $cotizacion): ?>
                    <?php $estadoRaw = (string) ($cotizacion['estado'] ?? ''); ?>
                    <tr>
                        <td>#<?= (int) $cotizacion['id'] ?></td>
                        <td><span class="badge <?= $estadoBadge($estadoRaw) ?> text-capitalize"><?= htmlspecialchars($estadoTexto($estadoRaw)) ?></span></td>
                        <td><?= htmlspecialchars($formatCurrency((float) $cotizacion['total'])) ?></td>
                        <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="cotizar.php?view=cotizaciones&estado=<?= urlencode($estadoFiltro) ?>&download_pdf=<?= (int) $cotizacion['id'] ?>">Ver PDF</a>
                                <?php if ($estadoRaw === 'enviada'): ?>
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="action" value="aprobar_cotizacion_cliente">
                                        <input type="hidden" name="return_url" value="cotizar.php?view=cotizaciones&estado=<?= urlencode($estadoFiltro) ?>">
                                        <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                                        <button class="btn btn-sm btn-success" type="submit">Aprobar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($cotizaciones)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No hay cotizaciones para este estado.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
