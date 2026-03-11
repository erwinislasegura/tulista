CREATE TABLE IF NOT EXISTS proveedores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(20) NOT NULL,
    razon_social VARCHAR(180) NOT NULL,
    nombre_contacto VARCHAR(150) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    telefono VARCHAR(50) DEFAULT NULL,
    direccion VARCHAR(255) DEFAULT NULL,
    comuna VARCHAR(120) DEFAULT NULL,
    plazo_pago_dias INT NOT NULL DEFAULT 30,
    observaciones VARCHAR(255) DEFAULT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_proveedores_rut (rut),
    KEY idx_proveedores_estado_razon (estado, razon_social)
);

INSERT IGNORE INTO role_permissions (role_codigo, permiso) VALUES
('supervisor', 'proveedores.manage'),
('vendedor', 'proveedores.manage'),
('bodega', 'proveedores.view');
