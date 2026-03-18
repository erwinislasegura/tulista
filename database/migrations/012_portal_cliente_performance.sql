-- Portal cliente performance improvements (cotizaciones y seguimiento)
-- Execute this migration in the target DB (example: mysql ... tulista < this_file.sql)

SET @target_db = DATABASE();

-- If no DB is selected, default to tulista.
SET @target_db = IFNULL(@target_db, 'tulista');

-- idx_cotizaciones_cliente_estado_fecha
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @target_db
      AND TABLE_NAME = 'cotizaciones'
      AND INDEX_NAME = 'idx_cotizaciones_cliente_estado_fecha'
);
SET @sql = IF(
    @idx_exists = 0,
    'CREATE INDEX idx_cotizaciones_cliente_estado_fecha ON cotizaciones (cliente_id, estado, fecha)',
    'SELECT ''idx_cotizaciones_cliente_estado_fecha already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- idx_pedidos_cliente_estado_pago_fecha
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @target_db
      AND TABLE_NAME = 'pedidos'
      AND INDEX_NAME = 'idx_pedidos_cliente_estado_pago_fecha'
);
SET @sql = IF(
    @idx_exists = 0,
    'CREATE INDEX idx_pedidos_cliente_estado_pago_fecha ON pedidos (cliente_id, estado, estado_pago, fecha)',
    'SELECT ''idx_pedidos_cliente_estado_pago_fecha already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- idx_cotizacion_detalle_cot_prod
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @target_db
      AND TABLE_NAME = 'cotizacion_detalle'
      AND INDEX_NAME = 'idx_cotizacion_detalle_cot_prod'
);
SET @sql = IF(
    @idx_exists = 0,
    'CREATE INDEX idx_cotizacion_detalle_cot_prod ON cotizacion_detalle (cotizacion_id, producto_id)',
    'SELECT ''idx_cotizacion_detalle_cot_prod already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- idx_productos_nombre_existencia
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @target_db
      AND TABLE_NAME = 'productos'
      AND INDEX_NAME = 'idx_productos_nombre_existencia'
);
SET @sql = IF(
    @idx_exists = 0,
    'CREATE INDEX idx_productos_nombre_existencia ON productos (nombre, existencia)',
    'SELECT ''idx_productos_nombre_existencia already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
