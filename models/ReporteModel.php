<?php

require_once __DIR__ . '/BaseModel.php';

class ReporteModel extends BaseModel
{
    public function ventasPorVendedor(): array
    {
        $sql = "SELECT COALESCE(u.nombre, 'Sin asignar') AS vendedor, COUNT(*) AS pedidos, COALESCE(SUM(p.total),0) AS total
                FROM pedidos p
                LEFT JOIN usuarios u ON u.id = p.usuario_id
                WHERE p.estado <> 'cancelado'
                GROUP BY vendedor
                ORDER BY total DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function comisionesPorVendedor(): array
    {
        $sql = "SELECT u.nombre AS vendedor, COUNT(*) AS operaciones, COALESCE(SUM(c.monto_comision),0) AS total_comision
                FROM comisiones c
                INNER JOIN usuarios u ON u.id = c.usuario_id
                GROUP BY u.id, u.nombre
                ORDER BY total_comision DESC";
        return $this->db->query($sql)->fetchAll();
    }
}
