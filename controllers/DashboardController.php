<?php

require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/../models/LogSistema.php';
require_once __DIR__ . '/../services/AuthService.php';

class DashboardController
{
    public function __construct()
    {
        AuthService::startSession();
        if (!AuthService::user()) {
            header('Location: auth-signin.php');
            exit;
        }
    }

    public function handleRequest(): array
    {
        try {
            $dashboard = new DashboardModel();
            $log = new LogSistema();

            return [
                'kpis' => $dashboard->kpis(),
                'top_productos' => $dashboard->topProductos(),
                'top_clientes' => $dashboard->topClientes(),
                'ventas_mensuales' => $dashboard->ventasPorMes(),
                'actividad' => $log->recent(8),
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
