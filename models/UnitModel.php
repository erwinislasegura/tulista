<?php

require_once __DIR__ . '/BaseModel.php';

class UnitModel extends BaseModel
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT id, descripcion, abreviatura FROM unidades_medida ORDER BY descripcion ASC');
        return $stmt->fetchAll();
    }

    public function create(string $descripcion, string $abreviatura): bool
    {
        $stmt = $this->db->prepare('INSERT INTO unidades_medida (descripcion, abreviatura) VALUES (:descripcion, :abreviatura)');
        return $stmt->execute([
            'descripcion' => $descripcion,
            'abreviatura' => $abreviatura,
        ]);
    }

    public function findByAbbreviation(string $abbreviation): ?array
    {
        $stmt = $this->db->prepare('SELECT id, descripcion, abreviatura FROM unidades_medida WHERE abreviatura = :abreviatura LIMIT 1');
        $stmt->execute(['abreviatura' => $abbreviation]);
        $unit = $stmt->fetch();
        return $unit ?: null;
    }
}
