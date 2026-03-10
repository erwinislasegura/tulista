<?php

require_once __DIR__ . '/BaseModel.php';

class CotizacionDetalle extends BaseModel
{
    private ?bool $hasDescuentoPct = null;

    private function hasDescuentoPct(): bool
    {
        if ($this->hasDescuentoPct !== null) {
            return $this->hasDescuentoPct;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM cotizacion_detalle LIKE 'descuento_pct'");
        $this->hasDescuentoPct = (bool) $stmt->fetch();
        return $this->hasDescuentoPct;
    }

    public function create(int $cotizacionId, int $productoId, int $cantidad, float $precio, float $descuentoPct, float $subtotal): bool
    {
        if ($this->hasDescuentoPct()) {
            $stmt = $this->db->prepare('INSERT INTO cotizacion_detalle (cotizacion_id, producto_id, cantidad, precio, descuento_pct, subtotal) VALUES (:cotizacion_id, :producto_id, :cantidad, :precio, :descuento_pct, :subtotal)');
            return $stmt->execute([
                'cotizacion_id' => $cotizacionId,
                'producto_id' => $productoId,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'descuento_pct' => $descuentoPct,
                'subtotal' => $subtotal,
            ]);
        }

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
        $discountField = $this->hasDescuentoPct() ? 'd.descuento_pct' : '0 AS descuento_pct';
        $stmt = $this->db->prepare("SELECT d.*, {$discountField}, p.nombre AS producto_nombre, p.sku
            FROM cotizacion_detalle d
            INNER JOIN productos p ON p.id = d.producto_id
            WHERE d.cotizacion_id = :cotizacion_id");
        $stmt->execute(['cotizacion_id' => $cotizacionId]);
        return $stmt->fetchAll();
    }
}
