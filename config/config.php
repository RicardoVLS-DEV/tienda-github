<?php
// Configuración por defecto (usa variables de entorno si existen).
$config = [
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'tienda',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'app_name' => 'Mi Tienda',
];

// Si existe config.local.php (ignorado por Git) sobreescribe los valores de arriba.
$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $config = array_replace_recursive($config, require $local);
}

return $config;
