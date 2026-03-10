<?php

require_once __DIR__ . '/BaseModel.php';

class BrandModel extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT id, nombre FROM marcas ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function create(string $name): bool
    {
        $stmt = $this->db->prepare('INSERT INTO marcas (nombre) VALUES (:nombre)');
        return $stmt->execute(['nombre' => $name]);
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre FROM marcas WHERE nombre = :nombre LIMIT 1');
        $stmt->execute(['nombre' => $name]);
        $brand = $stmt->fetch();
        return $brand ?: null;
    }

    public function findOrCreate(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $brand = $this->findByName($name);
        if ($brand) {
            return (int) $brand['id'];
        }

        $this->create($name);
        return (int) $this->db->lastInsertId();
    }
}
