<?php foreach ($data['flash'] as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?>"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<div class="card">
    <h5 class="tl-section-title">Configuración de empresa</h5>
    <form method="post" enctype="multipart/form-data" class="row g-3 tl-minimal-form">
        <input type="hidden" name="logo_path_actual" value="<?= htmlspecialchars($data['empresa']['logo_path'] ?? '') ?>">
        <div class="col-md-4">
            <label class="form-label">Nombre comercial</label>
            <input class="form-control" name="nombre" value="<?= htmlspecialchars($data['empresa']['nombre'] ?? 'TU LISTA') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Razón social</label>
            <input class="form-control" name="razon_social" value="<?= htmlspecialchars($data['empresa']['razon_social'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">RUT empresa</label>
            <input class="form-control" name="rut" value="<?= htmlspecialchars($data['empresa']['rut'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($data['empresa']['email'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Teléfono</label>
            <input class="form-control" name="telefono" value="<?= htmlspecialchars($data['empresa']['telefono'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Sitio web</label>
            <input class="form-control" name="sitio_web" value="<?= htmlspecialchars($data['empresa']['sitio_web'] ?? '') ?>" placeholder="https://">
        </div>
        <div class="col-md-8">
            <label class="form-label">Dirección</label>
            <input class="form-control" name="direccion" value="<?= htmlspecialchars($data['empresa']['direccion'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Logo corporativo</label>
            <input type="file" class="form-control" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg">
        </div>

        <?php if (!empty($data['empresa']['logo_path'])): ?>
            <div class="col-12">
                <p class="text-muted mb-2">Logo actual</p>
                <img src="<?= htmlspecialchars($data['empresa']['logo_path']) ?>" alt="Logo empresa" style="max-height: 64px; width: auto;">
            </div>
        <?php endif; ?>

        <div class="col-12">
            <button class="btn btn-primary" type="submit">Guardar configuración</button>
        </div>
    </form>
</div>
