<?php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';

class BodegaController
{
    private Pedido $pedidos;
    private ProductModel $productos;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('bodega.view');
        $this->pedidos = new Pedido();
        $this->productos = new ProductModel();
    }

    public function handleRequest(): array
    {
        $stockQuery = trim($_GET['stock_q'] ?? '');

        return [
            'cotizaciones_aprobadas' => $this->pedidos->cotizacionesAceptadasPendientesBodega(),
            'pedidos' => $this->pedidos->all(),
            'stock_query' => $stockQuery,
            'stock_resultados' => $this->productos->stockLookup($stockQuery),
        ];
    }
}
