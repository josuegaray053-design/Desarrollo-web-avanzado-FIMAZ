<?php
namespace Config;

use PDO;
use PDOException;

class Database
{
    /**
     * Propiedades privadas con las credenciales de acceso 
     * al servidor local de la base de datos MySQL.
     */
    private string $host = 'localhost';
    private string $dbName = 'tienda_mvc';
    private string $username = 'root';
    private string $password = '';
    private string $charset = 'utf8mb4';

    /**
     * Establece la conexión segura con la base de datos mediante PDO,
     * configurando el manejo de excepciones y el modo de respuesta asociativo.
     */
    public function connect(): PDO
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die('Error de conexión: ' . $e->getMessage());
        }
    }
}
?>