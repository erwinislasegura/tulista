<?php

require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/../conexion/Database.php';

class AuthorizationService
{
    private const DEFAULT_PERMISSIONS = [
        'admin' => ['*'],
        'supervisor' => [
            'dashboard.view', 'cotizaciones.manage', 'pedidos.manage', 'clientes.manage', 'productos.manage',
            'inventario.manage', 'bodega.view', 'reportes.view', 'auditoria.view', 'usuarios.manage', 'configuracion.view',
        ],
        'vendedor' => [
            'dashboard.view', 'cotizaciones.manage', 'pedidos.manage', 'clientes.manage', 'productos.manage',
            'inventario.view', 'bodega.view', 'reportes.basic',
        ],
        'bodega' => [
            'dashboard.view', 'pedidos.view', 'inventario.manage', 'bodega.view', 'productos.view',
        ],
    ];

    private static ?array $dbPermissions = null;

    public static function role(): string
    {
        $user = AuthService::user();
        return strtolower((string) ($user['rol'] ?? ''));
    }

    public static function can(string $permission): bool
    {
        $role = self::role();
        if ($role === '') {
            return false;
        }

        $permissions = self::permissionsByRole();
        $rolePermissions = $permissions[$role] ?? [];

        if (in_array('*', $rolePermissions, true) || in_array($permission, $rolePermissions, true)) {
            return true;
        }

        if (str_ends_with($permission, '.view')) {
            $manage = substr($permission, 0, -5) . '.manage';
            return in_array($manage, $rolePermissions, true);
        }

        return false;
    }

    public static function requirePermission(string $permission): void
    {
        if (!self::can($permission)) {
            http_response_code(403);
            exit('Acceso no autorizado para esta acción.');
        }
    }

    private static function permissionsByRole(): array
    {
        if (self::$dbPermissions !== null) {
            return self::$dbPermissions;
        }

        $permissions = self::DEFAULT_PERMISSIONS;

        try {
            $db = Database::getConnection();
            $tableExists = $db->query("SHOW TABLES LIKE 'role_permissions'")->fetch();
            if ($tableExists) {
                $rows = $db->query('SELECT role_codigo, permiso FROM role_permissions WHERE estado = 1')->fetchAll();
                if (!empty($rows)) {
                    $permissions = [];
                    foreach ($rows as $row) {
                        $role = strtolower((string) ($row['role_codigo'] ?? ''));
                        $perm = (string) ($row['permiso'] ?? '');
                        if ($role === '' || $perm === '') {
                            continue;
                        }
                        $permissions[$role][] = $perm;
                    }
                }
            }
        } catch (Throwable $e) {
            // Fallback a permisos por defecto si no hay conexión o tabla.
        }

        self::$dbPermissions = $permissions;
        return $permissions;
    }
}
