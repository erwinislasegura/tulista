<?php

require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';

class ReporteController
{
    private $reporte ;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('reportes.view');
        $this->reporte = new ReporteModel();
    }

    public function handleRequest(): array
    {
        return [
            'ventas_vendedor' => $this->reporte->ventasPorVendedor(),
            'comisiones_vendedor' => $this->reporte->comisionesPorVendedor(),
        ];
    }
}
