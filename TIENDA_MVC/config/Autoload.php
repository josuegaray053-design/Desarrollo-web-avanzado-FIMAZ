<?php

/**
 * Autoloader del proyecto: Busca e incluye de forma automática 
 * los archivos de las clases cuando se instancian en el sistema,
 * convirtiendo sus Namespaces en rutas físicas del servidor.
 */
spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../';
    $class = str_replace('\\', '/', $class);
    $parts = explode('/', $class);
    if (!empty($parts)) {
        $parts[0] = strtolower($parts[0]);
    }
     
    $file = $baseDir . implode('/', $parts) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

?>