<?php

require_once __DIR__ . '/../models/CompanyConfig.php';

class CompanyConfigService
{
    public static function get(): array
    {
        $model = new CompanyConfig();
        $config = $model->get();

        return [
            'nombre' => $config['nombre'] ?? 'TU LISTA',
            'logo_path' => $config['logo_path'] ?? 'assets/images/logo-dark.png',
        ];
    }
}
