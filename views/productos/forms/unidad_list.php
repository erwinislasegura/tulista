<div class="table-responsive">
<table class="table align-middle table-hover">
    <thead><tr><th>Descripción</th><th>Abreviatura</th></tr></thead>
    <tbody>
    <?php if (empty($data['units'])): ?>
        <tr><td colspan="2" class="text-center text-muted">Sin unidades</td></tr>
    <?php else: foreach ($data['units'] as $item): ?>
        <tr><td><?= htmlspecialchars($item['descripcion']) ?></td><td><span class="badge bg-info-subtle text-info"><?= htmlspecialchars($item['abreviatura']) ?></span></td></tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>
</div>
