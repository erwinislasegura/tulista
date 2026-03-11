<div class="page-content">
    <div class="container-fluid">

        <?php foreach ($data['flash'] as $alert): ?>
            <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($alert['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="mb-0"><?= htmlspecialchars($formTitle) ?></h5>
            </div>
            <div class="card-body pt-3">
                <?php include $formFile; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="mb-0">Registros existentes</h5>
            </div>
            <div class="card-body pt-3">
                <?php include $listFile; ?>
            </div>
        </div>
    </div>

    <?php include "partials/footer.php" ?>
</div>
