<?php
declare(strict_types=1);

// Autoload PSR-4 minimo (App\ => src/). Si usas Composer, vendor/autoload.php lo reemplaza.
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require_once __DIR__ . '/src/helpers.php';
