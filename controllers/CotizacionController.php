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

        $commands = [];
        $commands[] = "1 1 1 rg 0 0 595 842 re f";
        $commands[] = "0.25 0.53 0.70 RG 1.4 w 18 18 559 806 re S";

        // Encabezado
        $commands[] = "0.95 0.97 0.99 rg 18 760 559 64 re f";
        if ($hasLogo) {
            $commands[] = "q 46 0 0 46 30 772 cm /Im1 Do Q";
        }
        $headerStartX = $hasLogo ? 86 : 30;
        $commands[] = "0.12 0.27 0.45 rg";
        $commands[] = "BT /F2 15 Tf {$headerStartX} 803 Td (" . $escape($toPdfText($companyName)) . ") Tj ET";
        $commands[] = "0 0 0 rg";
        $commands[] = "BT /F1 8 Tf {$headerStartX} 789 Td (" . $escape($toPdfText('RUT: ' . ($empresa['rut'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf {$headerStartX} 777 Td (" . $escape($toPdfText('Email: ' . ($empresa['email'] ?? '-') . '  |  Tel: ' . ($empresa['telefono'] ?? '-'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf {$headerStartX} 765 Td (" . $escape($toPdfText('Direccion: ' . ($empresa['direccion'] ?? '-'))) . ") Tj ET";

        // Caja de cotización derecha
        $commands[] = "0.25 0.53 0.70 RG 2 w 340 770 228 50 re S";
        $commands[] = "BT /F2 12 Tf 407 803 Td (" . $escape($toPdfText('COTIZACION')) . ") Tj ET";
        $commands[] = "BT /F1 9 Tf 356 788 Td (" . $escape($toPdfText('Folio N° ' . (int) ($cotizacion['id'] ?? 0))) . ") Tj ET";
        $commands[] = "BT /F1 9 Tf 356 776 Td (" . $escape($toPdfText('Fecha: ' . ((string) ($cotizacion['fecha'] ?? '-')))) . ") Tj ET";
        $commands[] = "BT /F1 9 Tf 356 764 Td (" . $escape($toPdfText('Estado: ' . strtoupper((string) ($cotizacion['estado'] ?? '-')))) . ") Tj ET";

        // Bloque de información
        $commands[] = "0.25 0.53 0.70 RG 1.2 w 18 590 559 160 re S";
        $commands[] = "0.96 0.98 0.99 rg 20 724 555 24 re f";
        $commands[] = "BT /F2 9 Tf 30 731 Td (" . $escape($toPdfText($cotizacion['cliente_empresa'] ?? 'Cliente sin empresa')) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 30 716 Td (" . $escape($toPdfText('Cliente: ' . ($cotizacion['cliente_nombre'] ?? '-') . ' | RUT: ' . ($cotizacion['cliente_rut'] ?? '-'))) . ") Tj ET";

        $leftRows = [
            ['Contacto', $cotizacion['cliente_email'] ?? '-'],
            ['Fono', $cotizacion['cliente_telefono'] ?? '-'],
            ['Direccion', $cotizacion['cliente_direccion'] ?? '-'],
            ['Condicion', 'Validez 10 dias habiles'],
        ];
        $rightRows = [
            ['Ejecutivo', $cotizacion['vendedor'] ?? 'Sin asignar'],
            ['Emision', (string) ($cotizacion['fecha'] ?? '-')],
            ['Moneda', 'CLP'],
            ['Pago', 'Contra orden de compra'],
        ];

        $infoY = 694;
        foreach ($leftRows as $i => $row) {
            $rowY = $infoY - ($i * 22);
            $commands[] = "0.92 0.95 0.97 rg 20 " . ($rowY - 14) . " 272 20 re f";
            $commands[] = "BT /F2 8 Tf 26 {$rowY} Td (" . $escape($toPdfText($row[0] . ':')) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf 96 {$rowY} Td (" . $escape($toPdfText((string) $row[1])) . ") Tj ET";
        }
        foreach ($rightRows as $i => $row) {
            $rowY = $infoY - ($i * 22);
            $commands[] = "0.95 0.97 0.99 rg 304 " . ($rowY - 14) . " 271 20 re f";
            $commands[] = "BT /F2 8 Tf 310 {$rowY} Td (" . $escape($toPdfText($row[0] . ':')) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf 384 {$rowY} Td (" . $escape($toPdfText((string) $row[1])) . ") Tj ET";
        }

        // Tabla detalle
        $tableTop = 560;
        $commands[] = "0.25 0.53 0.70 RG 1 w 18 150 559 410 re S";
        $commands[] = "0.25 0.53 0.70 rg 18 {$tableTop} 559 22 re f";
        $commands[] = "1 1 1 rg";
        $commands[] = "BT /F2 8 Tf 26 " . ($tableTop + 7) . " Td (" . $escape('Detalle') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 350 " . ($tableTop + 7) . " Td (" . $escape('Cant') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 390 " . ($tableTop + 7) . " Td (" . $escape('Uni.') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 430 " . ($tableTop + 7) . " Td (" . $escape('% Desc') . ") Tj ET";
        $commands[] = "BT /F2 8 Tf 490 " . ($tableTop + 7) . " Td (" . $escape('Total') . ") Tj ET";

        $rowY = $tableTop - 16;
        $maxRows = 14;
        foreach (array_slice($detalles, 0, $maxRows) as $index => $detalle) {
            $commands[] = ($index % 2 === 0)
                ? "0.98 0.99 1 rg 20 " . ($rowY - 10) . " 555 16 re f"
                : "1 1 1 rg 20 " . ($rowY - 10) . " 555 16 re f";

            $cantidad = (int) ($detalle['cantidad'] ?? 0);
            $precio = (float) ($detalle['precio'] ?? 0);
            $descuento = (float) ($detalle['descuento_pct'] ?? 0);
            $subtotal = (float) ($detalle['subtotal'] ?? 0);
            $nombre = substr((string) ($detalle['producto_nombre'] ?? '-'), 0, 58);

            $commands[] = "0 0 0 rg";
            $commands[] = "BT /F1 8 Tf 26 {$rowY} Td (" . $escape($toPdfText($nombre)) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf 352 {$rowY} Td (" . $escape(number_format($cantidad, 0, ',', '.')) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf 390 {$rowY} Td (" . $escape($toPdfText('$' . number_format($precio, 0, ',', '.'))) . ") Tj ET";
            $commands[] = "BT /F1 8 Tf 434 {$rowY} Td (" . $escape(number_format($descuento, 1, ',', '.') . '%') . ") Tj ET";
            $commands[] = "BT /F1 8 Tf 490 {$rowY} Td (" . $escape($toPdfText('$' . number_format($subtotal, 0, ',', '.'))) . ") Tj ET";
            $rowY -= 18;
        }

        // Resumen
        $commands[] = "0.91 0.95 0.98 rg 378 156 199 96 re f";
        $commands[] = "0.25 0.53 0.70 rg 378 236 199 16 re f";
        $commands[] = "1 1 1 rg";
        $commands[] = "BT /F2 8 Tf 386 241 Td (" . $escape('RESUMEN CONTABLE') . ") Tj ET";
        $commands[] = "0 0 0 rg";
        $commands[] = "BT /F1 8 Tf 386 222 Td (" . $escape($toPdfText('Subtotal Bruto: $' . number_format($totalBruto, 0, ',', '.'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 386 208 Td (" . $escape($toPdfText('Descuento: $' . number_format($descuentoGlobal, 0, ',', '.'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 386 194 Td (" . $escape($toPdfText('Neto: $' . number_format($total, 0, ',', '.'))) . ") Tj ET";
        $commands[] = "BT /F1 8 Tf 386 180 Td (" . $escape($toPdfText('IVA(19%): $' . number_format($iva, 0, ',', '.'))) . ") Tj ET";
        $commands[] = "BT /F2 10 Tf 386 165 Td (" . $escape($toPdfText('TOTAL: $' . number_format($totalConIva, 0, ',', '.'))) . ") Tj ET";

        $commands[] = "BT /F1 7 Tf 24 132 Td (" . $escape($toPdfText('Observaciones: Entrega sujeta a stock y validacion comercial. Documento referencial de cotizacion.')) . ") Tj ET";
        if (count($detalles) > $maxRows) {
            $commands[] = "BT /F1 7 Tf 24 120 Td (" . $escape($toPdfText('Nota: se muestran los primeros ' . $maxRows . ' items en esta version PDF.')) . ") Tj ET";
        }

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
