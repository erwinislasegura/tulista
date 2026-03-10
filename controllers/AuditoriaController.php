<?php

require_once __DIR__ . '/../models/LogSistema.php';
require_once __DIR__ . '/../services/AuthService.php';

class AuditoriaController
{
    private LogSistema $log;

    public function __construct()
    {
        AuthService::startSession();
        AuthService::requireRole(['admin', 'supervisor']);
        $this->log = new LogSistema();
    }

    public function handleRequest(): array
    {
        return ['logs' => $this->log->recent(100)];
    }
}
