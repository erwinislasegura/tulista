<?php

require_once __DIR__ . '/BaseModel.php';

class UnitModel extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT id, nombre FROM unidades_medida ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function create(string $name): bool
    {
        $stmt = $this->db->prepare('INSERT INTO unidades_medida (nombre) VALUES (:nombre)');
        return $stmt->execute(['nombre' => $name]);
    }

    public function findOrCreate(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $stmt = $this->db->prepare('SELECT id FROM unidades_medida WHERE nombre = :nombre LIMIT 1');
        $stmt->execute(['nombre' => $name]);
        $item = $stmt->fetch();
        if ($item) {
            return (int) $item['id'];
        }

        $this->create($name);
        return (int) $this->db->lastInsertId();
    }
}
