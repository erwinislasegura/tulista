<?php

require_once __DIR__ . '/../models/Cotizacion.php';
require_once __DIR__ . '/../models/CotizacionDetalle.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/CompanyConfig.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuditService.php';

class CotizacionController
{
    private Cotizacion $cotizaciones;
    private CotizacionDetalle $detalles;
    private ProductModel $productos;
    private Cliente $clientes;
    private Pedido $pedidos;
    private CompanyConfig $empresa;

    public function __construct()
    {
        AuthService::startSession();
        $this->cotizaciones = new Cotizacion();
        $this->detalles = new CotizacionDetalle();
        $this->productos = new ProductModel();
        $this->clientes = new Cliente();
        $this->pedidos = new Pedido();
        $this->empresa = new CompanyConfig();
        $_SESSION['cotizaciones_flash'] = $_SESSION['cotizaciones_flash'] ?? [];
    }

    public function handlePortalRequest(?int $forcedClienteId = null): array
    {
        $cliente = AuthService::cliente();
        $clienteId = $forcedClienteId ?: ($cliente['id'] ?? 0);
        if ($clienteId <= 0) {
            http_response_code(403);
            exit('Debes iniciar sesión como cliente.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'crear_cotizacion') {
                $this->crearCotizacion($clienteId);
            } elseif ($action === 'crear_pedido') {
                $this->crearPedidoDesdeCotizacion($clienteId);
            }
            header('Location: ' . $this->portalReturnUrl());
            exit;
        }

        $flash = $_SESSION['cotizaciones_flash'];
        $_SESSION['cotizaciones_flash'] = [];

        return [
            'productos' => $this->productos->catalog(),
            'cotizaciones' => $this->cotizaciones->all($clienteId),
            'pedidos' => $this->pedidos->byCliente($clienteId),
            'cliente' => $this->clientes->find($clienteId),
            'flash' => $flash,
        ];
    }

    public function handleAdminRequest(): array
    {
        AuthService::requireRole(['admin', 'supervisor', 'vendedor']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'cambiar_estado') {
                $id = (int) ($_POST['cotizacion_id'] ?? 0);
                $estado = $_POST['estado'] ?? 'borrador';
                $allowed = ['borrador', 'enviada', 'aprobada', 'rechazada'];
                if ($id > 0 && in_array($estado, $allowed, true)) {
                    $this->cotizaciones->updateEstado($id, $estado);
                    AuditService::log('editar', 'cotizaciones', $id, "Cotización actualizada a estado {$estado}");
                }
            } elseif ($action === 'crear_admin') {
                $this->crearCotizacion((int) ($_POST['cliente_id'] ?? 0));
            } elseif ($action === 'eliminar') {
                $id = (int) ($_POST['cotizacion_id'] ?? 0);
                if ($id > 0) {
                    $this->cotizaciones->delete($id);
                    AuditService::log('eliminar', 'cotizaciones', $id, 'Cotización eliminada');
                }
            }
            header('Location: apps-cotizaciones.php');
            exit;
        }

        if (isset($_GET['download_pdf'])) {
            $this->descargarCotizacionPdf((int) $_GET['download_pdf']);
        }

        $flash = $_SESSION['cotizaciones_flash'];
        $_SESSION['cotizaciones_flash'] = [];

        $clientes = $this->clientes->all();
        return [
            'cotizaciones' => $this->cotizaciones->all(),
            'productos' => $this->productos->catalog(),
            'clientes' => $clientes,
            'clientes_json' => json_encode($clientes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'flash' => $flash,
        ];
    }

    private function crearCotizacion(int $clienteId): void
    {
        $items = $_POST['items'] ?? [];
        if ($clienteId <= 0) {
            $this->flash('warning', 'Debes seleccionar un cliente.');
            return;
        }
        if (!is_array($items) || empty($items)) {
            $this->flash('warning', 'Debes agregar al menos un producto.');
            return;
        }

        $usuario = AuthService::user();
        $cotizacionId = $this->cotizaciones->create($clienteId, (int) ($usuario['id'] ?? 0), 0);
        $productos = $this->productos->catalogByIds(array_map('intval', array_keys($items)));
        $total = 0;
        $lineas = 0;

        foreach ($productos as $producto) {
            $pid = (int) $producto['id'];
            $item = $items[$pid] ?? [];
            if (!is_array($item)) {
                continue;
            }

            $cantidad = (int) ($item['cantidad'] ?? 0);
            $descuentoPct = max(0, min(100, (float) ($item['descuento'] ?? 0)));
            if ($cantidad <= 0) {
                continue;
            }

            $precio = (float) $producto['precio_venta_total'];
            $bruto = $precio * $cantidad;
            $montoDescuento = $bruto * ($descuentoPct / 100);
            $subtotal = $bruto - $montoDescuento;

            $this->detalles->create($cotizacionId, $pid, $cantidad, $precio, $descuentoPct, $subtotal);
            $total += $subtotal;
            $lineas++;
        }

        if ($lineas === 0) {
            $this->cotizaciones->delete($cotizacionId);
            $this->flash('warning', 'Debes indicar cantidades mayores a 0 para generar una cotización.');
            return;
        }

        $this->cotizaciones->updateTotal($cotizacionId, $total);
        AuditService::log('crear', 'cotizaciones', $cotizacionId, 'Cotización creada', null, ['cliente_id' => $clienteId, 'total' => $total]);
        $this->flash('success', 'Cotización creada exitosamente.');
    }

    private function descargarCotizacionPdf(int $cotizacionId): void
    {
        if ($cotizacionId <= 0) {
            http_response_code(400);
            exit('Cotización inválida.');
        }

        $cotizacion = $this->cotizaciones->findById($cotizacionId);
        if (!$cotizacion) {
            http_response_code(404);
            exit('Cotización no encontrada.');
        }

        $detalles = $this->detalles->findByCotizacion($cotizacionId);
        $empresa = $this->empresa->get() ?: [];
        $pdf = $this->buildCorporatePdf($cotizacion, $detalles, $empresa);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="cotizacion-' . $cotizacionId . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    private function buildCorporatePdf(array $cotizacion, array $detalles, array $empresa): string
    {
        $lineas = [];
        $lineas[] = strtoupper($empresa['nombre'] ?? 'TU LISTA ERP');
        $lineas[] = 'Razon social: ' . ($empresa['razon_social'] ?? 'No configurada');
        $lineas[] = 'RUT: ' . ($empresa['rut'] ?? 'No configurado') . ' | Email: ' . ($empresa['email'] ?? 'No configurado');
        $lineas[] = 'Telefono: ' . ($empresa['telefono'] ?? '-') . ' | Web: ' . ($empresa['sitio_web'] ?? '-');
        $lineas[] = str_repeat('-', 110);
        $lineas[] = 'COTIZACION #' . (int) $cotizacion['id'] . '   Fecha: ' . $cotizacion['fecha'] . '   Estado: ' . strtoupper((string) $cotizacion['estado']);
        $lineas[] = 'Cliente: ' . ($cotizacion['cliente_nombre'] ?? '-') . '  RUT: ' . ($cotizacion['cliente_rut'] ?? '-');
        $lineas[] = 'Empresa cliente: ' . ($cotizacion['cliente_empresa'] ?? '-') . '  Contacto: ' . ($cotizacion['cliente_email'] ?? '-') . ' / ' . ($cotizacion['cliente_telefono'] ?? '-');
        $lineas[] = 'Direccion: ' . ($cotizacion['cliente_direccion'] ?? '-');
        $lineas[] = 'Ejecutivo: ' . ($cotizacion['vendedor'] ?? 'Sin asignar');
        $lineas[] = str_repeat('-', 110);
        $lineas[] = sprintf('%-6s %-34s %-10s %8s %12s %8s %14s', 'SKU', 'Producto', 'Cantidad', 'Precio', 'Bruto', 'Desc%', 'Subtotal');
        $lineas[] = str_repeat('-', 110);

        $totalBruto = 0;
        foreach ($detalles as $detalle) {
            $cantidad = (int) $detalle['cantidad'];
            $precio = (float) $detalle['precio'];
            $descuento = (float) ($detalle['descuento_pct'] ?? 0);
            $subtotal = (float) $detalle['subtotal'];
            $bruto = $precio * $cantidad;
            $totalBruto += $bruto;

            $lineas[] = sprintf(
                '%-6s %-34s %10s %12s %12s %8s %14s',
                substr((string) ($detalle['sku'] ?? ''), 0, 6),
                substr((string) ($detalle['producto_nombre'] ?? ''), 0, 34),
                number_format($cantidad, 0, ',', '.'),
                '$' . number_format($precio, 0, ',', '.'),
                '$' . number_format($bruto, 0, ',', '.'),
                number_format($descuento, 2, ',', '.') . '%',
                '$' . number_format($subtotal, 0, ',', '.')
            );
        }

        $total = (float) $cotizacion['total'];
        $descuentoGlobal = $totalBruto - $total;
        $iva = $total * 0.19;
        $totalConIva = $total + $iva;

        $lineas[] = str_repeat('-', 110);
        $lineas[] = 'Subtotal bruto: $' . number_format($totalBruto, 0, ',', '.');
        $lineas[] = 'Descuentos aplicados: $' . number_format($descuentoGlobal, 0, ',', '.');
        $lineas[] = 'Neto cotizado: $' . number_format($total, 0, ',', '.');
        $lineas[] = 'IVA (19%): $' . number_format($iva, 0, ',', '.');
        $lineas[] = 'TOTAL FINAL: $' . number_format($totalConIva, 0, ',', '.');
        $lineas[] = str_repeat('-', 110);
        $lineas[] = 'Condiciones: Validez 10 dias | Entrega sujeta a stock | Valores en CLP.';
        $lineas[] = 'Documento generado por TU LISTA ERP.';

        return $this->renderSimplePdfFromLines($lineas);
    }

    private function renderSimplePdfFromLines(array $lines): string
    {
        $escape = static function (string $text): string {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace('(', '\\(', $text);
            return str_replace(')', '\\)', $text);
        };

        $content = "BT\n/F1 10 Tf\n40 800 Td\n";
        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -14 Td\n";
            }
            $content .= '(' . $escape($line) . ") Tj\n";
        }
        $content .= "ET";

        $len = strlen($content);
        $objs = [];
        $objs[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objs[] = "2 0 obj << /Type /Pages /Count 1 /Kids [3 0 R] >> endobj";
        $objs[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
        $objs[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Courier >> endobj";
        $objs[] = "5 0 obj << /Length {$len} >> stream\n{$content}\nendstream endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objs as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj . "\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }

        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";
        return $pdf;
    }

    private function crearPedidoDesdeCotizacion(int $clienteId): void
    {
        $cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);
        if ($cotizacionId <= 0) {
            $this->flash('warning', 'Selecciona una cotización para generar el pedido.');
            return;
        }

        $cotizacion = $this->cotizaciones->findByIdAndCliente($cotizacionId, $clienteId);
        if (!$cotizacion) {
            $this->flash('danger', 'Cotización no encontrada para el cliente.');
            return;
        }

        if (!in_array($cotizacion['estado'], ['aprobada', 'enviada'], true)) {
            $this->flash('warning', 'Solo cotizaciones enviadas o aprobadas pueden convertirse en pedido.');
            return;
        }

        $usuario = AuthService::user();
        $this->pedidos->create([
            'cliente_id' => $clienteId,
            'cotizacion_id' => $cotizacionId,
            'usuario_id' => (int) ($usuario['id'] ?? 0) ?: null,
            'estado' => 'pendiente',
            'total' => (float) $cotizacion['total'],
            'fecha' => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Pedido creado desde la cotización #' . $cotizacionId . '.');
    }

    private function portalReturnUrl(): string
    {
        $url = trim($_POST['return_url'] ?? 'cliente-portal.php');
        return $url !== '' ? $url : 'cliente-portal.php';
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['cotizaciones_flash'][] = ['type' => $type, 'message' => $message];
    }
}
