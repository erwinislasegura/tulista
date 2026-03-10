<?php

require_once __DIR__ . '/../models/ReporteModel.php';
require_once __DIR__ . '/../services/AuthService.php';

class ReporteController
{
    private ReporteModel $reporte;

    public function __construct()
    {
        AuthService::startSession();
        AuthService::requireRole(['admin', 'supervisor']);
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
