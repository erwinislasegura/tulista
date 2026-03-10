<ul class="list-group">
<?php if (empty($data['brands'])): ?>
    <li class="list-group-item text-muted">Sin marcas</li>
<?php else: foreach ($data['brands'] as $item): ?>
    <li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($item['nombre']) ?></span><span class="badge bg-light text-dark">#<?= (int) $item['id'] ?></span></li>
<?php endforeach; endif; ?>
</ul>
