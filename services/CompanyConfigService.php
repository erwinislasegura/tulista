<?php

require_once __DIR__ . '/../models/CompanyConfig.php';

class CompanyConfigService
{
    public static function get(): array
    {
        try {
            $model = new CompanyConfig();
            $config = $model->get();
        } catch (Throwable $e) {
            $config = null;
        }

        return [
            'nombre' => $config['nombre'] ?? 'TU LISTA',
            'logo_path' => $config['logo_path'] ?? 'assets/source/images/logo-tulista-mark.svg',
        ];
    }
}
