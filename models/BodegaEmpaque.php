<?php

require_once __DIR__ . '/BaseModel.php';

class BodegaEmpaque extends BaseModel
{
    public function sincronizarDesdePedido(int $pedidoId): int
    {
        $sql = "INSERT INTO pedido_empaque_detalle (
                    pedido_id,
                    cotizacion_detalle_id,
                    producto_id,
                    cantidad_solicitada,
                    stock_disponible_snapshot,
                    accion,
                    cantidad_empaquetada,
                    updated_by
                )
                SELECT
                    p.id,
                    cd.id,
                    cd.producto_id,
                    cd.cantidad,
                    pr.existencia,
                    'pendiente',
                    0,
                    NULL
                FROM pedidos p
                INNER JOIN cotizacion_detalle cd ON cd.cotizacion_id = p.cotizacion_id
                INNER JOIN productos pr ON pr.id = cd.producto_id
                LEFT JOIN pedido_empaque_detalle ped
                    ON ped.pedido_id = p.id AND ped.producto_id = cd.producto_id
                WHERE p.id = :pedido_id
                  AND p.cotizacion_id IS NOT NULL
                  AND ped.id IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pedido_id' => $pedidoId]);
        return $stmt->rowCount();
    }

    public function itemsPorPedido(int $pedidoId): array
    {
        $stmt = $this->db->prepare("SELECT ped.*, 
                pr.nombre AS producto_nombre,
                pr.sku AS producto_sku,
                pr.existencia AS stock_actual,
                rep.nombre AS reemplazo_nombre,
                rep.sku AS reemplazo_sku,
                rep.existencia AS reemplazo_stock
            FROM pedido_empaque_detalle ped
            INNER JOIN productos pr ON pr.id = ped.producto_id
            LEFT JOIN productos rep ON rep.id = ped.producto_reemplazo_id
            WHERE ped.pedido_id = :pedido_id
            ORDER BY ped.id ASC");
        $stmt->execute(['pedido_id' => $pedidoId]);
        return $stmt->fetchAll();
    }

    public function findItem(int $itemId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM pedido_empaque_detalle WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $itemId]);
        return $stmt->fetch() ?: null;
    }

    public function resolverItem(int $itemId, array $payload): bool
    {
        $payload['id'] = $itemId;
        $stmt = $this->db->prepare("UPDATE pedido_empaque_detalle
            SET accion = :accion,
                producto_reemplazo_id = :producto_reemplazo_id,
                cantidad_empaquetada = :cantidad_empaquetada,
                notas = :notas,
                updated_by = :updated_by
            WHERE id = :id");
        return $stmt->execute($payload);
    }

    public function resumenPorPedido(int $pedidoId): array
    {
        $stmt = $this->db->prepare("SELECT accion, COUNT(*) AS total
            FROM pedido_empaque_detalle
            WHERE pedido_id = :pedido_id
            GROUP BY accion");
        $stmt->execute(['pedido_id' => $pedidoId]);
        $rows = $stmt->fetchAll();

        $base = ['pendiente' => 0, 'confirmado' => 0, 'reemplazado' => 0, 'omitido' => 0];
        foreach ($rows as $row) {
            $base[$row['accion']] = (int) $row['total'];
        }
        return $base;
    }

    public function historialReciente(int $limit = 80): array
    {
        $limit = max(1, min(200, $limit));
        $sql = "SELECT ped.id, ped.pedido_id, ped.accion, ped.cantidad_solicitada, ped.cantidad_empaquetada,
                ped.notas, ped.updated_at,
                p.nombre AS producto_nombre,
                r.nombre AS reemplazo_nombre,
                u.nombre AS usuario_nombre
            FROM pedido_empaque_detalle ped
            INNER JOIN productos p ON p.id = ped.producto_id
            LEFT JOIN productos r ON r.id = ped.producto_reemplazo_id
            LEFT JOIN usuarios u ON u.id = ped.updated_by
            WHERE ped.accion <> 'pendiente'
            ORDER BY ped.updated_at DESC
            LIMIT {$limit}";

        return $this->db->query($sql)->fetchAll();
    }
}
