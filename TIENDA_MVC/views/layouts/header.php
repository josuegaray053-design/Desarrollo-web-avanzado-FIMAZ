<?php 
/**
 * Encabezado global del sistema (Header).
 * Configura la sesión, las etiquetas meta principales, los estilos de Bootstrap 
 * y la barra de navegación con el sistema dinámico de alertas flash.
 */
if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Desarrollo Web Avanzado: POO+PDO+TryCatch-Namespaces-Autoload-Transacciones-MVC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/TIENDA_MVC/catalogo">Tienda MVC</a>
        <div>
            <a class="btn btn-outline-light btn-sm me-2" href="/TIENDA_MVC/catalogo">Catálogo</a>
            <a class="btn btn-warning btn-sm" href="/TIENDA_MVC/login">Administrador</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>