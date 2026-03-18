<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Aprobar cotizaciones</h5>
                <p class="text-muted mb-0">Convierte cotizaciones aprobadas o enviadas en pedidos con un solo clic.</p>
            </div>
            <span class="badge rounded-pill text-bg-success">Paso 2 de 3</span>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>ID</th><th>Estado</th><th>Total</th><th>Fecha</th><th>Acción</th></tr></thead>
                <tbody>
                <?php foreach ($data['cotizaciones'] as $cotizacion): ?>
                    <tr>
                        <td>#<?= (int) $cotizacion['id'] ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($cotizacion['estado']) ?></td>
                        <td><?= htmlspecialchars($formatCurrency((float) $cotizacion['total'])) ?></td>
                        <td><?= htmlspecialchars($cotizacion['fecha']) ?></td>
                        <td>
                            <?php if (in_array($cotizacion['estado'], ['aprobada', 'enviada'], true)): ?>
                                <form method="post" class="m-0">
                                    <input type="hidden" name="action" value="crear_pedido">
                                    <input type="hidden" name="return_url" value="cliente-portal.php?view=aprobar">
                                    <input type="hidden" name="cotizacion_id" value="<?= (int) $cotizacion['id'] ?>">
                                    <button class="btn btn-success btn-sm" type="submit">Generar pedido</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Esperando revisión comercial</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
