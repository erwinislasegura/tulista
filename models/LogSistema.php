<?php

require_once __DIR__ . '/BaseModel.php';

class LogSistema extends BaseModel
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO log_sistema (usuario_id, accion, modulo, registro_id, descripcion, datos_anteriores, datos_nuevos, ip_usuario, navegador, url) VALUES (:usuario_id, :accion, :modulo, :registro_id, :descripcion, :datos_anteriores, :datos_nuevos, :ip_usuario, :navegador, :url)');
        return $stmt->execute($data);
    }

    public function recent(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        return $this->db->query("SELECT l.*, u.nombre AS usuario_nombre FROM log_sistema l LEFT JOIN usuarios u ON u.id = l.usuario_id ORDER BY l.id DESC LIMIT {$limit}")->fetchAll();
    }
}
