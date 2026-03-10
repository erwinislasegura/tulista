<?php

require_once __DIR__ . '/BaseModel.php';

class Pedido extends BaseModel
{
    public const ESTADOS_OPERACION = ['pendiente', 'empaquetado', 'despachado', 'transito', 'entregado', 'cancelado'];

    public function all(): array
    {
        $sql = 'SELECT p.id, p.cliente_id, p.cotizacion_id, p.usuario_id, p.estado, p.total, p.fecha,
                       p.contacto_nombre, p.contacto_email, p.contacto_telefono, p.direccion_entrega, p.observaciones,
                       c.nombre AS cliente_nombre, u.nombre AS vendedor
                FROM pedidos p
                INNER JOIN clientes c ON c.id = p.cliente_id
                LEFT JOIN usuarios u ON u.id = p.usuario_id
                ORDER BY p.id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO pedidos (cliente_id, cotizacion_id, usuario_id, estado, total, fecha, contacto_nombre, contacto_email, contacto_telefono, direccion_entrega, observaciones) VALUES (:cliente_id, :cotizacion_id, :usuario_id, :estado, :total, :fecha, :contacto_nombre, :contacto_email, :contacto_telefono, :direccion_entrega, :observaciones)');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE pedidos SET cliente_id = :cliente_id, cotizacion_id = :cotizacion_id, usuario_id = :usuario_id, estado = :estado, total = :total, fecha = :fecha, contacto_nombre = :contacto_nombre, contacto_email = :contacto_email, contacto_telefono = :contacto_telefono, direccion_entrega = :direccion_entrega, observaciones = :observaciones WHERE id = :id');
        return $stmt->execute($data);
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare('UPDATE pedidos SET estado = :estado WHERE id = :id');
        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM pedidos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function clientesSource(): array
    {
        return $this->db->query('SELECT id, nombre, rut, empresa, email, telefono, direccion FROM clientes WHERE estado = 1 ORDER BY nombre ASC')->fetchAll();
    }

    public function vendedoresSource(): array
    {
        return $this->db->query("SELECT id, nombre FROM usuarios WHERE estado = 1 AND rol IN ('admin','supervisor','vendedor') ORDER BY nombre ASC")->fetchAll();
    }

    public function byCliente(int $clienteId): array
    {
        $stmt = $this->db->prepare('SELECT id, cotizacion_id, estado, total, fecha FROM pedidos WHERE cliente_id = :cliente_id ORDER BY id DESC');
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function cotizacionesSource(): array
    {
        $sql = "SELECT c.id, c.cliente_id, c.total, c.estado, c.contacto_nombre, c.contacto_email, c.contacto_telefono, c.direccion_entrega, c.observaciones, cl.nombre AS cliente_nombre
                FROM cotizaciones c
                INNER JOIN clientes cl ON cl.id = c.cliente_id
                WHERE c.estado IN ('aprobada', 'enviada')
                ORDER BY c.id DESC
                LIMIT 100";
        return $this->db->query($sql)->fetchAll();
    }

    public function cotizacionesAceptadasPendientesBodega(): array
    {
        $sql = "SELECT c.id, c.total, c.fecha, c.estado, cl.nombre AS cliente_nombre,
                       CASE WHEN p.id IS NULL THEN 0 ELSE 1 END AS ya_en_pedido
                FROM cotizaciones c
                INNER JOIN clientes cl ON cl.id = c.cliente_id
                LEFT JOIN pedidos p ON p.cotizacion_id = c.id
                WHERE c.estado = 'aprobada'
                ORDER BY c.id DESC";

        return $this->db->query($sql)->fetchAll();
    }
}
