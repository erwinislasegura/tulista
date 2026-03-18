<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-1">Seguimiento de pedidos</h5>
        <p class="text-muted mb-3">Visualiza la trazabilidad de cada pedido en formato línea de tiempo y abre cada estado para ver el detalle en un modal.</p>

        <?php
        $pedidosSeguimiento = array_values(array_filter($data['pedidos'], static function ($pedido) {
            return !in_array((string) ($pedido['estado'] ?? ''), ['entregado', 'cancelado'], true);
        }));
        $totalActivos = count($pedidosSeguimiento);
        $transitoActivos = count(array_filter($pedidosSeguimiento, static function ($pedido) {
            return in_array(strtolower((string) ($pedido['estado'] ?? '')), ['despachado', 'transito', 'en_transito', 'en tránsito'], true);
        }));
        $timelineEstados = [
            'pendiente' => ['label' => 'Pendiente', 'icon' => 'solar:clock-circle-linear', 'description' => 'Pedido creado y en cola de preparación.'],
            'en_proceso' => ['label' => 'En proceso', 'icon' => 'solar:settings-minimalistic-linear', 'description' => 'El equipo está preparando y consolidando los ítems.'],
            'empaquetado' => ['label' => 'Empaquetado', 'icon' => 'solar:box-minimalistic-linear', 'description' => 'Pedido embalado y listo para despacho.'],
            'despachado' => ['label' => 'Despachado', 'icon' => 'solar:delivery-linear', 'description' => 'Pedido retirado por transporte o enviado.'],
            'transito' => ['label' => 'En tránsito', 'icon' => 'solar:rocket-2-linear', 'description' => 'Pedido en ruta hacia la dirección de entrega.'],
            'entregado' => ['label' => 'Entregado', 'icon' => 'solar:check-circle-linear', 'description' => 'Pedido entregado correctamente al cliente.'],
        ];
        $normalizarEstado = static function (string $estado): string {
            $normalizado = strtolower(trim($estado));
            return match ($normalizado) {
                'en tránsito', 'en_transito' => 'transito',
                default => $normalizado,
            };
        };
        $estadoOrden = array_keys($timelineEstados);
        $estadoIndice = array_flip($estadoOrden);
        ?>

        <style>
            .cp-tracking-card {
                border: 1px solid rgba(148, 163, 184, 0.28);
                border-radius: 14px;
                padding: .85rem;
                background: #fff;
            }

            .cp-tracking-line {
                display: flex;
                flex-wrap: wrap;
                gap: .55rem;
            }

            .cp-track-point {
                position: relative;
                flex: 1 1 150px;
                border: 1px solid rgba(203, 213, 225, 0.8);
                border-radius: 10px;
                background: #f8fafc;
                padding: .55rem .6rem;
                text-align: left;
                transition: all .2s ease;
            }

            .cp-track-point::after {
                content: '';
                position: absolute;
                top: 50%;
                right: -.42rem;
                width: .4rem;
                height: .4rem;
                border-radius: 50%;
                transform: translateY(-50%);
                background: rgba(148, 163, 184, 0.55);
            }

            .cp-track-point:last-child::after {
                display: none;
            }

            .cp-track-point iconify-icon {
                font-size: 1rem;
            }

            .cp-track-point.is-done {
                border-color: rgba(22, 163, 74, .45);
                background: #f0fdf4;
                color: #166534;
            }

            .cp-track-point.is-current {
                border-color: rgba(59, 130, 246, .5);
                background: #eff6ff;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, .12);
            }

            .cp-track-point.is-pending {
                opacity: .76;
            }

            .cp-track-point.is-cancelled {
                border-color: rgba(239, 68, 68, .45);
                background: #fef2f2;
                color: #991b1b;
            }

            .cp-track-point:focus-visible {
                outline: 2px solid #2563eb;
                outline-offset: 1px;
            }
        </style>

        <div class="row g-2 mb-3">
            <div class="col-sm-6">
                <div class="alert alert-light border mb-0 py-2">
                    <strong><?= (int) $totalActivos ?></strong> pedidos activos
                </div>
            </div>
            <div class="col-sm-6">
                <div class="alert alert-warning-subtle border mb-0 py-2">
                    <strong><?= (int) $transitoActivos ?></strong> en tránsito/despacho
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <?php foreach ($pedidosSeguimiento as $pedido): ?>
                <?php
                $pedidoEstado = (string) ($pedido['estado'] ?? 'pendiente');
                $pedidoEstadoKey = $normalizarEstado($pedidoEstado);
                $pedidoPago = (string) ($pedido['estado_pago'] ?? 'pendiente');
                $esCancelado = $pedidoEstadoKey === 'cancelado';
                $indiceActual = $estadoIndice[$pedidoEstadoKey] ?? 0;
                ?>
                <div class="cp-tracking-card">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="fw-semibold">Pedido #<?= (int) $pedido['id'] ?></div>
                            <div class="text-muted">
                                Cotización <?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : 'sin referencia' ?>
                                · <?= htmlspecialchars((string) ($pedido['fecha'] ?? '-')) ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <div><span class="badge <?= htmlspecialchars($pedidoEstadoBadge($pedidoEstado)) ?> text-capitalize"><?= htmlspecialchars($pedidoEstadoLabel($pedidoEstado)) ?></span></div>
                            <div class="mt-1"><span class="badge <?= htmlspecialchars($pagoEstadoBadge($pedidoPago)) ?> text-capitalize"><?= htmlspecialchars($pedidoPago) ?></span></div>
                            <div class="fw-semibold mt-1"><?= htmlspecialchars($formatCurrency((float) ($pedido['total'] ?? 0))) ?></div>
                        </div>
                    </div>

                    <div class="cp-tracking-line">
                        <?php foreach ($timelineEstados as $estadoKey => $estadoConfig): ?>
                            <?php
                            $estadoId = 'estadoModal-' . (int) $pedido['id'] . '-' . $estadoKey;
                            $stepClass = 'is-pending';
                            if ($esCancelado) {
                                $stepClass = 'is-cancelled';
                            } elseif (($estadoIndice[$estadoKey] ?? 0) < $indiceActual) {
                                $stepClass = 'is-done';
                            } elseif ($estadoKey === $pedidoEstadoKey) {
                                $stepClass = 'is-current';
                            }
                            ?>
                            <button
                                type="button"
                                class="cp-track-point <?= $stepClass ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#<?= htmlspecialchars($estadoId) ?>"
                                title="Ver detalle de <?= htmlspecialchars($estadoConfig['label']) ?>"
                            >
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <iconify-icon icon="<?= htmlspecialchars($estadoConfig['icon']) ?>"></iconify-icon>
                                    <strong><?= htmlspecialchars($estadoConfig['label']) ?></strong>
                                </div>
                                <small class="d-block text-muted">Toca para más detalle</small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($pedidosSeguimiento)): ?>
                <div class="text-center text-muted border rounded py-3 bg-white">No tienes pedidos activos en seguimiento.</div>
            <?php endif; ?>
        </div>

        <?php foreach ($pedidosSeguimiento as $pedido): ?>
            <?php
            $pedidoEstado = (string) ($pedido['estado'] ?? 'pendiente');
            $pedidoEstadoKey = $normalizarEstado($pedidoEstado);
            $pedidoPago = (string) ($pedido['estado_pago'] ?? 'pendiente');
            $esCancelado = $pedidoEstadoKey === 'cancelado';
            $indiceActual = $estadoIndice[$pedidoEstadoKey] ?? 0;
            ?>
            <?php foreach ($timelineEstados as $estadoKey => $estadoConfig): ?>
                <?php
                $estadoId = 'estadoModal-' . (int) $pedido['id'] . '-' . $estadoKey;
                $estatusPaso = 'Pendiente';
                if ($esCancelado) {
                    $estatusPaso = 'Pedido cancelado';
                } elseif (($estadoIndice[$estadoKey] ?? 0) < $indiceActual) {
                    $estatusPaso = 'Completado';
                } elseif ($estadoKey === $pedidoEstadoKey) {
                    $estatusPaso = 'Estado actual';
                }
                ?>
                <div class="modal fade" id="<?= htmlspecialchars($estadoId) ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 class="modal-title mb-0">Pedido #<?= (int) $pedido['id'] ?> · <?= htmlspecialchars($estadoConfig['label']) ?></h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-2"><?= htmlspecialchars($estadoConfig['description']) ?></p>
                                <div class="small border rounded p-2 bg-light">
                                    <div><strong>Estado del punto:</strong> <?= htmlspecialchars($estatusPaso) ?></div>
                                    <div><strong>Estado actual del pedido:</strong> <?= htmlspecialchars($pedidoEstadoLabel($pedidoEstado)) ?></div>
                                    <div><strong>Estado de pago:</strong> <?= htmlspecialchars($pedidoPago) ?></div>
                                    <div><strong>Total pedido:</strong> <?= htmlspecialchars($formatCurrency((float) ($pedido['total'] ?? 0))) ?></div>
                                    <div><strong>Fecha de creación:</strong> <?= htmlspecialchars((string) ($pedido['fecha'] ?? '-')) ?></div>
                                    <div><strong>Cotización asociada:</strong> <?= !empty($pedido['cotizacion_id']) ? ('#' . (int) $pedido['cotizacion_id']) : 'Sin cotización asociada' ?></div>
                                </div>
                            </div>
                            <div class="modal-footer py-2">
                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
