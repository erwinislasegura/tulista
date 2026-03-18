<?php

require_once __DIR__ . '/../models/RoleModel.php';
require_once __DIR__ . '/../models/RolePermissionModel.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/AuditService.php';

class PermisoController
{
    private $roles ;
    private $permissions ;

    public function __construct()
    {
        AuthService::startSession();
        $this->roles = new RoleModel();
        $this->permissions = new RolePermissionModel();
        $_SESSION['permisos_flash'] = $_SESSION['permisos_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        AuthorizationService::requirePermission('usuarios.manage');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save();
            header('Location: apps-permisos.php?rol=' . urlencode($_POST['rol'] ?? ''));
            exit;
        }

        $roles = $this->roles->all(true);
        $permissionsByRole = $this->permissions->allByRole();
        $selectedRole = (string) ($_GET['rol'] ?? ($roles[0]['codigo'] ?? ''));

        $flash = $_SESSION['permisos_flash'];
        $_SESSION['permisos_flash'] = [];

        return [
            'roles' => $roles,
            'selected_role' => $selectedRole,
            'permissions_by_role' => $permissionsByRole,
            'catalog' => AuthorizationService::permissionCatalog(),
            'flash' => $flash,
        ];
    }

    private function save(): void
    {
        $role = strtolower(trim((string) ($_POST['rol'] ?? '')));
        if ($role === '' || !$this->roles->findByCodigo($role)) {
            $this->flash('warning', 'Rol inválido para asignar permisos.');
            return;
        }

        $catalog = AuthorizationService::permissionCatalog();
        $requested = $_POST['permissions'] ?? [];
        $allowedPermissions = [];

        foreach ($catalog as $menuKey => $menu) {
            foreach (array_keys($menu['actions']) as $actionKey) {
                $permission = AuthorizationService::permissionForMenuAction($menuKey, $actionKey);
                if (($requested[$menuKey][$actionKey] ?? '0') === '1' && $permission !== '') {
                    $allowedPermissions[] = $permission;
                }
            }
        }

        $this->permissions->replaceForRole($role, array_values(array_unique($allowedPermissions)));
        AuditService::log('editar', 'role_permissions', null, 'Permisos actualizados', null, ['role' => $role, 'permissions' => $allowedPermissions]);
        $this->flash('success', 'Permisos actualizados correctamente.');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['permisos_flash'][] = ['type' => $type, 'message' => $message];
    }
}
