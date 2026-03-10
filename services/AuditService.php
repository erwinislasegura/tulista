<?php

require_once __DIR__ . '/../models/LogSistema.php';
require_once __DIR__ . '/AuthService.php';

class AuditService
{
    public static function log(string $accion, string $modulo, ?int $registroId, string $descripcion, ?array $antes = null, ?array $despues = null): void
    {
        try {
            $usuario = AuthService::user();
            $model = new LogSistema();
            $model->create([
                'usuario_id' => $usuario['id'] ?? null,
                'accion' => $accion,
                'modulo' => $modulo,
                'registro_id' => $registroId,
                'descripcion' => $descripcion,
                'datos_anteriores' => $antes ? json_encode($antes, JSON_UNESCAPED_UNICODE) : null,
                'datos_nuevos' => $despues ? json_encode($despues, JSON_UNESCAPED_UNICODE) : null,
                'ip_usuario' => $_SERVER['REMOTE_ADDR'] ?? null,
                'navegador' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'url' => $_SERVER['REQUEST_URI'] ?? null,
            ]);
        } catch (Throwable $e) {
            // Evita romper flujo principal por errores de auditoría.
        }
    }
}
