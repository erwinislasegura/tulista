<?php

require_once __DIR__ . '/BaseModel.php';

class Pedido extends BaseModel
{
    public function all(): array
    {
        $sql = 'SELECT p.id, p.estado, p.total, p.fecha, c.nombre AS cliente_nombre, u.nombre AS vendedor
                FROM pedidos p
                INNER JOIN clientes c ON c.id = p.cliente_id
                LEFT JOIN usuarios u ON u.id = p.usuario_id
                ORDER BY p.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE pedidos SET estado = :estado WHERE id = :id');
        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }
}
