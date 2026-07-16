<?php

/**
 * Returns the storefront base path for both a domain root and a subdirectory.
 *
 * Examples:
 * - https://tulista.cl/            => /
 * - http://localhost/tulista/      => /tulista/
 */
function paginaBasePath(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    $markerPosition = strpos($scriptName, '/pagina/');

    if ($markerPosition !== false) {
        $base = substr($scriptName, 0, $markerPosition + 1);
    } else {
        $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/') . '/';
    }

    return $base === '//' ? '/' : $base;
}
