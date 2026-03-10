<?php

require_once __DIR__ . '/BaseModel.php';

class CotizacionDetalle extends BaseModel
{
    public function create(int $cotizacionId, int $productoId, int $cantidad, float $precio, float $subtotal): bool
    {
        $stmt = $this->db->prepare('INSERT INTO cotizacion_detalle (cotizacion_id, producto_id, cantidad, precio, subtotal) VALUES (:cotizacion_id, :producto_id, :cantidad, :precio, :subtotal)');
        return $stmt->execute([
            'cotizacion_id' => $cotizacionId,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'precio' => $precio,
            'subtotal' => $subtotal,
        ]);
    }

    public function findByCotizacion(int $cotizacionId): array
    {
        $stmt = $this->db->prepare('SELECT d.*, p.nombre AS producto_nombre
            FROM cotizacion_detalle d
            INNER JOIN productos p ON p.id = d.producto_id
            WHERE d.cotizacion_id = :cotizacion_id');
        $stmt->execute(['cotizacion_id' => $cotizacionId]);
        return $stmt->fetchAll();
    }
}
