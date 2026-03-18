<?php

require_once __DIR__ . '/BaseModel.php';

class Cliente extends BaseModel
{
    private $hasTokenAcceso = null;

    private function hasTokenAcceso(): bool
    {
        if ($this->hasTokenAcceso !== null) {
            return $this->hasTokenAcceso;
        }
        $stmt = $this->db->query("SHOW COLUMNS FROM clientes LIKE 'token_acceso'");
        $this->hasTokenAcceso = (bool) $stmt->fetch();
        return $this->hasTokenAcceso;
    }

    private function tokenSelect(): string
    {
        return $this->hasTokenAcceso() ? 'token_acceso' : 'url_token';
    }

    public function all(): array
    {
        $token = $this->tokenSelect();
        $sql = "SELECT c.id, c.rut, c.nombre, c.empresa, c.giro, c.comuna, c.email, c.telefono, c.direccion, c.tipo_cliente, c.estado, c.created_at, c.{$token} AS token,
                       (SELECT COUNT(*) FROM cotizaciones ct WHERE ct.cliente_id = c.id) AS total_cotizaciones,
                       (SELECT COUNT(*) FROM pedidos p WHERE p.cliente_id = c.id) AS total_pedidos
                FROM clientes c
                ORDER BY c.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $token = $this->tokenSelect();
        $stmt = $this->db->prepare("SELECT *, {$token} AS token FROM clientes WHERE id = :id LIMIT 1");
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
        $field = $this->tokenSelect();
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE {$field} = :token AND estado = 1 LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public function cotizacionesByCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('SELECT id, estado, total, fecha FROM cotizaciones WHERE cliente_id=:id ORDER BY id DESC');
        $stmt->execute(['id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function pedidosByCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('SELECT id, estado, total, fecha FROM pedidos WHERE cliente_id=:id ORDER BY id DESC');
        $stmt->execute(['id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $field = $this->tokenSelect();
        $stmt = $this->db->prepare("INSERT INTO clientes (rut, nombre, empresa, giro, comuna, email, telefono, direccion, tipo_cliente, password, {$field}, estado) VALUES (:rut, :nombre, :empresa, :giro, :comuna, :email, :telefono, :direccion, :tipo_cliente, :password, :token, :estado)");
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $field = $this->tokenSelect();
        $data['id'] = $id;
        $sql = "UPDATE clientes SET rut=:rut, nombre=:nombre, empresa=:empresa, giro=:giro, comuna=:comuna, email=:email, telefono=:telefono, direccion=:direccion, tipo_cliente=:tipo_cliente, {$field}=:token, estado=:estado";
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

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE clientes SET password = :password WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'password' => $passwordHash,
        ]);
    }
}
