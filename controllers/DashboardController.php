<?php

require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/LogSistema.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';

class DashboardController
{
    private DashboardModel $dashboard;
    private LogSistema $log;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('dashboard.view');
        $this->dashboard = new DashboardModel();
        $this->log = new LogSistema();
    }

    public function handleRequest(): array
    {
        try {
            return [
                'kpis' => $this->dashboard->kpis(),
                'top_productos' => $this->dashboard->topProductos(),
                'top_clientes' => $this->dashboard->topClientes(),
                'ventas_mensuales' => $this->dashboard->ventasPorMes(),
                'actividad' => $this->log->recent(8),
            ];
        } catch (Throwable $e) {
            return [
                'kpis' => [
                    'ventas_dia' => 0,
                    'ventas_mes' => 0,
                    'ganancia_mes' => 0,
                    'comisiones_mes' => 0,
                    'cotizaciones_pendientes' => 0,
                    'pedidos_proceso' => 0,
                    'stock_bajo' => 0,
                    'clientes_nuevos' => 0,
                ],
                'top_productos' => [],
                'top_clientes' => [],
                'ventas_mensuales' => [],
                'actividad' => [],
            ];
        }
    }
}
