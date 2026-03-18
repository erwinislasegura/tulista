-- 013_cliente_portal_pedidos_historial_hardening.sql
-- Objetivo:
-- 1) Asegurar columnas requeridas por el portal cliente en pedidos.
-- 2) Crear índice para mejorar filtros de historial por cliente/estado/pago/fecha.

-- ===== columnas estado_pago =====
SET @col_estado_pago_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'pedidos'
      AND column_name = 'estado_pago'
);
SET @sql_estado_pago := IF(
    @col_estado_pago_exists = 0,
    "ALTER TABLE pedidos ADD COLUMN estado_pago ENUM('pendiente','pagado') NOT NULL DEFAULT 'pendiente' AFTER estado",
    'SELECT 1'
);
PREPARE stmt FROM @sql_estado_pago;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===== columna pagado_at =====
SET @col_pagado_at_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'pedidos'
      AND column_name = 'pagado_at'
);
SET @sql_pagado_at := IF(
    @col_pagado_at_exists = 0,
    'ALTER TABLE pedidos ADD COLUMN pagado_at DATETIME NULL AFTER estado_pago',
    'SELECT 1'
);
PREPARE stmt FROM @sql_pagado_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===== índice historial cliente =====
SET @idx_historial_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'pedidos'
      AND index_name = 'idx_pedidos_cliente_historial'
);
SET @sql_idx_historial := IF(
    @idx_historial_exists = 0,
    'ALTER TABLE pedidos ADD INDEX idx_pedidos_cliente_historial (cliente_id, estado, estado_pago, fecha)',
    'SELECT 1'
);
PREPARE stmt FROM @sql_idx_historial;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
