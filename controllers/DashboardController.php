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
        return [
            'kpis' => $this->dashboard->kpis(),
            'top_productos' => $this->dashboard->topProductos(),
            'top_clientes' => $this->dashboard->topClientes(),
            'ventas_mensuales' => $this->dashboard->ventasPorMes(),
            'actividad' => $this->log->recent(8),
        ];
    }
}
