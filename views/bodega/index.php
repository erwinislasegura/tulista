<?php foreach (($data['flash'] ?? []) as $alert): ?>
    <div class="alert alert-<?= htmlspecialchars($alert['type']) ?> py-2 mb-2"><?= htmlspecialchars($alert['message']) ?></div>
<?php endforeach; ?>

<?php $menu = $data['menu'] ?? 'resumen'; ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <?php
            $menus = [
                'resumen' => 'Resumen operativo',
                'revision' => 'Revisión de faltantes',
                'logistica' => 'Flujo logístico',
                'stock' => 'Consulta de stock',
                'historial' => 'Historial de decisiones',
            ];
            foreach ($menus as $key => $label):
            ?>
                <a class="btn btn-sm <?= $menu === $key ? 'btn-primary' : 'btn-light' ?>" href="apps-bodega.php?menu=<?= urlencode($key) ?><?= !empty($data['review_pedido_id']) ? '&review_pedido=' . (int) $data['review_pedido_id'] : '' ?>"><?= htmlspecialchars($label) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($menu === 'resumen'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Cotizaciones aceptadas</small><h4 class="mb-0"><?= count($data['cotizaciones_aprobadas']) ?></h4></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Pedidos empaquetados</small><h4 class="mb-0"><?= count(array_filter($data['pedidos'], fn($p) => ($p['estado'] ?? '') === 'empaquetado')) ?></h4></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Pedidos despachados</small><h4 class="mb-0"><?= count(array_filter($data['pedidos'], fn($p) => ($p['estado'] ?? '') === 'despachado')) ?></h4></div></div>
        <div class="col-md-3"><div class="card p-3"><small class="text-muted">Pedidos en tránsito</small><h4 class="mb-0"><?= count(array_filter($data['pedidos'], fn($p) => ($p['estado'] ?? '') === 'transito')) ?></h4></div></div>
    </div>

    <div class="card">
        <h5 class="tl-section-title">Cotizaciones aceptadas por clientes</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Cotización</th><th>Cliente</th><th>Total</th><th>Pedido</th><th>Estado pedido</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach ($data['cotizaciones_aprobadas'] as $cot): ?>
                    <tr>
                        <td>#<?= (int) $cot['id'] ?></td>
                        <td><?= htmlspecialchars($cot['cliente_nombre']) ?></td>
                        <td>$<?= number_format((float) $cot['total'], 0, ',', '.') ?></td>
                        <td><?= !empty($cot['pedido_id']) ? ('#' . (int) $cot['pedido_id']) : 'Sin pedido' ?></td>
                        <td><?= htmlspecialchars($cot['pedido_estado'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($cot['pedido_fecha'] ?: $cot['fecha']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['cotizaciones_aprobadas'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">Sin cotizaciones aprobadas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($menu === 'revision'): ?>
    <div class="card mb-4">
        <h5 class="tl-section-title">Preparar pedido para revisión de empaque</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Pedido</th><th>Cliente</th><th>Cotización</th><th>Estado</th><th>Total</th><th>Acción</th></tr></thead>
                <tbody>
                <?php foreach ($data['pedidos'] as $pedido): ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                        <td><?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : '-' ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></td>
                        <td>$<?= number_format((float) $pedido['total'], 0, ',', '.') ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="action" value="preparar_pedido">
                                <input type="hidden" name="menu" value="revision">
                                <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                                <input type="hidden" name="review_pedido" value="<?= (int) $pedido['id'] ?>">
                                <button class="btn btn-sm btn-primary" type="submit">Revisar faltantes</button>
                                <a class="btn btn-sm btn-light" href="apps-bodega.php?menu=revision&review_pedido=<?= (int) $pedido['id'] ?>">Ver detalle</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($data['review_pedido_id'])): ?>
        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card p-3"><small class="text-muted">Pendientes</small><h4 class="mb-0"><?= (int) ($data['review_summary']['pendiente'] ?? 0) ?></h4></div></div>
            <div class="col-md-3"><div class="card p-3"><small class="text-muted">Confirmados</small><h4 class="mb-0"><?= (int) ($data['review_summary']['confirmado'] ?? 0) ?></h4></div></div>
            <div class="col-md-3"><div class="card p-3"><small class="text-muted">Reemplazados</small><h4 class="mb-0"><?= (int) ($data['review_summary']['reemplazado'] ?? 0) ?></h4></div></div>
            <div class="col-md-3"><div class="card p-3"><small class="text-muted">Omitidos</small><h4 class="mb-0"><?= (int) ($data['review_summary']['omitido'] ?? 0) ?></h4></div></div>
        </div>

        <div class="card">
            <h5 class="tl-section-title">Resolución de faltantes para pedido #<?= (int) $data['review_pedido_id'] ?></h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Producto</th><th>Solicitado</th><th>Stock actual</th><th>Decisión</th><th>Acción</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['review_items'] as $item): ?>
                        <?php $sinStock = (int) $item['stock_actual'] < (int) $item['cantidad_solicitada']; ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['producto_nombre']) ?></strong><br>
                                <small class="text-muted">SKU: <?= htmlspecialchars($item['producto_sku']) ?></small>
                                <?php if ($sinStock): ?><br><span class="badge bg-danger-subtle text-danger">Sin stock suficiente</span><?php endif; ?>
                                <?php if (($item['accion'] ?? '') === 'reemplazado' && !empty($item['reemplazo_nombre'])): ?><br><small class="text-success">Reemplazo: <?= htmlspecialchars($item['reemplazo_nombre']) ?> (<?= htmlspecialchars($item['reemplazo_sku']) ?>)</small><?php endif; ?>
                            </td>
                            <td><?= (int) $item['cantidad_solicitada'] ?></td>
                            <td><?= (int) $item['stock_actual'] ?></td>
                            <td><span class="badge bg-light text-dark text-capitalize"><?= htmlspecialchars($item['accion']) ?></span></td>
                            <td>
                                <form method="post" class="row g-2">
                                    <input type="hidden" name="action" value="resolver_item">
                                    <input type="hidden" name="menu" value="revision">
                                    <input type="hidden" name="review_pedido" value="<?= (int) $data['review_pedido_id'] ?>">
                                    <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
                                    <div class="col-md-3">
                                        <select name="decision" class="form-select form-select-sm" required>
                                            <option value="confirmado" <?= $item['accion'] === 'confirmado' ? 'selected' : '' ?>>Confirmar</option>
                                            <option value="reemplazado" <?= $item['accion'] === 'reemplazado' ? 'selected' : '' ?>>Reemplazar</option>
                                            <option value="omitido" <?= $item['accion'] === 'omitido' ? 'selected' : '' ?>>Excluir</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2"><input type="number" min="0" name="cantidad_empaquetada" class="form-control form-control-sm" value="<?= (int) $item['cantidad_empaquetada'] ?>" placeholder="Cant."></div>
                                    <div class="col-md-3">
                                        <select name="producto_reemplazo_id" class="form-select form-select-sm">
                                            <option value="">Sin reemplazo</option>
                                            <?php foreach ($data['productos'] as $producto): ?>
                                                <option value="<?= (int) $producto['id'] ?>" <?= (int) ($item['producto_reemplazo_id'] ?? 0) === (int) $producto['id'] ? 'selected' : '' ?>><?= htmlspecialchars($producto['nombre']) ?> (Stock: <?= (int) $producto['existencia'] ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3"><input type="text" name="notas" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($item['notas'] ?? '')) ?>" placeholder="Nota decisión"></div>
                                    <div class="col-md-1"><button class="btn btn-sm btn-primary w-100" type="submit">OK</button></div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data['review_items'])): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Aún no hay ítems de empaque para este pedido. Presiona "Revisar faltantes".</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($menu === 'logistica'): ?>
    <div class="card">
        <h5 class="tl-section-title">Flujo logístico rápido</h5>
        <p class="text-muted">Actualiza estados de pedido sin salir del módulo para acelerar empaquetado, despacho y tránsito.</p>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Pedido</th><th>Cliente</th><th>Estado actual</th><th>Cambiar a</th></tr></thead>
                <tbody>
                <?php foreach ($data['pedidos'] as $pedido): ?>
                    <tr>
                        <td>#<?= (int) $pedido['id'] ?></td>
                        <td><?= htmlspecialchars($pedido['cliente_nombre']) ?></td>
                        <td><span class="badge bg-light text-dark text-capitalize"><?= htmlspecialchars($pedido['estado']) ?></span></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="action" value="cambiar_estado">
                                <input type="hidden" name="menu" value="logistica">
                                <input type="hidden" name="pedido_id" value="<?= (int) $pedido['id'] ?>">
                                <select name="estado" class="form-select form-select-sm" style="max-width: 220px;">
                                    <?php foreach ($data['estados_operacion'] as $estado): ?>
                                        <option value="<?= $estado ?>" <?= $estado === $pedido['estado'] ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-primary" type="submit">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($menu === 'stock'): ?>
    <div class="card">
        <h5 class="tl-section-title">Consulta de stock para clientes/usuarios</h5>
        <form method="get" class="row g-2 align-items-end mb-3">
            <input type="hidden" name="menu" value="stock">
            <div class="col-md-8"><label class="form-label">Producto o SKU</label><input type="text" name="stock_q" class="form-control tl-compact-input" value="<?= htmlspecialchars($data['stock_query']) ?>"></div>
            <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary" type="submit">Buscar stock</button><a href="apps-bodega.php?menu=stock" class="btn btn-light">Limpiar</a></div>
        </form>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Producto</th><th>SKU</th><th>Existencia</th><th>Mínimo</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($data['stock_resultados'] as $item): ?>
                    <?php $ok = (int) $item['existencia'] > (int) $item['stock_minimo']; ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                        <td><?= htmlspecialchars($item['sku']) ?></td>
                        <td><?= (int) $item['existencia'] ?></td>
                        <td><?= (int) $item['stock_minimo'] ?></td>
                        <td><span class="badge <?= $ok ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>"><?= $ok ? 'Disponible' : 'Stock crítico' ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['stock_resultados'])): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">Sin resultados de stock.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if ($menu === 'historial'): ?>
    <div class="card">
        <h5 class="tl-section-title">Historial de decisiones de empaque</h5>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Pedido</th><th>Producto</th><th>Decisión</th><th>Cant. solicitada</th><th>Cant. empaquetada</th><th>Reemplazo</th><th>Nota</th><th>Usuario</th><th>Actualización</th></tr></thead>
                <tbody>
                <?php foreach ($data['historial'] as $item): ?>
                    <tr>
                        <td>#<?= (int) $item['pedido_id'] ?></td>
                        <td><?= htmlspecialchars($item['producto_nombre']) ?></td>
                        <td><span class="badge bg-light text-dark text-capitalize"><?= htmlspecialchars($item['accion']) ?></span></td>
                        <td><?= (int) $item['cantidad_solicitada'] ?></td>
                        <td><?= (int) $item['cantidad_empaquetada'] ?></td>
                        <td><?= htmlspecialchars($item['reemplazo_nombre'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['notas'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['usuario_nombre'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['updated_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['historial'])): ?>
                    <tr><td colspan="9" class="text-center text-muted py-3">Aún no hay decisiones registradas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
