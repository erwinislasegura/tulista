<?php

require_once __DIR__ . '/../models/LogSistema.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/AuthorizationService.php';

class AuditoriaController
{
    private LogSistema $log;

    public function __construct()
    {
        AuthService::startSession();
        AuthorizationService::requirePermission('auditoria.view');
        $this->log = new LogSistema();
    }

    public function handleRequest(): array
    {
        return ['logs' => $this->log->recent(100)];
    }
}
