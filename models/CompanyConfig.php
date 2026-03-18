<?php

require_once __DIR__ . '/BaseModel.php';

class CompanyConfig extends BaseModel
{
    public function get(): ?array
    {
        try {
            $stmt = $this->db->query('SELECT * FROM empresa_config WHERE id = 1 LIMIT 1');
            $data = $stmt->fetch();
            return $data ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function save(array $payload): void
    {
        $this->ensureTable();

        $sql = 'INSERT INTO empresa_config (id, nombre, razon_social, rut, email, telefono, direccion, sitio_web, logo_path, updated_at)
                VALUES (1, :nombre, :razon_social, :rut, :email, :telefono, :direccion, :sitio_web, :logo_path, NOW())
                ON DUPLICATE KEY UPDATE
                    nombre = VALUES(nombre),
                    razon_social = VALUES(razon_social),
                    rut = VALUES(rut),
                    email = VALUES(email),
                    telefono = VALUES(telefono),
                    direccion = VALUES(direccion),
                    sitio_web = VALUES(sitio_web),
                    logo_path = VALUES(logo_path),
                    updated_at = NOW()';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nombre' => $payload['nombre'],
            'razon_social' => $payload['razon_social'],
            'rut' => $payload['rut'],
            'email' => $payload['email'],
            'telefono' => $payload['telefono'],
            'direccion' => $payload['direccion'],
            'sitio_web' => $payload['sitio_web'],
            'logo_path' => $payload['logo_path'],
        ]);
    }

    private function ensureTable(): void
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS empresa_config (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            nombre VARCHAR(150) NOT NULL,
            razon_social VARCHAR(180) NOT NULL,
            rut VARCHAR(20) NOT NULL,
            email VARCHAR(120) NOT NULL,
            telefono VARCHAR(40) DEFAULT NULL,
            direccion VARCHAR(255) DEFAULT NULL,
            sitio_web VARCHAR(180) DEFAULT NULL,
            logo_path VARCHAR(255) DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }
}
