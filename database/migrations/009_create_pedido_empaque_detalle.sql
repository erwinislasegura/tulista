CREATE TABLE IF NOT EXISTS pedido_empaque_detalle (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    cotizacion_detalle_id INT UNSIGNED DEFAULT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad_solicitada INT UNSIGNED NOT NULL DEFAULT 0,
    stock_disponible_snapshot INT NOT NULL DEFAULT 0,
    accion ENUM('pendiente','confirmado','reemplazado','omitido') NOT NULL DEFAULT 'pendiente',
    producto_reemplazo_id INT UNSIGNED DEFAULT NULL,
    cantidad_empaquetada INT UNSIGNED NOT NULL DEFAULT 0,
    notas VARCHAR(255) DEFAULT NULL,
    updated_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_empaque_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_empaque_cot_detalle FOREIGN KEY (cotizacion_detalle_id) REFERENCES cotizacion_detalle(id) ON DELETE SET NULL,
    CONSTRAINT fk_empaque_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
    CONSTRAINT fk_empaque_producto_reemplazo FOREIGN KEY (producto_reemplazo_id) REFERENCES productos(id),
    CONSTRAINT fk_empaque_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios(id)
);

CREATE UNIQUE INDEX idx_empaque_pedido_producto ON pedido_empaque_detalle (pedido_id, producto_id);
CREATE INDEX idx_empaque_accion ON pedido_empaque_detalle (accion);
CREATE INDEX idx_empaque_updated_at ON pedido_empaque_detalle (updated_at);
