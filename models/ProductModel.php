<?php

require_once __DIR__ . '/BaseModel.php';

class ProductModel extends BaseModel
{
    public function all(): array
    {
        $sql = 'SELECT p.id, c.nombre AS categoria, p.nombre, p.sku, m.nombre AS marca,
                       CONCAT(u.descripcion, " (", u.abreviatura, ")") AS unidad,
                       p.precio_venta_total, p.existencia
                FROM productos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN marcas m ON m.id = p.marca_id
                LEFT JOIN unidades_medida u ON u.id = p.unidad_id
                ORDER BY p.id DESC';

        return $this->db->query($sql)->fetchAll();
    }



    public function catalog(): array
    {
        return $this->db->query('SELECT id, nombre, sku, precio_venta_total, existencia FROM productos ORDER BY nombre ASC')->fetchAll();
    }

    public function catalogByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, nombre, precio_venta_total FROM productos WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }



    public function stockLookup(string $term = ''): array
    {
        $term = trim($term);
        $sql = 'SELECT id, nombre, sku, existencia, stock_minimo, precio_venta_total FROM productos';
        $params = [];

        if ($term !== '') {
            $sql .= ' WHERE nombre LIKE :term OR sku LIKE :term';
            $params['term'] = '%' . $term . '%';
        }

        $sql .= ' ORDER BY nombre ASC LIMIT 100';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO productos (
                categoria_id, nombre, sku, marca_id, modelo, unidad_id, codigo_barras, tipo_item,
                costo_neto, precio_venta_neto, precio_venta_total, stock_minimo, comision_vendedor, existencia
            ) VALUES (
                :categoria_id, :nombre, :sku, :marca_id, :modelo, :unidad_id, :codigo_barras, :tipo_item,
                :costo_neto, :precio_venta_neto, :precio_venta_total, :stock_minimo, :comision_vendedor, :existencia
            )'
        );

        return $stmt->execute($data);
    }
}
