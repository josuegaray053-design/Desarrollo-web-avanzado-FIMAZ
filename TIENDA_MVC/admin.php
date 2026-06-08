<?php
/**
 * Script de utilidad para generar hashes seguros de contraseñas.
 * Utiliza el algoritmo por defecto de PHP para encriptar la clave del administrador.
 */
$hash = password_hash('admin123', PASSWORD_DEFAULT);
echo $hash;
echo "\n";

// Imprime la extensión del hash en caracteres para asegurar la compatibilidad con el campo en la BD
echo "Longitud: " . strlen($hash); 
?>