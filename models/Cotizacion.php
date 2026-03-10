<?php

require_once __DIR__ . '/BaseModel.php';

class Cotizacion extends BaseModel
{
    public function all(?int $clienteId = null): array
    {
        $sql = 'SELECT c.id, c.cliente_id, cl.nombre AS cliente_nombre, c.estado, c.total, c.fecha
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
}
