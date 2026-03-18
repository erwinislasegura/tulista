<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Compatibilidad heredada: la autenticación real está en AuthService.
 * Aquí solo mantenemos helpers para páginas antiguas sin depender de SQLite.
 */
function checkAuth($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Email is not valid';
    }

    setSesstion();
    return true;
}

function setSesstion()
{
    $_SESSION['user'] = true;
}

function logoutSesstion()
{
    $_SESSION['user'] = false;
}
