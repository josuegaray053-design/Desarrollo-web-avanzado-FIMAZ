# Catálogo y Gestor de Inventario - Arquitectura MVC PHP

Este repositorio contiene el código fuente de una plataforma web para la administración y visualización de productos. El sistema está estructurado bajo el patrón de diseño Modelo-Vista-Controlador (MVC) utilizando PHP nativo enfocado en objetos, priorizando el desacoplamiento de código, la seguridad en peticiones HTTP y el registro de eventos del lado del servidor.

---

## Stack Tecnológico
* **Core:** PHP 8.x (Modularizado con Namespaces y Autoloading estándar).
* **Capa de Datos:** MySQL administrado mediante la interfaz PDO.
* **Interfaz Gráfica:** Bootstrap 5.3 (Maquetado responsive y componentes CDN), HTML5 y CSS3.
* **Servidor Web:** Apache con soporte para directivas de reescritura.

---

## Funcionalidades Clave

* **Enrutamiento Front Controller:** Procesamiento centralizado de peticiones mediante un único punto de entrada (index.php), con soporte para enmascaramiento de URLs dinámicas a través de reglas en .htaccess.
* **Módulo API RESTful:** Integración de un endpoint nativo que expone el catálogo completo de productos en formato estructurado JSON, habilitado con cabeceras CORS para consumo externo o integraciones de terceros.
* **Seguridad y Sanitización:**
  * Mecanismo de prevención contra vulnerabilidades CSRF implementando tokens criptográficos aleatorios verificados por sesiones mediante hash_equals.
  * Filtros de validación estrictos en tipos de datos, control de valores numéricos negativos y redundancia para evitar la duplicidad de claves SKU.
  * Restricción perimetral mediante control de sesiones ($_SESSION) para resguardar de forma obligatoria las rutas administrativas.
* **Paginación del Lado del Servidor:** Segmentación matemática del catálogo de cara al cliente público, limitando la carga masiva de datos a bloques fijos de 4 registros por sección.
* **Trazabilidad (Bitácora Local):** Escritura automatizada en el archivo local bitacora.log. Captura transacciones críticas de base de datos con marcas de tiempo sincronizadas bajo el huso horario del Pacífico.

---

## Organización de Directorios

```text
TIENDA_MVC/
 ┣ config           # Abstracción de base de datos y cargador de clases
 ┣ Controllers      # Despachadores de lógica (Auth, Productos, Vistas Públicas)
 ┣ Models           # Modelado de datos y ejecución de queries con PDO
 ┣ views            # Capa de presentación visual
 ┃ ┣ layouts        # Elementos estructurales reutilizables (cabecera/pie)
 ┃ ┣ productos      # Formularios e índices del área de administración
 ┃ ┗ public         # Interfaz de la vitrina comercial
 ┣ .htaccess        # Abstracción de variables GET de ruta
 ┣ bitacora.log     # Registro histórico de operaciones de escritura
 ┗ index.php        # Inicializador global y manejador de huso horario