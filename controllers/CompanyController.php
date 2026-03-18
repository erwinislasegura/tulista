<?php

require_once __DIR__ . '/../models/CompanyConfig.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';

class CompanyController
{
    private $company ;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('configuracion.view');
        $this->company = new CompanyConfig();
        $_SESSION['empresa_flash'] = $_SESSION['empresa_flash'] ?? [];
    }

    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save();
            header('Location: apps-configuracion-empresa.php');
            exit;
        }

        $flash = $_SESSION['empresa_flash'];
        $_SESSION['empresa_flash'] = [];

        return [
            'empresa' => $this->company->get() ?: [],
            'flash' => $flash,
        ];
    }

    private function save(): void
    {
        $logoPath = trim($_POST['logo_path_actual'] ?? '');

        if (!empty($_FILES['logo']['name'])) {
            $upload = $this->uploadLogo($_FILES['logo']);
            if ($upload === null) {
                return;
            }
            $logoPath = $upload;
        }

        $payload = [
            'nombre' => trim($_POST['nombre'] ?? 'TU LISTA'),
            'razon_social' => trim($_POST['razon_social'] ?? ''),
            'rut' => trim($_POST['rut'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'direccion' => trim($_POST['direccion'] ?? ''),
            'sitio_web' => trim($_POST['sitio_web'] ?? ''),
            'logo_path' => $logoPath,
        ];

        if ($payload['nombre'] === '' || $payload['razon_social'] === '' || $payload['rut'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('warning', 'Completa nombre, razón social, RUT y email válido.');
            return;
        }

        $this->company->save($payload);
        $this->flash('success', 'Configuración de empresa actualizada.');
    }

    private function uploadLogo(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->flash('danger', 'No fue posible cargar el logo.');
            return null;
        }

        $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
        $mime = mime_content_type($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            $this->flash('warning', 'Formato de logo no permitido (usa PNG, JPG, WEBP o SVG).');
            return null;
        }

        $dir = __DIR__ . '/../assets/images/company';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $filename = 'logo-empresa.' . $allowed[$mime];
        $target = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            $this->flash('danger', 'No fue posible guardar el logo en el servidor.');
            return null;
        }

        return 'assets/images/company/' . $filename;
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['empresa_flash'][] = ['type' => $type, 'message' => $message];
    }
}
