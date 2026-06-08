<?php namespace Controllers;

use Models\UsuarioModel;

class AuthController
{
    /**
     * Muestra la vista del formulario de inicio de sesión.
     */
    public function showLogin(): void
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesa la autenticación del usuario, valida credenciales 
     * y crea la sesión del administrador.
     */
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?route=login');
            exit;
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->buscarPorUsername($username);

        if ($usuario && password_verify($password, $usuario['password'])) 
        {
            $_SESSION['admin'] = [
                'id' => $usuario['id'],
                'username' => $usuario['username'],
                'nombre_completo' => $usuario['nombre_completo']
            ];

            $_SESSION['success'] = 'Bienvenido, ' . $usuario['nombre_completo'] . '.';
            header('Location: index.php?route=productos');
            exit;
        }

        $_SESSION['error'] = 'Credenciales incorrectas.';
        header('Location: index.php?route=login');
        exit;
    }

    /**
     * Destruye la sesión activa del usuario y redirige al login.
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: index.php?route=login');
        exit;
    }
}