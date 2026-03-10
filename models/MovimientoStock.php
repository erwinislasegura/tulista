<?php

require_once __DIR__ . '/BaseModel.php';

class MovimientoStock extends BaseModel
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO movimientos_stock (producto_id, tipo_movimiento, cantidad, usuario_id, descripcion) VALUES (:producto_id, :tipo_movimiento, :cantidad, :usuario_id, :descripcion)');
        return $stmt->execute($data);
    }

    public function recent(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $sql = "SELECT m.id, m.tipo_movimiento, m.cantidad, m.descripcion, m.fecha, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
                FROM movimientos_stock m
                INNER JOIN productos p ON p.id = m.producto_id
                LEFT JOIN usuarios u ON u.id = m.usuario_id
                ORDER BY m.id DESC LIMIT {$limit}";
        return $this->db->query($sql)->fetchAll();
    }
}
