<?php

require_once __DIR__ . '/BaseModel.php';

class CategoryModel extends BaseModel
{
    private bool $imagesTableReady = false;
    private bool $activeColumnReady = false;

    public function all(): array
    {
        $this->ensureActiveColumn();
        $stmt = $this->db->query('SELECT id, nombre, activo FROM categorias ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function publicCatalog(): array
    {
        $this->ensureActiveColumn();
        $this->ensureProductImagesTable();
        $stmt = $this->db->query('SELECT c.id, c.nombre, COUNT(DISTINCT p.id) AS productos_total FROM categorias c INNER JOIN productos p ON p.categoria_id = c.id AND p.existencia > 0 INNER JOIN producto_imagenes img ON img.producto_id = p.id AND img.es_principal = 1 WHERE c.activo = 1 GROUP BY c.id, c.nombre HAVING productos_total > 0 ORDER BY c.nombre ASC');
        return $stmt->fetchAll();
    }

    public function create(string $name): int
    {
        $this->ensureActiveColumn();
        $stmt = $this->db->prepare('INSERT INTO categorias (nombre, activo) VALUES (:nombre, 1)');
        $stmt->execute(['nombre' => $name]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name): bool
    {
        $this->ensureActiveColumn();
        $stmt = $this->db->prepare('UPDATE categorias SET nombre = :nombre WHERE id = :id');
        return $stmt->execute(['nombre' => $name, 'id' => $id]);
    }

    public function setActive(int $id, bool $active): bool
    {
        $this->ensureActiveColumn();
        $stmt = $this->db->prepare('UPDATE categorias SET activo = :activo WHERE id = :id');
        return $stmt->execute(['activo' => $active ? 1 : 0, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categorias WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function findByName(string $name): ?array
    {
        $this->ensureActiveColumn();
        $stmt = $this->db->prepare('SELECT id, nombre, activo FROM categorias WHERE nombre = :nombre LIMIT 1');
        $stmt->execute(['nombre' => $name]);
        $category = $stmt->fetch();
        return $category ?: null;
    }


    public function findByNameExceptId(string $name, int $id): ?array
    {
        $this->ensureActiveColumn();
        $stmt = $this->db->prepare('SELECT id, nombre, activo FROM categorias WHERE nombre = :nombre AND id <> :id LIMIT 1');
        $stmt->execute(['nombre' => $name, 'id' => $id]);
        $category = $stmt->fetch();
        return $category ?: null;
    }

    private function ensureActiveColumn(): void
    {
        if ($this->activeColumnReady) {
            return;
        }

        $stmt = $this->db->query("SHOW COLUMNS FROM categorias LIKE 'activo'");
        if (!$stmt->fetch()) {
            $this->db->exec('ALTER TABLE categorias ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER nombre');
        }
        $this->activeColumnReady = true;
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
