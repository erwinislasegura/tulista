<?php
// Ejemplo simple de rutas para Apache/Nginx con front controller.
$routes = [
    'GET /cliente/login' => 'cliente-login.php',
    'POST /cliente/login' => 'cliente-login.php',
    'GET /cliente/portal' => 'cliente-portal.php',
    'GET /cotizar/{token_cliente}' => 'cotizar.php?token={token_cliente}',
    'GET /admin/usuarios' => 'apps-usuarios.php',
    'GET /admin/clientes' => 'apps-clientes.php',
    'GET /admin/cotizaciones' => 'apps-cotizaciones.php',
];
