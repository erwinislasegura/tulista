SET @column_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'productos'
      AND COLUMN_NAME = 'afecto_iva'
);

SET @sql := IF(
    @column_exists = 0,
    'ALTER TABLE productos ADD COLUMN afecto_iva TINYINT(1) NOT NULL DEFAULT 1 AFTER precio_venta_total',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
