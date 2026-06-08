<?php
// Forzar la zona horaria del Pacífico (Sinaloa) de forma matemática
date_default_timezone_set('Etc/GMT+7');

/**
 * Enrutador principal de la aplicación (Front Controller).
 * Inicializa los mecanismos de sesión global, genera tokens de seguridad CSRF,
 * gestiona la autocarga de namespaces e intercepta las peticiones URI para 
 * delegarlas al controlador y método correspondiente.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Genera un token criptográfico único por sesión para mitigar ataques CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Carga automática de los archivos de clases e interfaces del sistema
require_once __DIR__ . '/config/Autoload.php';

use Controllers\AuthController;
use Controllers\ProductoController;
use Controllers\PublicController;

// Intercepta la ruta solicitada por parámetros GET (soporta 'url' o 'route')
$route = $_GET['url'] ?? $_GET['route'] ?? 'catalogo';
$route = rtrim($route, '/');

// Instanciación de los controladores del patrón MVC
$authController = new AuthController();
$productoController = new ProductoController();
$publicController = new PublicController();

// Estructura de control para la resolución dinámica de rutas URL
switch ($route) {
    case 'login':
        $authController->showLogin();
        break;

    case 'auth/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $authController->login();
        }
        break;

    case 'logout':
        $authController->logout();
        break;

    case 'productos':
        $productoController->index();
        break;
        
    case 'productos/create':
        $productoController->create();
        break;

    case 'productos/store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productoController->store();
        }
        break;

    case 'productos/edit':
        $productoController->edit();
        break;

    case 'productos/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productoController->update();
        }
        break;

    case 'productos/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productoController->delete();
        }
        break;

    // Endpoint API REST para obtener el catálogo completo en formato JSON
    case 'api/productos':
        $productoController->apiIndex();
        break;

    case 'catalogo':
    default:
        $publicController->catalogo();
        break;
}
?>