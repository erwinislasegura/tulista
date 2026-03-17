<?php

require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/../conexion/Database.php';

class AuthorizationService
{
    private const MENU_PERMISSIONS = [
        'dashboard' => ['label' => 'Dashboard', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'cotizaciones' => ['label' => 'Cotizaciones', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'pedidos' => ['label' => 'Pedidos', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'clientes' => ['label' => 'Clientes', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'productos' => ['label' => 'Productos', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'proveedores' => ['label' => 'Proveedores', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'inventario' => ['label' => 'Inventario', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'bodega' => ['label' => 'Bodega', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'reportes' => ['label' => 'Reportes', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'auditoria' => ['label' => 'Auditoría', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'usuarios' => ['label' => 'Usuarios', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
        'configuracion' => ['label' => 'Configuración', 'actions' => ['view' => 'Ver', 'edit' => 'Editar', 'delete' => 'Eliminar']],
    ];

    private const DEFAULT_PERMISSIONS = [
        'admin' => ['*'],
        'supervisor' => [
            'dashboard.view', 'cotizaciones.manage', 'pedidos.manage', 'clientes.manage', 'productos.manage', 'proveedores.manage',
            'inventario.manage', 'bodega.view', 'reportes.view', 'auditoria.view', 'usuarios.manage', 'configuracion.view',
        ],
        'vendedor' => [
            'dashboard.view', 'cotizaciones.manage', 'pedidos.manage', 'clientes.manage', 'productos.manage', 'proveedores.manage',
            'inventario.view', 'bodega.view', 'reportes.basic',
        ],
        'bodega' => [
            'dashboard.view', 'pedidos.view', 'inventario.manage', 'bodega.view', 'productos.view', 'proveedores.view',
        ],
    ];

    private static ?array $dbPermissions = null;

    public static function role(): string
    {
        $user = AuthService::user();
        return self::normalizeRole((string) ($user['rol'] ?? ''));
    }

    public static function can(string $permission): bool
    {
        $role = self::role();
        if ($role === '') {
            return false;
        }

        if (in_array($role, ['admin', 'superadmin'], true)) {
            return true;
        }

        $permissions = self::permissionsByRole();
        $rolePermissions = $permissions[$role] ?? [];

        if (in_array('*', $rolePermissions, true) || in_array($permission, $rolePermissions, true)) {
            return true;
        }

        if (substr($permission, -7) === '.manage') {
            $edit = substr($permission, 0, -7) . '.edit';
            if (in_array($edit, $rolePermissions, true)) {
                return true;
            }
        }

        if (substr($permission, -5) === '.view') {
            $manage = substr($permission, 0, -5) . '.manage';
            $edit = substr($permission, 0, -5) . '.edit';
            return in_array($manage, $rolePermissions, true) || in_array($edit, $rolePermissions, true);
        }

        return false;
    }

    public static function permissionCatalog(): array
    {
        return self::MENU_PERMISSIONS;
    }

    public static function permissionForMenuAction(string $menuKey, string $action): string
    {
        if (!isset(self::MENU_PERMISSIONS[$menuKey]['actions'][$action])) {
            return '';
        }

        if ($action === 'edit') {
            return $menuKey . '.manage';
        }

        if ($action === 'delete') {
            return $menuKey . '.delete';
        }

        return $menuKey . '.view';
    }

    public static function requirePermission(string $permission): void
    {
        if (!AuthService::user()) {
            header('Location: auth-signin.php');
            exit;
        }

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
                    $dbPermissionsByRole = [];
                    foreach ($rows as $row) {
                        $role = self::normalizeRole((string) ($row['role_codigo'] ?? ''));
                        $perm = trim((string) ($row['permiso'] ?? ''));
                        if ($role === '' || $perm === '') {
                            continue;
                        }
                        $dbPermissionsByRole[$role][] = $perm;
                    }

                    foreach ($dbPermissionsByRole as $role => $rolePermissions) {
                        $permissions[$role] = array_values(array_unique($rolePermissions));
                    }
                }
            }
        } catch (Throwable $e) {
            // Fallback a permisos por defecto si no hay conexión o tabla.
        }

        self::$dbPermissions = $permissions;
        return $permissions;
    }

    private static function normalizeRole(string $role): string
    {
        $normalized = strtolower(trim($role));

        if ($normalized === 'administrador') {
            return 'admin';
        }

        return $normalized;
    }

}
