ALTER TABLE cotizacion_detalle
    ADD COLUMN descuento_pct DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER precio;
