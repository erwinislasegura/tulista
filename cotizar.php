<?php
$query = $_SERVER['QUERY_STRING'] ?? '';
$target = 'cliente-portal.php' . ($query !== '' ? ('?' . $query) : '');
header('Location: ' . $target, true, 302);
exit;
