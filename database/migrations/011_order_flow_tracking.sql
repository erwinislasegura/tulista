ALTER TABLE pedidos
    MODIFY COLUMN estado ENUM('pendiente','procesado','en_proceso','empaquetado','despachado','transito','entregado','cancelado') NOT NULL DEFAULT 'pendiente';

UPDATE pedidos
SET estado = 'en_proceso'
WHERE estado = 'procesado';

ALTER TABLE pedidos
    MODIFY COLUMN estado ENUM('pendiente','en_proceso','empaquetado','despachado','transito','entregado','cancelado') NOT NULL DEFAULT 'pendiente';

SET @has_estado_pago := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pedidos'
      AND COLUMN_NAME = 'estado_pago'
);
SET @sql_estado_pago := IF(
    @has_estado_pago = 0,
    "ALTER TABLE pedidos ADD COLUMN estado_pago ENUM('pendiente','pagado') NOT NULL DEFAULT 'pendiente' AFTER estado",
    'SELECT 1'
);
PREPARE stmt_estado_pago FROM @sql_estado_pago;
EXECUTE stmt_estado_pago;
DEALLOCATE PREPARE stmt_estado_pago;

SET @has_pagado_at := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pedidos'
      AND COLUMN_NAME = 'pagado_at'
);
SET @sql_pagado_at := IF(
    @has_pagado_at = 0,
    "ALTER TABLE pedidos ADD COLUMN pagado_at DATETIME DEFAULT NULL AFTER estado_pago",
    'SELECT 1'
);
PREPARE stmt_pagado_at FROM @sql_pagado_at;
EXECUTE stmt_pagado_at;
DEALLOCATE PREPARE stmt_pagado_at;

UPDATE pedidos
SET pagado_at = COALESCE(pagado_at, updated_at)
WHERE estado_pago = 'pagado' AND pagado_at IS NULL;

INSERT INTO estados_pedido (codigo, nombre, orden_visual, estado)
SELECT 'en_proceso', 'En proceso', 2, 1
WHERE NOT EXISTS (
    SELECT 1 FROM estados_pedido WHERE codigo = 'en_proceso'
);

UPDATE estados_pedido SET nombre = 'Pendiente', orden_visual = 1 WHERE codigo = 'pendiente';
UPDATE estados_pedido SET nombre = 'En proceso', orden_visual = 2 WHERE codigo = 'en_proceso';
UPDATE estados_pedido SET nombre = 'Empaquetado', orden_visual = 3 WHERE codigo = 'empaquetado';
UPDATE estados_pedido SET nombre = 'Despachado', orden_visual = 4 WHERE codigo = 'despachado';
UPDATE estados_pedido SET nombre = 'En tránsito', orden_visual = 5 WHERE codigo = 'transito';
UPDATE estados_pedido SET nombre = 'Entregado', orden_visual = 6 WHERE codigo = 'entregado';
UPDATE estados_pedido SET nombre = 'Cancelado', orden_visual = 7 WHERE codigo = 'cancelado';
