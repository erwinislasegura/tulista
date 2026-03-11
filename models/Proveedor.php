<?php

require_once __DIR__ . '/BaseModel.php';

class Proveedor extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM proveedores ORDER BY estado DESC, razon_social ASC')->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO proveedores (rut, razon_social, nombre_contacto, email, telefono, direccion, comuna, plazo_pago_dias, observaciones, estado) VALUES (:rut, :razon_social, :nombre_contacto, :email, :telefono, :direccion, :comuna, :plazo_pago_dias, :observaciones, :estado)');
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE proveedores SET rut=:rut, razon_social=:razon_social, nombre_contacto=:nombre_contacto, email=:email, telefono=:telefono, direccion=:direccion, comuna=:comuna, plazo_pago_dias=:plazo_pago_dias, observaciones=:observaciones, estado=:estado WHERE id=:id');
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM proveedores WHERE id=:id');
        return $stmt->execute(['id' => $id]);
    }

    public function existsByRut(string $rut, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT id FROM proveedores WHERE rut = :rut';
        $params = ['rut' => $rut];
        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $ignoreId;
        }
        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch();
    }
}
