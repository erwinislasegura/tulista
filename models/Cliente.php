<?php

require_once __DIR__ . '/BaseModel.php';

class Cliente extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT id, rut, nombre, empresa, email, telefono, direccion, url_token, estado, created_at FROM clientes ORDER BY id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByRut(string $rut): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE rut = :rut LIMIT 1');
        $stmt->execute(['rut' => $rut]);
        return $stmt->fetch() ?: null;
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM clientes WHERE url_token = :token AND estado = 1 LIMIT 1');
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO clientes (rut, nombre, empresa, email, telefono, direccion, password, url_token, estado) VALUES (:rut, :nombre, :empresa, :email, :telefono, :direccion, :password, :url_token, :estado)');
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = 'UPDATE clientes SET rut=:rut, nombre=:nombre, empresa=:empresa, email=:email, telefono=:telefono, direccion=:direccion, url_token=:url_token, estado=:estado';
        if (!empty($data['password'])) {
            $sql .= ', password=:password';
        }
        $sql .= ' WHERE id=:id';

        $stmt = $this->db->prepare($sql);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM clientes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
