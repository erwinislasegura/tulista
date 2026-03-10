# Arquitectura ERP Librería Mayorista (PHP MVC + MySQL)

## 1) Objetivo de arquitectura
Diseñar una base ERP modular, simple y escalable para una librería mayorista en Chile, priorizando velocidad operativa, trazabilidad y experiencia de uso minimalista.

Principios:
- Pantallas enfocadas en una sola tarea principal.
- Formularios compactos (campos esenciales por defecto + extras en panel colapsable).
- Flujos guiados de venta: **cotización -> aprobación -> pedido -> despacho -> cierre**.
- Seguridad y auditoría transversal por módulo.
- Métricas financieras y de operación visibles en dashboard en tiempo real.

---

## 2) Módulos funcionales

### 2.1 Dashboard principal
KPIs:
- Ventas del día
- Ventas del mes
- Ganancia del mes
- Comisiones del mes
- Cotizaciones pendientes
- Pedidos en proceso
- Productos con bajo stock
- Clientes nuevos

Visualizaciones:
- Ventas por mes
- Ventas por vendedor
- Productos más vendidos

Widgets:
- Top clientes
- Top productos
- Actividad reciente (`log_sistema`)

### 2.2 Usuarios y seguridad
Roles:
- Administrador
- Supervisor
- Vendedor
- Bodega

Funciones:
- Crear/editar usuario
- Activar/desactivar
- Asignar rol
- Definir porcentaje de comisión

### 2.3 Auditoría (log de usuario)
Registrar eventos críticos:
- login/logout
- crear/editar/eliminar registros
- aprobar/rechazar cotización
- conversión de cotización a pedido
- cambio de precios
- movimientos de inventario

### 2.4 Clientes
- Gestión de clientes mayoristas.
- Segmentación por tipo de cliente.
- Activación/desactivación.

### 2.5 Portal de clientes
Autenticación:
- RUT + contraseña
- Token en URL (`/portal-cliente/{token}`)

Funciones:
- Solicitar cotizaciones
- Ver estado de cotizaciones/pedidos
- Historial de compras
- Descarga PDF de cotizaciones

### 2.6 Productos
(ya implementado) + estándar financiero:
- costo
- precio venta
- stock actual
- stock mínimo
- categoría

### 2.7 Cotizaciones
Estados:
- borrador
- enviada
- aprobada
- rechazada

### 2.8 Pedidos
Estados:
- pendiente
- preparacion
- enviado
- entregado
- cancelado

### 2.9 Ganancias
Resumen por pedido en `ventas_resumen`:
- total_venta
- total_costo
- ganancia

### 2.10 Comisiones
Cálculo:
`monto_comision = total_venta * porcentaje / 100`

### 2.11 Inventario
Movimientos de stock:
- entrada
- salida
- ajuste

### 2.12 Reportes
- Ventas por periodo
- Ventas por vendedor
- Comisiones por vendedor
- Productos más vendidos
- Clientes con más compras

### 2.13 Mantenedores
- Categorías
- Tipos de cliente
- Roles
- Estados de pedido
- Parámetros de comisión

### 2.14 Configuración
- Datos empresa
- SMTP
- Parámetros generales de operación

---

## 3) Arquitectura técnica (MVC)

### Controllers sugeridos
- `DashboardController.php`
- `UsuarioController.php`
- `ClienteController.php`
- `ClienteAuthController.php`
- `ProductoController.php`
- `CotizacionController.php`
- `PedidoController.php`
- `InventarioController.php`
- `ReporteController.php`
- `ConfiguracionController.php`
- `AuditoriaController.php`

### Models sugeridos
- `Usuario.php`
- `Cliente.php`
- `Producto.php`
- `Cotizacion.php`
- `CotizacionDetalle.php`
- `Pedido.php`
- `PedidoDetalle.php`
- `MovimientoStock.php`
- `Comision.php`
- `VentaResumen.php`
- `LogSistema.php`
- `ConfiguracionEmpresa.php`

### Views sugeridas
- `views/dashboard/`
- `views/usuarios/`
- `views/clientes/`
- `views/portal_cliente/`
- `views/productos/`
- `views/cotizaciones/`
- `views/pedidos/`
- `views/inventario/`
- `views/reportes/`
- `views/configuracion/`
- `views/mantenedores/`

### Servicios transversales
- `AuthService` (sesión + ACL por rol)
- `AuditService` (escritura central en `log_sistema`)
- `PricingService` (cálculo de precios y totales)
- `CommissionService` (cálculo comisiones)
- `InventoryService` (consumo/reposición de stock)
- `ReportService` (agregados y vistas)

---

## 4) Flujo operativo de ventas

1. **Cliente** crea cotización en portal o vendedor la crea internamente.
2. Cotización queda en `borrador`/`enviada`.
3. Supervisor o vendedor aprueba/rechaza.
4. Si se aprueba, se genera pedido automáticamente.
5. Pedido pasa por estados (`pendiente -> preparacion -> enviado -> entregado`).
6. En preparación/despacho se descuenta stock (`movimientos_stock`).
7. Al marcar entregado:
   - se consolida `ventas_resumen`
   - se genera `comisiones`
8. Dashboard refresca indicadores y reportes.

---

## 5) UX/UI minimalista (SaaS)

### Reglas de interfaz
- Máximo 1 acción primaria por pantalla.
- Tabla con filtros rápidos y búsqueda persistente.
- Formularios en 1-2 columnas, sección avanzada colapsable.
- Auto-guardado en borradores cuando aplique (cotizaciones).

### Tokens visuales
- Morado principal: `#3A136A`
- Morado claro: `#8A63C7`
- Naranjo: `#F97316`
- Fondo: `#F5F6FA`
- Blanco: `#FFFFFF`
- Texto: `#1F2937`

Inputs:
- alto: `36px`
- `border-radius: 8px`
- borde: `1px solid #E5E7EB`
- focus: `#3A136A`

### Sidebar
- Dashboard
- Ventas
- Cotizaciones
- Pedidos
- Clientes
- Productos
- Inventario
- Reportes
- Usuarios
- Mantenedores
- Configuración

---

## 6) Seguridad, control y escalabilidad
- Passwords con `password_hash` (bcrypt/argon2id).
- ACL por rol + validación server-side.
- Rate-limit de login portal cliente.
- Registro de IP/UA/URL por evento.
- Índices en llaves de negocio (`rut`, `estado`, `fecha`, `usuario_id`, `cliente_id`).
- Preparar partición por fecha para `log_sistema` cuando supere volumen.

