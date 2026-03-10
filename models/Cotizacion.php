<?php

require_once __DIR__ . '/BaseModel.php';

class Cotizacion extends BaseModel
{
    public function all(?int $clienteId = null): array
    {
        $sql = 'SELECT c.id, c.cliente_id, cl.nombre AS cliente_nombre, c.estado, c.total, c.created_at
                FROM cotizaciones c
                INNER JOIN clientes cl ON cl.id = c.cliente_id';
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

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cotizaciones WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $clienteId, float $total = 0): int
    {
        $stmt = $this->db->prepare('INSERT INTO cotizaciones (cliente_id, estado, total) VALUES (:cliente_id, :estado, :total)');
        $stmt->execute(['cliente_id' => $clienteId, 'estado' => 'pendiente', 'total' => $total]);
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
}
