<?php

require_once __DIR__ . '/BaseModel.php';

class RolePermissionModel extends BaseModel
{
    public function allByRole(): array
    {
        $rows = $this->db->query('SELECT role_codigo, permiso FROM role_permissions WHERE estado = 1 ORDER BY role_codigo, permiso')->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['role_codigo']][] = $row['permiso'];
        }

        return $result;
    }

    public function replaceForRole(string $roleCodigo, array $permissions): void
    {
        $this->db->beginTransaction();
        try {
            $delete = $this->db->prepare('DELETE FROM role_permissions WHERE role_codigo = :role_codigo');
            $delete->execute(['role_codigo' => $roleCodigo]);

            if (!empty($permissions)) {
                $insert = $this->db->prepare('INSERT INTO role_permissions (role_codigo, permiso, estado) VALUES (:role_codigo, :permiso, 1)');
                foreach ($permissions as $permission) {
                    $insert->execute([
                        'role_codigo' => $roleCodigo,
                        'permiso' => $permission,
                    ]);
                }
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
