<?php

require_once __DIR__ . '/BaseModel.php';

class RoleModel extends BaseModel
{
    public function all(bool $onlyActive = false): array
    {
        $sql = 'SELECT id, codigo, nombre, estado, created_at FROM roles_usuario';
        if ($onlyActive) {
            $sql .= ' WHERE estado = 1';
        }
        $sql .= ' ORDER BY nombre ASC';

        return $this->db->query($sql)->fetchAll();
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT id, codigo, nombre, estado FROM roles_usuario WHERE codigo = :codigo LIMIT 1');
        $stmt->execute(['codigo' => $codigo]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO roles_usuario (codigo, nombre, estado) VALUES (:codigo, :nombre, :estado)');

        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE roles_usuario SET nombre = :nombre, estado = :estado WHERE id = :id');

        return $stmt->execute($data);
    }
}
