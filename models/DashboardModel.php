<?php

require_once __DIR__ . '/BaseModel.php';

class DashboardModel extends BaseModel
{
    private function scalar(string $sql): float
    {
        $value = $this->db->query($sql)->fetchColumn();
        return (float) ($value ?: 0);
    }

    public function kpis(): array
    {
        return [
            'ventas_dia' => $this->scalar("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(fecha)=CURDATE() AND estado <> 'cancelado'"),
            'ventas_mes' => $this->scalar("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE()) AND estado <> 'cancelado'"),
            'ganancia_mes' => $this->scalar("SELECT COALESCE(SUM(ganancia),0) FROM ventas_resumen vr INNER JOIN pedidos p ON p.id=vr.pedido_id WHERE YEAR(p.fecha)=YEAR(CURDATE()) AND MONTH(p.fecha)=MONTH(CURDATE())"),
            'comisiones_mes' => $this->scalar("SELECT COALESCE(SUM(monto_comision),0) FROM comisiones WHERE YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())"),
            'cotizaciones_pendientes' => $this->scalar("SELECT COUNT(*) FROM cotizaciones WHERE estado IN ('borrador','enviada')"),
            'pedidos_proceso' => $this->scalar("SELECT COUNT(*) FROM pedidos WHERE estado IN ('pendiente','preparacion','enviado')"),
            'stock_bajo' => $this->scalar('SELECT COUNT(*) FROM productos WHERE existencia <= stock_minimo'),
            'clientes_nuevos' => $this->scalar('SELECT COUNT(*) FROM clientes WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)'),
        ];
    }

    public function topProductos(): array
    {
        $sql = "SELECT p.nombre, SUM(cd.cantidad) AS total_vendido
                FROM cotizacion_detalle cd
                INNER JOIN productos p ON p.id = cd.producto_id
                GROUP BY p.id, p.nombre
                ORDER BY total_vendido DESC
                LIMIT 5";
        return $this->db->query($sql)->fetchAll();
    }

    public function topClientes(): array
    {
        $sql = "SELECT c.nombre, COUNT(*) AS total_pedidos, COALESCE(SUM(p.total),0) AS total_monto
                FROM pedidos p
                INNER JOIN clientes c ON c.id = p.cliente_id
                WHERE p.estado <> 'cancelado'
                GROUP BY c.id, c.nombre
                ORDER BY total_monto DESC
                LIMIT 5";
        return $this->db->query($sql)->fetchAll();
    }

    public function ventasPorMes(): array
    {
        $sql = "SELECT DATE_FORMAT(fecha, '%Y-%m') AS periodo, COALESCE(SUM(total),0) AS total
                FROM pedidos
                WHERE estado <> 'cancelado'
                GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                ORDER BY periodo DESC
                LIMIT 6";
        return array_reverse($this->db->query($sql)->fetchAll());
    }
}
