-- ERP Core Expansion

-- 1) Usuarios: nuevos roles y comisión
ALTER TABLE usuarios
    MODIFY COLUMN rol ENUM('admin','supervisor','vendedor','bodega') NOT NULL DEFAULT 'vendedor';

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS porcentaje_comision DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER rol,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- 2) Clientes: tipo y token de acceso estandarizado
ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS tipo_cliente VARCHAR(80) DEFAULT 'mayorista' AFTER direccion,
    ADD COLUMN IF NOT EXISTS token_acceso VARCHAR(80) NULL AFTER estado,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

UPDATE clientes SET token_acceso = url_token WHERE token_acceso IS NULL;

ALTER TABLE clientes
    MODIFY COLUMN token_acceso VARCHAR(80) NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_clientes_token_acceso ON clientes (token_acceso);
CREATE INDEX IF NOT EXISTS idx_clientes_tipo_estado ON clientes (tipo_cliente, estado);

-- 3) Cotizaciones: usuario responsable y estados nuevos
ALTER TABLE cotizaciones
    MODIFY COLUMN estado ENUM('borrador','enviada','aprobada','rechazada') NOT NULL DEFAULT 'borrador',
    ADD COLUMN IF NOT EXISTS usuario_id INT UNSIGNED NULL AFTER cliente_id,
    ADD COLUMN IF NOT EXISTS fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER total,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE cotizaciones
    ADD CONSTRAINT fk_cotizacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id);

CREATE INDEX IF NOT EXISTS idx_cotizaciones_estado_fecha ON cotizaciones (estado, fecha);
CREATE INDEX IF NOT EXISTS idx_cotizaciones_cliente ON cotizaciones (cliente_id);

-- 4) Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    cotizacion_id INT UNSIGNED DEFAULT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    estado ENUM('pendiente','empaquetado','despachado','transito','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_pedido_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id),
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE INDEX IF NOT EXISTS idx_pedidos_estado_fecha ON pedidos (estado, fecha);
CREATE INDEX IF NOT EXISTS idx_pedidos_usuario ON pedidos (usuario_id);

-- 5) Resumen de ventas y ganancias
CREATE TABLE IF NOT EXISTS ventas_resumen (
    pedido_id INT UNSIGNED PRIMARY KEY,
    total_venta DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_costo DECIMAL(12,2) NOT NULL DEFAULT 0,
    ganancia DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ventas_resumen_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);

-- 6) Comisiones
CREATE TABLE IF NOT EXISTS comisiones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    pedido_id INT UNSIGNED NOT NULL,
    porcentaje DECIMAL(5,2) NOT NULL,
    monto_comision DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comision_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_comision_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_comision_usuario_pedido (usuario_id, pedido_id)
);

CREATE INDEX IF NOT EXISTS idx_comisiones_fecha ON comisiones (fecha);

-- 7) Movimientos de inventario
CREATE TABLE IF NOT EXISTS movimientos_stock (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id INT UNSIGNED NOT NULL,
    tipo_movimiento ENUM('entrada','salida','ajuste') NOT NULL,
    cantidad INT NOT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mov_stock_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
    CONSTRAINT fk_mov_stock_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE INDEX IF NOT EXISTS idx_movimientos_producto_fecha ON movimientos_stock (producto_id, fecha);


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

-- 8) Auditoría
CREATE TABLE IF NOT EXISTS log_sistema (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED DEFAULT NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(120) NOT NULL,
    registro_id BIGINT UNSIGNED DEFAULT NULL,
    descripcion VARCHAR(255) NOT NULL,
    datos_anteriores JSON DEFAULT NULL,
    datos_nuevos JSON DEFAULT NULL,
    ip_usuario VARCHAR(45) DEFAULT NULL,
    navegador VARCHAR(255) DEFAULT NULL,
    url VARCHAR(255) DEFAULT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE INDEX IF NOT EXISTS idx_log_modulo_fecha ON log_sistema (modulo, fecha);
CREATE INDEX IF NOT EXISTS idx_log_usuario_fecha ON log_sistema (usuario_id, fecha);

-- 9) Mantenedores
CREATE TABLE IF NOT EXISTS tipos_cliente (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS estados_pedido (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(80) NOT NULL,
    orden_visual TINYINT UNSIGNED NOT NULL DEFAULT 1,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles_usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(80) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 10) Configuración
CREATE TABLE IF NOT EXISTS configuracion_empresa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(150) NOT NULL,
    rut_empresa VARCHAR(30) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    telefono VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    smtp_host VARCHAR(150) DEFAULT NULL,
    smtp_user VARCHAR(150) DEFAULT NULL,
    smtp_password VARCHAR(255) DEFAULT NULL,
    smtp_port INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO tipos_cliente (nombre) VALUES ('mayorista'), ('minorista'), ('institucional');
INSERT IGNORE INTO roles_usuario (codigo, nombre) VALUES
('admin', 'Administrador'),
('supervisor', 'Supervisor'),
('vendedor', 'Vendedor'),
('bodega', 'Bodega');
INSERT IGNORE INTO estados_pedido (codigo, nombre, orden_visual) VALUES
('pendiente', 'Pendiente', 1),
('empaquetado', 'Empaquetado', 2),
('despachado', 'Despachado', 3),
('transito', 'En tránsito', 4),
('entregado', 'Entregado', 5),
('cancelado', 'Cancelado', 5);
