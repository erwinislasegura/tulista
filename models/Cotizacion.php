<?php

require_once __DIR__ . '/BaseModel.php';

class Cotizacion extends BaseModel
{
    public function all(?int $clienteId = null): array
    {
        $sql = 'SELECT c.id, c.cliente_id, cl.nombre AS cliente_nombre, c.usuario_id, u.nombre AS vendedor, c.estado, c.total, c.fecha
                FROM cotizaciones c
                INNER JOIN clientes cl ON cl.id = c.cliente_id
                LEFT JOIN usuarios u ON u.id = c.usuario_id';
        $params = [];

        if ($clienteId) {
            $sql .= ' WHERE c.cliente_id = :cliente_id';
            $params['cliente_id'] = $clienteId;
        }

        $sql .= ' ORDER BY c.id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }



    public function findByIdAndCliente(int $id, int $clienteId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, cliente_id, estado, total FROM cotizaciones WHERE id = :id AND cliente_id = :cliente_id LIMIT 1');
        $stmt->execute(['id' => $id, 'cliente_id' => $clienteId]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT c.id, c.cliente_id, cl.nombre AS cliente_nombre, cl.rut AS cliente_rut,
                cl.empresa AS cliente_empresa, cl.email AS cliente_email, cl.telefono AS cliente_telefono,
                cl.direccion AS cliente_direccion, c.usuario_id, u.nombre AS vendedor, c.estado, c.total, c.fecha
            FROM cotizaciones c
            INNER JOIN clientes cl ON cl.id = c.cliente_id
            LEFT JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.id = :id
            LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $clienteId, int $usuarioId = 0, float $total = 0): int
    {
        $stmt = $this->db->prepare('INSERT INTO cotizaciones (cliente_id, usuario_id, estado, total) VALUES (:cliente_id, :usuario_id, :estado, :total)');
        $stmt->execute([
            'cliente_id' => $clienteId,
            'usuario_id' => $usuarioId ?: null,
            'estado' => 'borrador',
            'total' => $total,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE cotizaciones SET estado=:estado WHERE id=:id');
        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }

    public function updateTotal(int $id, float $total): bool
    {
        $stmt = $this->db->prepare('UPDATE cotizaciones SET total = :total WHERE id = :id');
        return $stmt->execute(['total' => $total, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM cotizaciones WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
