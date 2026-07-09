<?php $returnUrl = htmlspecialchars($data['section'] ?? 'apps-productos-categorias.php'); ?>
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead>
            <tr>
                <th>Categoría</th>
                <th class="text-center">Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($data['categories'])): ?>
            <tr><td colspan="3" class="text-muted">Sin categorías</td></tr>
        <?php else: foreach ($data['categories'] as $item): ?>
            <?php $isActive = (int) ($item['activo'] ?? 1) === 1; ?>
            <tr>
                <td>
                    <form method="post" class="d-flex gap-2 align-items-center tl-category-edit-form d-none" id="category-edit-<?= (int) $item['id'] ?>">
                        <input type="hidden" name="action" value="update_category">
                        <input type="hidden" name="return_url" value="<?= $returnUrl ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <input class="form-control form-control-sm" name="name" value="<?= htmlspecialchars($item['nombre']) ?>" required>
                        <button class="btn btn-sm btn-success" type="submit">Guardar</button>
                        <button class="btn btn-sm btn-light" type="button" data-category-edit-cancel="<?= (int) $item['id'] ?>">Cancelar</button>
                    </form>
                    <div class="tl-category-view" id="category-view-<?= (int) $item['id'] ?>">
                        <span class="fw-medium"><?= htmlspecialchars($item['nombre']) ?></span>
                        <span class="badge bg-light text-dark ms-2">#<?= (int) $item['id'] ?></span>
                    </div>
                </td>
                <td class="text-center">
                    <form method="post" class="d-inline-block">
                        <input type="hidden" name="action" value="toggle_category">
                        <input type="hidden" name="return_url" value="<?= $returnUrl ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <input type="hidden" name="activo" value="<?= $isActive ? 0 : 1 ?>">
                        <div class="form-check form-switch d-inline-flex align-items-center gap-2 ps-0">
                            <input class="form-check-input ms-0" type="checkbox" role="switch" <?= $isActive ? 'checked' : '' ?> onchange="this.form.submit()" aria-label="<?= $isActive ? 'Deshabilitar' : 'Habilitar' ?> categoría <?= htmlspecialchars($item['nombre']) ?>">
                            <span class="small <?= $isActive ? 'text-success' : 'text-muted' ?>"><?= $isActive ? 'Habilitada' : 'Deshabilitada' ?></span>
                        </div>
                    </form>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary" type="button" data-category-edit="<?= (int) $item['id'] ?>">Editar</button>
                    <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar la categoría <?= htmlspecialchars($item['nombre']) ?>? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="return_url" value="<?= $returnUrl ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Borrar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<script>
document.querySelectorAll('[data-category-edit]').forEach((button) => {
    button.addEventListener('click', () => {
        const id = button.dataset.categoryEdit;
        document.getElementById(`category-view-${id}`)?.classList.add('d-none');
        document.getElementById(`category-edit-${id}`)?.classList.remove('d-none');
    });
});
document.querySelectorAll('[data-category-edit-cancel]').forEach((button) => {
    button.addEventListener('click', () => {
        const id = button.dataset.categoryEditCancel;
        document.getElementById(`category-edit-${id}`)?.classList.add('d-none');
        document.getElementById(`category-view-${id}`)?.classList.remove('d-none');
    });
});
</script>
