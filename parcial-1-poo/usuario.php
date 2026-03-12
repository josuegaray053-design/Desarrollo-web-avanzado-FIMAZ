<?php
// Clase base Usuario 
class Usuario {
    protected string $nombre;
    protected string $correo;

    public function __construct(string $nombre, string $correo) {
        // Validación de formato de correo 
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Error: El formato del correo '$correo' no es válido."); [cite: 22]
        }
        $this->nombre = $nombre;
        $this->correo = $correo;
    }

    public function getNombre(): string { [cite: 23]
        return $this->nombre;
    }

    public function getCorreo(): string { [cite: 23]
        return $this->correo;
    }
}

// Clase Admin que extiende Usuario [cite: 24]
class Admin extends Usuario {
    public function getRol(): string { [cite: 24]
        return "Administrador";
    }
}

// Clase Alumno que extiende Usuario [cite: 25]
class Alumno extends Usuario {
    private string $matricula; [cite: 26]

    public function __construct(string $nombre, string $correo, string $matricula) {
        parent::__construct($nombre, $correo);
        $this->matricula = $matricula;
    }

    public function getMatricula(): string { [cite: 27]
        return $this->matricula;
    }

    public function getRol(): string { [cite: 28]
        return "Alumno";
    }
}