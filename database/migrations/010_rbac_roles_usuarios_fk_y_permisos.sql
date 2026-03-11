-- RBAC funcional: roles dinámicos en usuarios + permisos por menú/acción

CREATE TABLE IF NOT EXISTS roles_usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(80) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_codigo VARCHAR(30) NOT NULL,
    permiso VARCHAR(80) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_role_permiso (role_codigo, permiso),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_codigo) REFERENCES roles_usuario(codigo)
);

INSERT IGNORE INTO roles_usuario (codigo, nombre, estado) VALUES
('admin', 'Administrador', 1),
('supervisor', 'Supervisor', 1),
('vendedor', 'Vendedor', 1),
('bodega', 'Bodega', 1);

-- usuarios.rol debe permitir nuevos códigos de rol creados desde mantenedor
ALTER TABLE usuarios
    MODIFY COLUMN rol VARCHAR(30) NOT NULL DEFAULT 'vendedor';

-- normaliza roles inválidos al rol por defecto
UPDATE usuarios u
LEFT JOIN roles_usuario r ON r.codigo = u.rol
SET u.rol = 'vendedor'
WHERE r.codigo IS NULL;

-- índice para FK (idempotente)
SET @idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'usuarios'
      AND index_name = 'idx_usuarios_rol'
);
SET @idx_sql := IF(@idx_exists = 0,
    'ALTER TABLE usuarios ADD INDEX idx_usuarios_rol (rol)',
    'SELECT 1');
PREPARE stmt_idx FROM @idx_sql;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

-- FK usuarios.rol -> roles_usuario.codigo (idempotente)
SET @fk_exists := (
    SELECT COUNT(*)
    FROM information_schema.table_constraints
    WHERE constraint_schema = DATABASE()
      AND table_name = 'usuarios'
      AND constraint_name = 'fk_usuarios_rol'
      AND constraint_type = 'FOREIGN KEY'
);
SET @fk_sql := IF(@fk_exists = 0,
    'ALTER TABLE usuarios ADD CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol) REFERENCES roles_usuario(codigo)',
    'SELECT 1');
PREPARE stmt_fk FROM @fk_sql;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

-- permisos base (compatibles con AuthorizationService actual)
INSERT IGNORE INTO role_permissions (role_codigo, permiso, estado) VALUES
('admin', '*', 1),
('supervisor', 'dashboard.view', 1),
('supervisor', 'cotizaciones.manage', 1),
('supervisor', 'pedidos.manage', 1),
('supervisor', 'clientes.manage', 1),
('supervisor', 'productos.manage', 1),
('supervisor', 'inventario.manage', 1),
('supervisor', 'bodega.view', 1),
('supervisor', 'reportes.view', 1),
('supervisor', 'auditoria.view', 1),
('supervisor', 'usuarios.manage', 1),
('supervisor', 'configuracion.view', 1),
('vendedor', 'dashboard.view', 1),
('vendedor', 'cotizaciones.manage', 1),
('vendedor', 'pedidos.manage', 1),
('vendedor', 'clientes.manage', 1),
('vendedor', 'productos.manage', 1),
('vendedor', 'inventario.view', 1),
('vendedor', 'bodega.view', 1),
('vendedor', 'reportes.basic', 1),
('bodega', 'dashboard.view', 1),
('bodega', 'pedidos.view', 1),
('bodega', 'inventario.manage', 1),
('bodega', 'bodega.view', 1),
('bodega', 'productos.view', 1);
