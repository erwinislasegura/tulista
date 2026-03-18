<?php

require_once __DIR__ . '/../models/Cotizacion.php';
require_once __DIR__ . '/../models/CotizacionDetalle.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/CompanyConfig.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/AuditService.php';

class CotizacionController
{
    private $cotizaciones ;
    private $detalles ;
    private $productos ;
    private $clientes ;
    private $pedidos ;
    private $empresa ;

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

        if (isset($_GET['download_pdf'])) {
            $this->descargarCotizacionPdfCliente((int) $_GET['download_pdf'], $clienteId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $handled = false;
            if ($action === 'crear_cotizacion') {
                $this->crearCotizacion($clienteId);
                $handled = true;
            } elseif ($action === 'crear_pedido') {
                $this->crearPedidoDesdeCotizacion($clienteId);
                $handled = true;
            } elseif ($action === 'aprobar_cotizacion_cliente') {
                $this->aprobarCotizacionCliente($clienteId);
                $handled = true;
            } elseif ($action === 'eliminar_cotizacion_cliente') {
                $this->eliminarCotizacionCliente($clienteId);
                $handled = true;
            } elseif ($action === 'editar_cotizacion_cliente') {
                $this->editarCotizacionCliente($clienteId);
                $handled = true;
            }

            if ($handled) {
                header('Location: ' . $this->portalReturnUrl());
                exit;
            }
        }

        $flash = $_SESSION['cotizaciones_flash'];
        $_SESSION['cotizaciones_flash'] = [];

        $cotizaciones = $this->cotizaciones->all($clienteId);
        $detallesPorCotizacion = [];
        foreach ($cotizaciones as $cotizacion) {
            $cotizacionId = (int) ($cotizacion['id'] ?? 0);
            if ($cotizacionId > 0) {
                $detallesPorCotizacion[$cotizacionId] = $this->detalles->findByCotizacion($cotizacionId);
            }
        }

        return [
            'productos' => $this->productos->catalog(),
            'cotizaciones' => $cotizaciones,
            'detalles_por_cotizacion' => $detallesPorCotizacion,
            'pedidos' => $this->pedidos->byCliente($clienteId),
            'pedidos_historial' => $this->pedidosHistorialCliente($clienteId),
            'cliente' => $this->clientes->find($clienteId),
            'flash' => $flash,
        ];
    }

    private function aprobarCotizacionCliente(int $clienteId): void
    {
        $cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);
        if ($cotizacionId <= 0) {
            $this->flash('warning', 'Cotización inválida para aprobar.');
            return;
        }

        $cotizacion = $this->cotizaciones->findByIdAndCliente($cotizacionId, $clienteId);
        if (!$cotizacion) {
            $this->flash('warning', 'La cotización no pertenece a tu cuenta.');
            return;
        }

        if (($cotizacion['estado'] ?? '') !== 'enviada') {
            $this->flash('warning', 'Solo las cotizaciones en estado enviada pueden aprobarse desde el portal.');
            return;
        }

        $this->cotizaciones->updateEstado($cotizacionId, 'aprobada');
        $pedido = $this->pedidos->findByCotizacion($cotizacionId);
        if (!$pedido) {
            $this->pedidos->create([
                'cliente_id' => $clienteId,
                'cotizacion_id' => $cotizacionId,
                'usuario_id' => null,
                'estado' => 'pendiente',
                'estado_pago' => 'pendiente',
                'pagado_at' => null,
                'total' => (float) ($cotizacion['total'] ?? 0),
                'fecha' => date('Y-m-d H:i:s'),
                'contacto_nombre' => null,
                'contacto_email' => null,
                'contacto_telefono' => null,
                'direccion_entrega' => null,
                'observaciones' => 'Pedido generado automáticamente al aprobar cotización desde portal cliente.',
            ]);
        }
        AuditService::log('aprobar', 'cotizaciones', $cotizacionId, 'Cotización aprobada por cliente');
        $this->flash('success', 'Cotización #' . $cotizacionId . ' aprobada correctamente. El pedido ya quedó visible para bodega.');
    }

    private function eliminarCotizacionCliente(int $clienteId): void
    {
        $cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);
        if ($cotizacionId <= 0) {
            $this->flash('warning', 'Cotización inválida para eliminar.');
            return;
        }

        $cotizacion = $this->cotizaciones->findByIdAndCliente($cotizacionId, $clienteId);
        if (!$cotizacion) {
            $this->flash('warning', 'La cotización no pertenece a tu cuenta.');
            return;
        }

        $estado = (string) ($cotizacion['estado'] ?? '');
        if (!in_array($estado, ['borrador', 'enviada'], true)) {
            $this->flash('warning', 'Solo puedes eliminar cotizaciones en estado sin revisión o enviada.');
            return;
        }

        $this->detalles->deleteByCotizacion($cotizacionId);
        $this->cotizaciones->delete($cotizacionId);
        AuditService::log('eliminar', 'cotizaciones', $cotizacionId, 'Cotización eliminada por cliente', null, ['cliente_id' => $clienteId]);
        $this->flash('success', 'Cotización #' . $cotizacionId . ' eliminada.');
    }

    private function editarCotizacionCliente(int $clienteId): void
    {
        $cotizacionId = (int) ($_POST['cotizacion_id'] ?? 0);
        $cantidadesRaw = $_POST['cantidades'] ?? [];
        if ($cotizacionId <= 0 || !is_array($cantidadesRaw)) {
            $this->flash('warning', 'Datos inválidos para editar la cotización.');
            return;
        }

        $cotizacion = $this->cotizaciones->findByIdAndCliente($cotizacionId, $clienteId);
        if (!$cotizacion) {
            $this->flash('warning', 'La cotización no pertenece a tu cuenta.');
            return;
        }

        $estado = (string) ($cotizacion['estado'] ?? '');
        if (!in_array($estado, ['borrador', 'enviada'], true)) {
            $this->flash('warning', 'Solo puedes editar cotizaciones en estado sin revisión o enviada.');
            return;
        }

        $detallesActuales = $this->detalles->findByCotizacion($cotizacionId);
        if (empty($detallesActuales)) {
            $this->flash('warning', 'La cotización no tiene ítems para editar.');
            return;
        }

        $nuevosDetalles = [];
        foreach ($detallesActuales as $detalle) {
            $productoId = (int) ($detalle['producto_id'] ?? 0);
            $cantidadNueva = max(0, (int) ($cantidadesRaw[$productoId] ?? 0));
            if ($cantidadNueva <= 0) {
                continue;
            }

            $precio = (float) ($detalle['precio'] ?? 0);
            $descuento = (float) ($detalle['descuento_pct'] ?? 0);
            $bruto = $precio * $cantidadNueva;
            $subtotal = $bruto - ($bruto * ($descuento / 100));

            $nuevosDetalles[] = [
                'producto_id' => $productoId,
                'cantidad' => $cantidadNueva,
                'precio' => $precio,
                'descuento' => $descuento,
                'subtotal' => $subtotal,
            ];
        }

        if (empty($nuevosDetalles)) {
            $this->flash('warning', 'Debes mantener al menos un producto con cantidad mayor a 0.');
            return;
        }

        $this->detalles->deleteByCotizacion($cotizacionId);
        $nuevoTotal = 0;
        foreach ($nuevosDetalles as $item) {
            $this->detalles->create(
                $cotizacionId,
                (int) $item['producto_id'],
                (int) $item['cantidad'],
                (float) $item['precio'],
                (float) $item['descuento'],
                (float) $item['subtotal']
            );
            $nuevoTotal += (float) $item['subtotal'];
        }

        $this->cotizaciones->updateTotal($cotizacionId, $nuevoTotal);
        AuditService::log('editar', 'cotizaciones', $cotizacionId, 'Cotización editada por cliente', null, ['cliente_id' => $clienteId, 'total' => $nuevoTotal]);
        $this->flash('success', 'Cotización #' . $cotizacionId . ' actualizada correctamente.');
    }

    public function handleAdminRequest(): array
    {
        AuthorizationService::requirePermission('cotizaciones.manage');
        $estadoFiltro = (string) ($_GET['estado'] ?? 'todas');
        $estadoSql = $estadoFiltro === 'sin_revision' ? 'borrador' : $estadoFiltro;
        $filtrosPermitidos = ['todas', 'sin_revision', 'enviada', 'aprobada', 'rechazada'];
        if (!in_array($estadoFiltro, $filtrosPermitidos, true)) {
            $estadoFiltro = 'todas';
            $estadoSql = 'todas';
        }

        if (($_GET['ajax'] ?? '') === 'productos') {
            $this->handleProductosAjax();
        }

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
            header('Location: ' . $this->portalReturnUrl());
            exit;
        }

        if (isset($_GET['download_pdf'])) {
            $this->descargarCotizacionPdf((int) $_GET['download_pdf']);
        }

        $flash = $_SESSION['cotizaciones_flash'];
        $_SESSION['cotizaciones_flash'] = [];

        $clientes = $this->clientes->all();
        $cotizaciones = $this->cotizaciones->all();
        if ($estadoSql !== 'todas') {
            $cotizaciones = array_values(array_filter($cotizaciones, static fn ($c) => ($c['estado'] ?? '') === $estadoSql));
        }

        return [
            'cotizaciones' => $cotizaciones,
            'productos' => $this->productos->catalog(),
            'clientes' => $clientes,
            'clientes_json' => json_encode($clientes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'estado_filtro' => $estadoFiltro,
            'flash' => $flash,
        ];
    }

    private function handleProductosAjax(): void
    {
        $term = trim((string) ($_GET['q'] ?? ''));
        $onlyInStock = (($_GET['solo_stock'] ?? '0') === '1');
        $productos = $this->productos->searchCatalog($term, $onlyInStock, 80);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        $payload = json_encode([
            'ok' => true,
            'count' => count($productos),
            'productos' => $productos,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        echo $payload !== false ? $payload : '{"ok":false,"count":0,"productos":[]}';
        exit;
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
        $contacto = [
            'contacto_nombre' => trim($_POST['contacto_nombre'] ?? ''),
            'contacto_email' => trim($_POST['contacto_email'] ?? ''),
            'contacto_telefono' => trim($_POST['contacto_telefono'] ?? ''),
            'direccion_entrega' => trim($_POST['direccion_entrega'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
        ];
        $cotizacionId = $this->cotizaciones->create($clienteId, (int) ($usuario['id'] ?? 0), 0, $contacto);
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
        $this->flash('success', 'Cotización #' . $cotizacionId . ' creada exitosamente.');
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

    private function descargarCotizacionPdfCliente(int $cotizacionId, int $clienteId): void
    {
        if ($cotizacionId <= 0 || $clienteId <= 0) {
            http_response_code(400);
            exit('Cotización inválida.');
        }

        $cotizacion = $this->cotizaciones->findByIdAndCliente($cotizacionId, $clienteId);
        if (!$cotizacion) {
            http_response_code(404);
            exit('Cotización no encontrada para este cliente.');
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
        $escape = static function (string $text): string {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace('(', '\\(', $text);
            return str_replace(')', '\\)', $text);
        };

        $companyName = (string) ($empresa['nombre'] ?? 'TU LISTA ERP');
        $logoPath = $this->resolveLogoAbsolutePath((string) ($empresa['logo_path'] ?? ''));
        $logo = $this->preparePdfImage($logoPath);
        $hasLogo = !empty($logo);

        $totalBruto = 0;
        foreach ($detalles as $detalle) {
            $cantidad = (int) ($detalle['cantidad'] ?? 0);
            $precio = (float) ($detalle['precio'] ?? 0);
            $totalBruto += $cantidad * $precio;
        }

        $total = (float) ($cotizacion['total'] ?? 0);
        $descuentoGlobal = max(0, $totalBruto - $total);
        $iva = $total * 0.19;
        $totalConIva = $total + $iva;

        $toPdfText = static function (string $text): string {
            $text = trim(str_replace(["\r", "\n"], ' ', $text));
            if (function_exists('iconv')) {
                $encoded = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
                if ($encoded !== false) {
                    return $encoded;
                }
            }
            return $text;
        };

        $textWidth = static function (string $text, float $fontSize = 8.0): float {
            $len = strlen($text);
            return $len * ($fontSize * 0.48);
        };

        $roundedRect = static function (float $x, float $y, float $w, float $h, float $r = 6): string {
            $k = 0.5522847498;
            $c = $r * $k;
            $x2 = $x + $w;
            $y2 = $y + $h;
            return sprintf(
                "%.2f %.2f m %.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c %.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c %.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c %.2f %.2f l %.2f %.2f %.2f %.2f %.2f %.2f c S",
                $x + $r, $y,
                $x2 - $r, $y,
                $x2 - $r + $c, $y, $x2, $y + $r - $c, $x2, $y + $r,
                $x2, $y2 - $r,
                $x2, $y2 - $r + $c, $x2 - $r + $c, $y2, $x2 - $r, $y2,
                $x + $r, $y2,
                $x + $r - $c, $y2, $x, $y2 - $r + $c, $x, $y2 - $r,
                $x, $y + $r,
                $x, $y + $r - $c, $x + $r - $c, $y, $x + $r, $y
            );
        };

        $commands = [];
        $commands[] = "1 1 1 rg 0 0 595 842 re f";
        $commands[] = "0 g 0 G";
        $commands[] = "0 0 0 RG 0.45 w";
        $commands[] = $roundedRect(24, 24, 547, 794, 8);

        $empresaDisplay = trim((string) ($empresa['nombre'] ?? ''));
        if ($empresaDisplay === '') {
            $empresaDisplay = trim((string) ($empresa['razon_social'] ?? 'Empresa no configurada'));
        }
        $fechaEmision = (string) ($cotizacion['fecha'] ?? date('Y-m-d'));
        $fechaValidaHasta = date('d/m/Y', strtotime(($fechaEmision ?: date('Y-m-d')) . ' +10 days'));
        $fechaPdf = date('d/m/Y', strtotime($fechaEmision ?: 'now'));

        // HEADER en formato plantilla
        if ($hasLogo) {
            $commands[] = "q 38 0 0 38 28 760 cm /Im1 Do Q";
        }
        $headerX = $hasLogo ? 74 : 30;
        $commands[] = "BT /F2 18 Tf {$headerX} 790 Td (" . $escape($toPdfText($empresaDisplay)) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf {$headerX} 776 Td (" . $escape($toPdfText((string) ($empresa['direccion'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf {$headerX} 765 Td (" . $escape($toPdfText('Tel: ' . ($empresa['telefono'] ?? '-') . '   |   Email: ' . ($empresa['email'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F2 24 Tf 410 790 Td (" . $escape('Cotizacion') . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 410 775 Td (" . $escape($toPdfText('Fecha: ' . $fechaPdf)) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 410 764 Td (" . $escape($toPdfText('N° Cotizacion: ' . (int) ($cotizacion['id'] ?? 0))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 410 753 Td (" . $escape($toPdfText('ID Cliente: ' . (int) ($cotizacion['cliente_id'] ?? 0))) . ") Tj ET";

        // CLIENTE
        $commands[] = "0 0 0 RG 0.35 w 24 740 m 571 740 l S";
        $commands[] = "BT /F2 9 Tf 30 724 Td (" . $escape('Cotizacion para:') . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 30 712 Td (" . $escape($toPdfText((string) ($cotizacion['cliente_nombre'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 30 701 Td (" . $escape($toPdfText((string) ($cotizacion['cliente_empresa'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 30 690 Td (" . $escape($toPdfText((string) ($cotizacion['cliente_direccion'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 30 679 Td (" . $escape($toPdfText('Telefono: ' . (string) ($cotizacion['cliente_telefono'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 360 724 Td (" . $escape($toPdfText('Valida hasta: ' . $fechaValidaHasta)) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 360 712 Td (" . $escape($toPdfText('Preparada por: ' . (string) ($cotizacion['vendedor'] ?? 'Ventas'))) . ") Tj ET";

        // Comentarios
        $commands[] = "BT /F2 8 Tf 30 658 Td (" . $escape('Comentarios:') . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 92 658 Td (" . $escape($toPdfText((string) ($cotizacion['observaciones'] ?? 'Ninguno'))) . ") Tj ET";

        // INFO BAR
        $infoTop = 638;
        $commands[] = "0 g 0 G";
        $commands[] = "0 0 0 RG 0.35 w";
        $commands[] = $roundedRect(24, $infoTop - 18, 547, 34, 3);
        $commands[] = "BT /F2 7 Tf 30 " . ($infoTop + 5) . " Td (" . $escape('VENDEDOR') . ") Tj ET";
        $commands[] = "BT /F2 7 Tf 118 " . ($infoTop + 5) . " Td (" . $escape('N° O/C') . ") Tj ET";
        $commands[] = "BT /F2 7 Tf 190 " . ($infoTop + 5) . " Td (" . $escape('FECHA ENVIO') . ") Tj ET";
        $commands[] = "BT /F2 7 Tf 292 " . ($infoTop + 5) . " Td (" . $escape('ENVIO') . ") Tj ET";
        $commands[] = "BT /F2 7 Tf 360 " . ($infoTop + 5) . " Td (" . $escape('PUNTO F.O.B') . ") Tj ET";
        $commands[] = "BT /F2 7 Tf 468 " . ($infoTop + 5) . " Td (" . $escape('TERMINOS') . ") Tj ET";
        $commands[] = "0 0 0 RG 0.25 w 24 " . ($infoTop - 2) . " m 571 " . ($infoTop - 2) . " l S";
        $commands[] = "BT /F1 8 Tf 30 " . ($infoTop - 13) . " Td (" . $escape($toPdfText((string) ($cotizacion['vendedor'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 468 " . ($infoTop - 13) . " Td (" . $escape('Contado') . ") Tj ET";

        // DETALLE TABLA estilo plantilla
        $tableTop = 594;
        $commands[] = "0 g 0 G";
        $commands[] = "0 0 0 RG 0.35 w";
        $commands[] = $roundedRect(24, $tableTop - 240, 547, 258, 4);
        $commands[] = "0 0 0 RG 0.3 w 24 {$tableTop} 547 18 re S";
        $commands[] = "BT /F2 8 Tf 32 " . ($tableTop + 6) . " Td (" . $escape('CANTIDAD') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 106 " . ($tableTop + 6) . " Td (" . $escape('DESCRIPCION') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 365 " . ($tableTop + 6) . " Td (" . $escape('PRECIO UNIT.') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 450 " . ($tableTop + 6) . " Td (" . $escape('IMPUESTO') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 520 " . ($tableTop + 6) . " Td (" . $escape('MONTO') . ") Tj ET";

        $rowY = $tableTop - 14;
        $maxRows = 12;
        foreach (array_slice($detalles, 0, $maxRows) as $detalle) {
            $cantidad = (int) ($detalle['cantidad'] ?? 0);
            $precio = (float) ($detalle['precio'] ?? 0);
            $subtotal = (float) ($detalle['subtotal'] ?? 0);
            $impuesto = $subtotal * 0.19;
            $descripcionRaw = (string) ($detalle['producto_nombre'] ?? '-');
            $descripcion = $toPdfText($descripcionRaw);
            $descFont = 8.0;
            while ($descFont > 6.6 && $textWidth($descripcion, $descFont) > 246) {
                $descFont -= 0.2;
            }
            if ($textWidth($descripcion, $descFont) > 250) {
                $descripcion = substr($descripcion, 0, 52) . '...';
            }

            $cantidadText = number_format($cantidad, 0, ',', '.');
            $precioText = $toPdfText('$' . number_format($precio, 0, ',', '.'));
            $impuestoText = $toPdfText('$' . number_format($impuesto, 0, ',', '.'));
            $subtotalText = $toPdfText('$' . number_format($subtotal, 0, ',', '.'));

            $cantidadX = 84 - $textWidth($cantidadText, 8);
            $precioX = 438 - $textWidth($precioText, 8);
            $impuestoX = 500 - $textWidth($impuestoText, 8);
            $subtotalX = 568 - $textWidth($subtotalText, 8);

            $commands[] = "0 0 0 RG 0.15 w 24 " . ($rowY - 10) . " 547 18 re S";
            $commands[] = "BT /F1 8 Tf {$cantidadX} {$rowY} Td (" . $escape($cantidadText) . ") Tj ET";
            $commands[] = "BT /F1 {$descFont} Tf 106 {$rowY} Td (" . $escape($descripcion) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf {$precioX} {$rowY} Td (" . $escape($precioText) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf {$impuestoX} {$rowY} Td (" . $escape($impuestoText) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf {$subtotalX} {$rowY} Td (" . $escape($subtotalText) . ") Tj ET";
            $rowY -= 18;
        }

        // TOTALES
        $totalsX = 380;
        $totalsY = 130;
        $commands[] = "0 g 0 G";
        $commands[] = "0 0 0 RG 0.35 w";
        $commands[] = $roundedRect($totalsX, $totalsY, 191, 54, 4);
        $commands[] = "0 0 0 RG 0.2 w {$totalsX} " . ($totalsY + 36) . " m " . ($totalsX + 191) . " " . ($totalsY + 36) . " l S";
        $commands[] = "0 0 0 RG 0.2 w {$totalsX} " . ($totalsY + 18) . " m " . ($totalsX + 191) . " " . ($totalsY + 18) . " l S";
        $commands[] = "BT /F1 8 Tf " . ($totalsX + 8) . " " . ($totalsY + 40) . " Td (" . $escape('SUBTOTAL') . ") Tj ET";
        $commands[] = "BT /F1 8 Tf " . ($totalsX + 8) . " " . ($totalsY + 22) . " Td (" . $escape('IVA (19%)') . ") Tj ET";
        $commands[] = "BT /F2 9 Tf " . ($totalsX + 8) . " " . ($totalsY + 6) . " Td (" . $escape('TOTAL') . ") Tj ET";
        $subtotalTotalText = $toPdfText('$' . number_format($total, 0, ',', '.'));
        $ivaTotalText = $toPdfText('$' . number_format($iva, 0, ',', '.'));
        $grandTotalText = $toPdfText('$' . number_format($totalConIva, 0, ',', '.'));
        $commands[] = "BT /F1 8 Tf " . (($totalsX + 184) - $textWidth($subtotalTotalText, 8)) . " " . ($totalsY + 40) . " Td (" . $escape($subtotalTotalText) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf " . (($totalsX + 184) - $textWidth($ivaTotalText, 8)) . " " . ($totalsY + 22) . " Td (" . $escape($ivaTotalText) . ") Tj ET";
        $commands[] = "BT /F2 9 Tf " . (($totalsX + 184) - $textWidth($grandTotalText, 9)) . " " . ($totalsY + 6) . " Td (" . $escape($grandTotalText) . ") Tj ET";

        // Footer
        $commands[] = "BT /F1 8 Tf 150 60 Td (" . $escape($toPdfText('Si tiene alguna consulta sobre esta cotizacion, contactenos.')) . ") Tj ET";
        $commands[] = "BT /F2 9 Tf 220 46 Td (" . $escape($toPdfText('¡GRACIAS POR SU COMPRA!')) . ") Tj ET";

        $content = implode("\n", $commands);
        return $this->renderPdfDocument($content, $logo);
    }

    private function resolveLogoAbsolutePath(string $logoPath): ?string
    {
        $path = trim($logoPath);
        if ($path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return null;
        }

        if (str_starts_with($path, '/')) {
            return is_file($path) ? $path : null;
        }

        $absolute = realpath(__DIR__ . '/../' . ltrim($path, '/'));
        return $absolute && is_file($absolute) ? $absolute : null;
    }

    private function preparePdfImage(?string $absolutePath): ?array
    {
        if (!$absolutePath || !is_file($absolutePath)) {
            return null;
        }

        $raw = @file_get_contents($absolutePath);
        if ($raw === false) {
            return null;
        }

        $ext = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
        if ($ext === 'jpg' || $ext === 'jpeg') {
            $size = @getimagesize($absolutePath);
            if (!$size) {
                return null;
            }
            return ['data' => $raw, 'width' => (int) $size[0], 'height' => (int) $size[1], 'filter' => '/DCTDecode', 'color' => '/DeviceRGB', 'bits' => 8];
        }

        if ($ext === 'png' && function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($raw);
            if (!$img) {
                return null;
            }

            ob_start();
            imagejpeg($img, null, 90);
            $jpegData = (string) ob_get_clean();
            $width = imagesx($img);
            $height = imagesy($img);
            imagedestroy($img);

            return ['data' => $jpegData, 'width' => $width, 'height' => $height, 'filter' => '/DCTDecode', 'color' => '/DeviceRGB', 'bits' => 8];
        }

        return null;
    }

    private function renderPdfDocument(string $content, ?array $logo = null): string
    {
        $contentLen = strlen($content);
        $objects = [];
        $hasLogo = !empty($logo);

        $objects[1] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
        $objects[2] = "2 0 obj << /Type /Pages /Count 1 /Kids [3 0 R] >> endobj";

        $xObject = $hasLogo ? "/XObject << /Im1 6 0 R >>" : '';
        $objects[3] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> {$xObject} >> /Contents " . ($hasLogo ? "7 0 R" : "6 0 R") . " >> endobj";
        $objects[4] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
        $objects[5] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj";

        if ($hasLogo) {
            $imgData = $logo['data'];
            $imgLen = strlen($imgData);
            $objects[6] = "6 0 obj << /Type /XObject /Subtype /Image /Width {$logo['width']} /Height {$logo['height']} /ColorSpace {$logo['color']} /BitsPerComponent {$logo['bits']} /Filter {$logo['filter']} /Length {$imgLen} >> stream\n{$imgData}\nendstream endobj";
            $objects[7] = "7 0 obj << /Length {$contentLen} >> stream\n{$content}\nendstream endobj";
            $lastObj = 7;
        } else {
            $objects[6] = "6 0 obj << /Length {$contentLen} >> stream\n{$content}\nendstream endobj";
            $lastObj = 6;
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        for ($i = 1; $i <= $lastObj; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $objects[$i] . "\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . ($lastObj + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $lastObj; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }

        $pdf .= "trailer << /Size " . ($lastObj + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";
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

        if ($cotizacion['estado'] !== 'aprobada') {
            $this->flash('warning', 'Solo cotizaciones aprobadas pueden convertirse en pedido.');
            return;
        }

        if ($this->pedidos->findByCotizacion($cotizacionId)) {
            $this->flash('warning', 'La cotización ya tiene un pedido asociado.');
            return;
        }

        $usuario = AuthService::user();
        $this->pedidos->create([
            'cliente_id' => $clienteId,
            'cotizacion_id' => $cotizacionId,
            'usuario_id' => (int) ($usuario['id'] ?? 0) ?: null,
            'estado' => 'pendiente',
            'estado_pago' => 'pendiente',
            'pagado_at' => null,
            'total' => (float) $cotizacion['total'],
            'fecha' => date('Y-m-d H:i:s'),
            'contacto_nombre' => null,
            'contacto_email' => null,
            'contacto_telefono' => null,
            'direccion_entrega' => null,
            'observaciones' => 'Pedido generado desde cotización aprobada.',
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

    private function pedidosHistorialCliente(int $clienteId): array
    {
        return array_values(array_filter(
            $this->pedidos->byCliente($clienteId),
            static fn (array $pedido): bool => in_array(($pedido['estado'] ?? ''), ['entregado', 'cancelado'], true) || ($pedido['estado_pago'] ?? '') === 'pagado'
        ));
    }
}
