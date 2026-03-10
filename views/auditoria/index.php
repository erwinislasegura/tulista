<div class="card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="tl-section-title mb-0">Actividad reciente del sistema</h5>
        <span class="text-muted small">Últimos <?= count($data['logs']) ?> eventos</span>
    </div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Fecha</th><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Descripción</th><th>URL</th></tr></thead><tbody>
    <?php foreach ($data['logs'] as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['fecha']) ?></td>
            <td><?= htmlspecialchars($log['usuario_nombre'] ?? 'Sistema') ?></td>
            <td><?= htmlspecialchars($log['modulo']) ?></td>
            <td><?= htmlspecialchars($log['accion']) ?></td>
            <td><?= htmlspecialchars($log['descripcion']) ?></td>
            <td class="small text-muted"><?= htmlspecialchars($log['url'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div>
