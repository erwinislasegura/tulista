<?php

require_once __DIR__ . '/BaseModel.php';

class Usuario extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT id, nombre, email, rol, estado, created_at FROM usuarios ORDER BY id DESC')->fetchAll();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre, email, rol, estado FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO usuarios (nombre, email, password, rol, estado) VALUES (:nombre, :email, :password, :rol, :estado)');
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = 'UPDATE usuarios SET nombre = :nombre, email = :email, rol = :rol, estado = :estado';
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

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM usuarios WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
