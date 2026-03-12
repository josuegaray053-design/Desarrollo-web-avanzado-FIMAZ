<?php
/**
 * EXAMEN PRÁCTICO – PARCIAL 1 (POO en PHP) [cite: 3]
 * Asignatura: Desarrollo Web Avanzado [cite: 4]
 * Docente: Dr. José Alfonso Aguilar Calderón [cite: 9]
 */

// 1. DEFINICIÓN DE CLASES (Debe ir al principio) [cite: 11]

class Usuario { // Clase base [cite: 14]
    protected $nombre; // Atributos [cite: 21]
    protected $correo;

    public function __construct($nombre, $correo) {
        // Validación de correo [cite: 22]
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            // Lanzar Exception si no es válido [cite: 22]
            throw new Exception("Error: El formato del correo '$correo' no es válido.");
        }
        $this->nombre = $nombre;
        $this->correo = $correo;
    }

    public function getNombre() { return $this->nombre; } // Métodos [cite: 23]
    public function getCorreo() { return $this->correo; }
}

class Admin extends Usuario { // Herencia [cite: 24]
    public function getRol() { return "Administrador"; } // Retorna rol [cite: 24]
}

class Alumno extends Usuario { // Herencia [cite: 25]
    private $matricula; // Atributo adicional [cite: 26]

    public function __construct($nombre, $correo, $matricula) {
        parent::__construct($nombre, $correo);
        $this->matricula = $matricula;
    }

    public function getMatricula() { return $this->matricula; } // Método específico [cite: 27]
    public function getRol() { return "Alumno"; } // Retorna rol [cite: 28]
}

// 2. LÓGICA DE EJECUCIÓN (index.php) [cite: 29]

$usuarios = [];
$errorMsg = "";

try {
    // 1 Admin válido [cite: 40]
    $usuarios[] = new Admin("Dr. José Alfonso", "alfonso.aguilar@uas.edu.mx");

    // 1 Alumno válido (Corregí el correo de Josue para que sea válido) [cite: 41]
    $usuarios[] = new Alumno("Josue Garay", "josuegaray053@gmail.com", "20832281");

    // 1 Alumno con correo inválido para probar la excepción [cite: 42]
    $usuarios[] = new Alumno("Usuario Error", "correo-no-valido", "00000000"); 

} catch (Exception $e) {
    // Usar try/catch para capturar la excepción [cite: 43]
    $errorMsg = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Examen Parcial 1 - FIMAZ</title>
    <style>
        table { border-collapse: collapse; width: 90%; margin: 20px auto; font-family: sans-serif; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: white; background-color: #d9534f; padding: 15px; width: 85%; margin: 20px auto; border-radius: 5px; font-family: sans-serif; }
        h1 { text-align: center; font-family: sans-serif; }
    </style>
</head>
<body>
    <h1>Examen Parcial 1 (POO en PHP)</h1>

    <?php if ($errorMsg): ?>
        <div class="error">
            <strong>Aviso del Sistema:</strong> <?php echo $errorMsg; ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Matrícula (solo si aplica)</th> </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?php echo $u->getNombre(); ?></td>
                    <td><?php echo $u->getCorreo(); ?></td>
                    <td><?php echo $u->getRol(); ?></td>
                    <td><?php echo ($u instanceof Alumno) ? $u->getMatricula() : '---'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>