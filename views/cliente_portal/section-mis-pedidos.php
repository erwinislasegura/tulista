<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Resumen de mis pedidos</h5>
        <p class="text-muted mb-3">Vista rápida para entender cómo va tu operación.</p>

        <?php $totalPedidos = count($data['pedidos']); ?>
        <?php $totalCotizaciones = count($data['cotizaciones']); ?>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 rounded-3 bg-light border h-100">
                    <div class="text-muted small">Pedidos totales</div>
                    <div class="fs-4 fw-semibold"><?= $totalPedidos ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 bg-light border h-100">
                    <div class="text-muted small">Cotizaciones registradas</div>
                    <div class="fs-4 fw-semibold"><?= $totalCotizaciones ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded-3 bg-light border h-100">
                    <div class="text-muted small">Último pedido</div>
                    <div class="fs-6 fw-semibold"><?= !empty($data['pedidos'][0]['fecha']) ? htmlspecialchars($data['pedidos'][0]['fecha']) : 'Sin registros' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
