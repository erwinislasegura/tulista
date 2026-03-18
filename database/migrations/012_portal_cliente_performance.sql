-- Mejora de performance para portal cliente (cotizaciones y seguimiento)
-- Ejecutar en ambiente de producción fuera de horario peak.

USE tulista;

-- Índices para acelerar listados del portal cliente
CREATE INDEX idx_cotizaciones_cliente_estado_fecha ON cotizaciones (cliente_id, estado, fecha);
CREATE INDEX idx_pedidos_cliente_estado_pago_fecha ON pedidos (cliente_id, estado, estado_pago, fecha);
CREATE INDEX idx_cotizacion_detalle_cot_prod ON cotizacion_detalle (cotizacion_id, producto_id);
CREATE INDEX idx_productos_nombre_existencia ON productos (nombre, existencia);
