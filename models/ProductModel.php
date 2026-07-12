<?php

require_once __DIR__ . '/BaseModel.php';

class ProductModel extends BaseModel
{
    private bool $imagesTableReady = false;
    private bool $ivaColumnReady = false;

    public function all(): array
    {
        $this->ensureImagesTable();
        $this->ensureIvaColumn();
        $sql = 'SELECT p.id, p.categoria_id, c.nombre AS categoria, p.nombre, p.sku, p.marca_id, m.nombre AS marca,
                       p.modelo, p.unidad_id, CONCAT(u.descripcion, " (", u.abreviatura, ")") AS unidad,
                       p.codigo_barras, p.tipo_item, p.costo_neto, p.precio_venta_neto,
                       p.precio_venta_total, p.afecto_iva, p.stock_minimo, p.comision_vendedor, p.existencia,
                       img.ruta AS imagen_principal
                FROM productos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN marcas m ON m.id = p.marca_id
                LEFT JOIN unidades_medida u ON u.id = p.unidad_id
                LEFT JOIN producto_imagenes img ON img.producto_id = p.id AND img.es_principal = 1
                ORDER BY p.id DESC';

        return $this->db->query($sql)->fetchAll();
    }

    public function publicCatalog(int $limit = 120): array
    {
        $this->ensureImagesTable();
        $limit = max(1, min(240, $limit));
        $sql = 'SELECT p.id, p.nombre, p.sku, p.precio_venta_total, p.existencia,
                       c.nombre AS categoria, m.nombre AS marca,
                       CONCAT(u.descripcion, " (", u.abreviatura, ")") AS unidad,
                       img.ruta AS imagen_principal
                FROM productos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN marcas m ON m.id = p.marca_id
                LEFT JOIN unidades_medida u ON u.id = p.unidad_id
                INNER JOIN producto_imagenes img ON img.producto_id = p.id AND img.es_principal = 1
                WHERE p.existencia > 0
                ORDER BY p.nombre ASC
                LIMIT ' . $limit;

        return $this->db->query($sql)->fetchAll();
    }

    public function imagesByProductIds(array $ids): array
    {
        $this->ensureImagesTable();
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id, producto_id, ruta, es_principal, posicion FROM producto_imagenes WHERE producto_id IN ($placeholders) ORDER BY producto_id ASC, es_principal DESC, posicion ASC, id ASC");
        $stmt->execute($ids);
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['producto_id']][] = $row;
        }
        return $grouped;
    }

    public function catalog(): array
    {
        return $this->db->query('SELECT id, nombre, sku, precio_venta_total, existencia FROM productos ORDER BY nombre ASC')->fetchAll();
    }

    public function searchCatalog(string $term = '', bool $onlyInStock = false, int $limit = 80): array
    {
        $term = trim($term);
        $limit = max(1, min(200, $limit));
        $sql = 'SELECT id, nombre, sku, precio_venta_total, existencia FROM productos';
        $conditions = [];
        $params = [];

        if ($term !== '') {
            $conditions[] = '(nombre LIKE :term OR sku LIKE :term)';
            $params['term'] = '%' . $term . '%';
        }

        if ($onlyInStock) {
            $conditions[] = 'existencia > 0';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY nombre ASC LIMIT ' . $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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

    public function findBasic(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre, sku, existencia, stock_minimo, precio_venta_total FROM productos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $this->ensureIvaColumn();
        $stmt = $this->db->prepare(
            'INSERT INTO productos (
                categoria_id, nombre, sku, marca_id, modelo, unidad_id, codigo_barras, tipo_item,
                costo_neto, precio_venta_neto, precio_venta_total, afecto_iva, stock_minimo, comision_vendedor, existencia
            ) VALUES (
                :categoria_id, :nombre, :sku, :marca_id, :modelo, :unidad_id, :codigo_barras, :tipo_item,
                :costo_neto, :precio_venta_neto, :precio_venta_total, :afecto_iva, :stock_minimo, :comision_vendedor, :existencia
            )'
        );

        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $this->ensureIvaColumn();
        $data['id'] = $id;
        $stmt = $this->db->prepare(
            'UPDATE productos SET
                categoria_id = :categoria_id, nombre = :nombre, sku = :sku, marca_id = :marca_id,
                modelo = :modelo, unidad_id = :unidad_id, codigo_barras = :codigo_barras,
                tipo_item = :tipo_item, costo_neto = :costo_neto, precio_venta_neto = :precio_venta_neto,
                precio_venta_total = :precio_venta_total, afecto_iva = :afecto_iva, stock_minimo = :stock_minimo,
                comision_vendedor = :comision_vendedor, existencia = :existencia
             WHERE id = :id'
        );

        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $this->ensureImagesTable();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('DELETE FROM producto_imagenes WHERE producto_id = :id');
            $stmt->execute(['id' => $id]);
            $stmt = $this->db->prepare('DELETE FROM productos WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }



    public function findForBulkCategoryUpdate(array $criteria, int $newCategoryId): array
    {
        [$where, $params] = $this->buildBulkCriteriaWhere($criteria);
        if ($where === '' || $newCategoryId <= 0) {
            return [];
        }

        $where = '(' . $where . ') AND categoria_id <> :new_categoria_id';
        $params['new_categoria_id'] = $newCategoryId;
        $stmt = $this->db->prepare('SELECT id, nombre, sku FROM productos WHERE ' . $where . ' ORDER BY nombre ASC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateCategoryMany(array $ids, int $categoryId): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if (empty($ids) || $categoryId <= 0) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("UPDATE productos SET categoria_id = ? WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$categoryId], $ids));
        return $stmt->rowCount();
    }

    public function findForBulkDelete(array $criteria): array
    {
        $this->ensureImagesTable();
        [$where, $params] = $this->buildBulkCriteriaWhere($criteria);
        if ($where === '') {
            return [];
        }

        $stmt = $this->db->prepare('SELECT id, nombre, sku FROM productos WHERE ' . $where . ' ORDER BY nombre ASC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function deleteMany(array $ids): array
    {
        $this->ensureImagesTable();
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pathsStmt = $this->db->prepare("SELECT ruta FROM producto_imagenes WHERE producto_id IN ($placeholders)");
        $pathsStmt->execute($ids);
        $paths = array_column($pathsStmt->fetchAll(), 'ruta');

        $this->db->beginTransaction();
        try {
            $deleteImages = $this->db->prepare("DELETE FROM producto_imagenes WHERE producto_id IN ($placeholders)");
            $deleteImages->execute($ids);
            $deleteProducts = $this->db->prepare("DELETE FROM productos WHERE id IN ($placeholders)");
            $deleteProducts->execute($ids);
            $this->db->commit();
            return $paths;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function buildBulkCriteriaWhere(array $criteria): array
    {
        $conditions = [];
        $params = [];

        if (!empty($criteria['categoria_id'])) {
            $conditions[] = 'categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $criteria['categoria_id'];
        }

        if (!empty($criteria['marca_id'])) {
            $conditions[] = 'marca_id = :marca_id';
            $params['marca_id'] = (int) $criteria['marca_id'];
        }

        foreach (['modelo', 'nombre', 'sku', 'codigo_barras'] as $field) {
            $value = trim((string) ($criteria[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            $conditions[] = $field . ' LIKE :' . $field;
            $params[$field] = '%' . $value . '%';
        }

        return [implode(' AND ', $conditions), $params];
    }

    public function replaceImages(int $productId, array $images): void
    {
        $this->ensureImagesTable();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('DELETE FROM producto_imagenes WHERE producto_id = :producto_id');
            $stmt->execute(['producto_id' => $productId]);
            $this->insertImages($productId, $images);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function insertImages(int $productId, array $images): void
    {
        $this->ensureImagesTable();
        if (empty($images)) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO producto_imagenes (producto_id, ruta, es_principal, posicion) VALUES (:producto_id, :ruta, :es_principal, :posicion)');
        foreach (array_values($images) as $index => $image) {
            $stmt->execute([
                'producto_id' => $productId,
                'ruta' => $image['ruta'],
                'es_principal' => !empty($image['es_principal']) ? 1 : 0,
                'posicion' => $index + 1,
            ]);
        }
    }

    public function setPrincipalImage(int $productId, int $imageId): void
    {
        $this->ensureImagesTable();
        $stmt = $this->db->prepare('UPDATE producto_imagenes SET es_principal = 0 WHERE producto_id = :producto_id');
        $stmt->execute(['producto_id' => $productId]);
        $stmt = $this->db->prepare('UPDATE producto_imagenes SET es_principal = 1 WHERE producto_id = :producto_id AND id = :id');
        $stmt->execute(['producto_id' => $productId, 'id' => $imageId]);
    }

    public function imagePaths(int $productId): array
    {
        $this->ensureImagesTable();
        $stmt = $this->db->prepare('SELECT ruta FROM producto_imagenes WHERE producto_id = :producto_id');
        $stmt->execute(['producto_id' => $productId]);
        return array_column($stmt->fetchAll(), 'ruta');
    }

    private function ensureIvaColumn(): void
    {
        if ($this->ivaColumnReady) {
            return;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM productos LIKE 'afecto_iva'");
        if (!$stmt->fetch()) {
            $this->db->exec('ALTER TABLE productos ADD COLUMN afecto_iva TINYINT(1) NOT NULL DEFAULT 1 AFTER precio_venta_total');
        }

        $this->ivaColumnReady = true;
    }

    private function ensureImagesTable(): void
    {
        if ($this->imagesTableReady) {
            return;
        }

        $this->db->exec('CREATE TABLE IF NOT EXISTS producto_imagenes (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            producto_id INT UNSIGNED NOT NULL,
            ruta VARCHAR(255) NOT NULL,
            es_principal TINYINT(1) NOT NULL DEFAULT 0,
            posicion TINYINT UNSIGNED NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_producto_imagen_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->imagesTableReady = true;
    }
}
