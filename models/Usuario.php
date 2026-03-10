<?php

require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT id, nombre, email, rol, porcentaje_comision, estado, created_at FROM usuarios ORDER BY id DESC')->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO usuarios (nombre, email, password, rol, porcentaje_comision, estado) VALUES (:nombre, :email, :password, :rol, :porcentaje_comision, :estado)');
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = 'UPDATE usuarios SET nombre = :nombre, email = :email, rol = :rol, porcentaje_comision = :porcentaje_comision, estado = :estado';
        if (!empty($data['password'])) {
            $sql .= ', password = :password';
        }
        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $stmt->execute($data);
    }
}
