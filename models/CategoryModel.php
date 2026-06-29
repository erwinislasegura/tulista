<?php

require_once __DIR__ . '/BaseModel.php';

class CategoryModel extends BaseModel
{
    private bool $imagesTableReady = false;

    public function all(): array
    {
        $stmt = $this->db->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function publicCatalog(): array
    {
        $this->ensureProductImagesTable();
        $stmt = $this->db->query('SELECT c.id, c.nombre, COUNT(DISTINCT p.id) AS productos_total FROM categorias c INNER JOIN productos p ON p.categoria_id = c.id AND p.existencia > 0 INNER JOIN producto_imagenes img ON img.producto_id = p.id AND img.es_principal = 1 GROUP BY c.id, c.nombre HAVING productos_total > 0 ORDER BY c.nombre ASC');
        return $stmt->fetchAll();
    }

    public function create(string $name): bool
    {
        $stmt = $this->db->prepare('INSERT INTO categorias (nombre) VALUES (:nombre)');
        return $stmt->execute(['nombre' => $name]);
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre FROM categorias WHERE nombre = :nombre LIMIT 1');
        $stmt->execute(['nombre' => $name]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    private function ensureProductImagesTable(): void
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
            CONSTRAINT fk_producto_imagen_producto_categoria FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->imagesTableReady = true;
    }
}
