<?php
require_once __DIR__ . '/controllers/ClienteAuthController.php';
$auth = new ClienteAuthController();
$auth->logout();
