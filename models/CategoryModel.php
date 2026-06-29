<?php

require_once __DIR__ . '/BaseModel.php';

class CategoryModel extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }


    public function publicCatalog(): array
    {
        $stmt = $this->db->query('SELECT c.id, c.nombre, COUNT(p.id) AS productos_total FROM categorias c LEFT JOIN productos p ON p.categoria_id = c.id GROUP BY c.id, c.nombre ORDER BY c.nombre ASC');
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
}
