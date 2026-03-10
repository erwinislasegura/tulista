CREATE DATABASE IF NOT EXISTS tulista CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tulista;

CREATE TABLE IF NOT EXISTS categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS marcas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS unidades_medida (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(150) NOT NULL,
    abreviatura VARCHAR(30) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    sku VARCHAR(100) DEFAULT NULL,
    marca_id INT UNSIGNED DEFAULT NULL,
    modelo VARCHAR(100) DEFAULT NULL,
    unidad_id INT UNSIGNED DEFAULT NULL,
    codigo_barras VARCHAR(100) DEFAULT NULL,
    tipo_item VARCHAR(100) DEFAULT NULL,
    costo_neto DECIMAL(12,2) NOT NULL DEFAULT 0,
    precio_venta_neto DECIMAL(12,2) NOT NULL DEFAULT 0,
    precio_venta_total DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 0,
    comision_vendedor DECIMAL(8,2) NOT NULL DEFAULT 0,
    existencia INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    CONSTRAINT fk_producto_marca FOREIGN KEY (marca_id) REFERENCES marcas(id),
    CONSTRAINT fk_producto_unidad FOREIGN KEY (unidad_id) REFERENCES unidades_medida(id)
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    telefono VARCHAR(40) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    cargo VARCHAR(120) DEFAULT NULL,
    notas VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','supervisor','vendedor','bodega') NOT NULL DEFAULT 'vendedor',
    porcentaje_comision DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    empresa VARCHAR(150) DEFAULT NULL,
    telefono VARCHAR(40) DEFAULT NULL,
    email VARCHAR(180) NOT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    tipo_cliente VARCHAR(80) DEFAULT 'mayorista',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    token_acceso VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cotizaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    estado ENUM('borrador','enviada','aprobada','rechazada') NOT NULL DEFAULT 'borrador',
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cotizacion_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_cotizacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS cotizacion_detalle (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cotizacion_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(12,2) NOT NULL,
    descuento_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_detalle_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    cotizacion_id INT UNSIGNED DEFAULT NULL,
    usuario_id INT UNSIGNED DEFAULT NULL,
    estado ENUM('pendiente','preparacion','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    CONSTRAINT fk_pedido_cotizacion FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id),
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS ventas_resumen (
    pedido_id INT UNSIGNED PRIMARY KEY,
    total_venta DECIMAL(12,2) NOT NULL DEFAULT 0,
    total_costo DECIMAL(12,2) NOT NULL DEFAULT 0,
    ganancia DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ventas_resumen_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
);

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

CREATE INDEX idx_productos_sku ON productos (sku);
CREATE INDEX idx_clientes_tipo_estado ON clientes (tipo_cliente, estado);
CREATE INDEX idx_cotizaciones_estado_fecha ON cotizaciones (estado, fecha);
CREATE INDEX idx_pedidos_estado_fecha ON pedidos (estado, fecha);
CREATE INDEX idx_movimientos_producto_fecha ON movimientos_stock (producto_id, fecha);
CREATE INDEX idx_log_modulo_fecha ON log_sistema (modulo, fecha);

INSERT IGNORE INTO tipos_cliente (nombre) VALUES ('mayorista'), ('minorista'), ('institucional');
INSERT IGNORE INTO roles_usuario (codigo, nombre) VALUES
('admin', 'Administrador'),
('supervisor', 'Supervisor'),
('vendedor', 'Vendedor'),
('bodega', 'Bodega');
INSERT IGNORE INTO estados_pedido (codigo, nombre, orden_visual) VALUES
('pendiente', 'Pendiente', 1),
('preparacion', 'Preparación', 2),
('enviado', 'Enviado', 3),
('entregado', 'Entregado', 4),
('cancelado', 'Cancelado', 5);

INSERT INTO usuarios (nombre, email, password, rol, porcentaje_comision, estado)
SELECT 'Super Administrador', 'superadmin@tulista.local', '$2y$12$RfmGss4UGywSyo0mofOzHeuEPkHH7NZVYk5Xn.7NXpKYePN0/zmdS', 'admin', 0, 1
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'superadmin@tulista.local'
);
