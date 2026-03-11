<?php

require_once __DIR__ . '/../models/RoleModel.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';
require_once __DIR__ . '/../services/AuditService.php';

class RoleController
{
    private RoleModel $roles;

    public function __construct()
    {
        AuthService::startSession();
        $this->roles = new RoleModel();
        $_SESSION['roles_flash'] = $_SESSION['roles_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        AuthorizationService::requirePermission('usuarios.manage');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $this->create();
            } elseif ($action === 'update') {
                $this->update();
            }

            header('Location: apps-roles.php');
            exit;
        }

        $flash = $_SESSION['roles_flash'];
        $_SESSION['roles_flash'] = [];

        return [
            'roles' => $this->roles->all(),
            'flash' => $flash,
        ];
    }

    private function create(): void
    {
        $codigo = $this->sanitizeCodigo($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $estado = !empty($_POST['estado']) ? 1 : 0;

        if ($codigo === '' || $nombre === '') {
            $this->flash('warning', 'Debes ingresar código y nombre para el rol.');
            return;
        }

        if ($this->roles->findByCodigo($codigo)) {
            $this->flash('warning', 'El código del rol ya existe.');
            return;
        }

        $this->roles->create([
            'codigo' => $codigo,
            'nombre' => $nombre,
            'estado' => $estado,
        ]);

        AuditService::log('crear', 'roles_usuario', null, 'Rol creado', null, ['codigo' => $codigo, 'nombre' => $nombre]);
        $this->flash('success', 'Rol creado correctamente.');
    }

    private function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $estado = !empty($_POST['estado']) ? 1 : 0;

        if ($id <= 0 || $nombre === '') {
            $this->flash('warning', 'Datos inválidos para actualizar el rol.');
            return;
        }

        $this->roles->update($id, [
            'nombre' => $nombre,
            'estado' => $estado,
        ]);

        AuditService::log('editar', 'roles_usuario', $id, 'Rol actualizado', null, ['nombre' => $nombre, 'estado' => $estado]);
        $this->flash('success', 'Rol actualizado correctamente.');
    }

    private function sanitizeCodigo(string $codigo): string
    {
        $value = strtolower(trim($codigo));
        $value = preg_replace('/[^a-z0-9_]/', '_', $value) ?? '';
        return trim($value, '_');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['roles_flash'][] = ['type' => $type, 'message' => $message];
    }
}
