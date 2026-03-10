ALTER TABLE clientes
    ADD COLUMN IF NOT EXISTS giro VARCHAR(150) DEFAULT NULL AFTER empresa,
    ADD COLUMN IF NOT EXISTS comuna VARCHAR(120) DEFAULT NULL AFTER giro;

ALTER TABLE cotizaciones
    ADD COLUMN IF NOT EXISTS contacto_nombre VARCHAR(150) DEFAULT NULL AFTER fecha,
    ADD COLUMN IF NOT EXISTS contacto_email VARCHAR(180) DEFAULT NULL AFTER contacto_nombre,
    ADD COLUMN IF NOT EXISTS contacto_telefono VARCHAR(40) DEFAULT NULL AFTER contacto_email,
    ADD COLUMN IF NOT EXISTS direccion_entrega VARCHAR(255) DEFAULT NULL AFTER contacto_telefono,
    ADD COLUMN IF NOT EXISTS observaciones VARCHAR(255) DEFAULT NULL AFTER direccion_entrega;

ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS contacto_nombre VARCHAR(150) DEFAULT NULL AFTER fecha,
    ADD COLUMN IF NOT EXISTS contacto_email VARCHAR(180) DEFAULT NULL AFTER contacto_nombre,
    ADD COLUMN IF NOT EXISTS contacto_telefono VARCHAR(40) DEFAULT NULL AFTER contacto_email,
    ADD COLUMN IF NOT EXISTS direccion_entrega VARCHAR(255) DEFAULT NULL AFTER contacto_telefono,
    ADD COLUMN IF NOT EXISTS observaciones VARCHAR(255) DEFAULT NULL AFTER direccion_entrega;

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_codigo VARCHAR(30) NOT NULL,
    permiso VARCHAR(80) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_role_permiso (role_codigo, permiso),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_codigo) REFERENCES roles_usuario(codigo)
);

INSERT IGNORE INTO role_permissions (role_codigo, permiso) VALUES
('admin', '*'),
('supervisor', 'dashboard.view'),
('supervisor', 'cotizaciones.manage'),
('supervisor', 'pedidos.manage'),
('supervisor', 'clientes.manage'),
('supervisor', 'productos.manage'),
('supervisor', 'inventario.manage'),
('supervisor', 'bodega.view'),
('supervisor', 'reportes.view'),
('supervisor', 'auditoria.view'),
('supervisor', 'usuarios.manage'),
('supervisor', 'configuracion.view'),
('vendedor', 'dashboard.view'),
('vendedor', 'cotizaciones.manage'),
('vendedor', 'pedidos.manage'),
('vendedor', 'clientes.manage'),
('vendedor', 'productos.manage'),
('vendedor', 'inventario.view'),
('vendedor', 'bodega.view'),
('vendedor', 'reportes.basic'),
('bodega', 'dashboard.view'),
('bodega', 'pedidos.view'),
('bodega', 'inventario.manage'),
('bodega', 'bodega.view'),
('bodega', 'productos.view');
