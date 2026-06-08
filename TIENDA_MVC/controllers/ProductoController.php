<?php
namespace Controllers;

use Models\ProductoModel;

class ProductoController
{
    private ProductoModel $productoModel;

    /**
     * Inicializa el modelo de productos para interactuar con la base de datos.
     */
    public function __construct()
    {
        $this->productoModel = new ProductoModel();
    }

    /**
     * Restringe el acceso redirigiendo al login si no hay sesión activa.
     */
    private function verificarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin'])) {
            header('Location: index.php?route=login');
            exit;
        }
    }

    /**
     * Muestra la lista completa de productos en el panel de administración.
     */
    public function index(): void
    {
        $this->verificarSesion();
        $productos = $this->productoModel->obtenerTodos();
        require_once __DIR__ . '/../views/productos/index.php';
    }

    /**
     * Genera la paginación de 4 en 4 productos para la vista pública de la tienda.
     */
    public function catalogo(): void
    {
        $porPagina = 4;
        $paginaActual = (int)($_GET['pagina'] ?? 1);
        if ($paginaActual < 1) { 
            $paginaActual = 1; 
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $totalProductos = $this->productoModel->contarTotal();
        $productos = $this->productoModel->obtenerPorPagina($porPagina, $offset);
        $totalPaginas = (int)ceil($totalProductos / $porPagina);

        require_once __DIR__ . '/../views/public/catalogo.php'; 
    }

    /**
     * Muestra el formulario para registrar un nuevo producto.
     */
    public function create(): void
    {
        $this->verificarSesion();
        require_once __DIR__ . '/../views/productos/create.php';
    }

    /**
     * Valida la seguridad CSRF, campos, tipos de datos e imagen, y guarda el producto.
     */
    public function store(): void
    {
        $this->verificarSesion();

        $tokenFormulario = $_POST['csrf_token'] ?? '';
        $tokenSesion = $_SESSION['csrf_token'] ?? '';

        if ($tokenFormulario === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            $_SESSION['error'] = 'Error de seguridad: Origen de formulario no válido (CSRF Inválido).';
            header('Location: index.php?route=productos/create');
            exit;
        }

        $data = [
            'sku' => trim($_POST['sku'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio_compra' => trim($_POST['precio_compra'] ?? ''),
            'precio_venta' => trim($_POST['precio_venta'] ?? ''),
            'existencia' => trim($_POST['existencia'] ?? '')
        ];

        if (
            $data['sku'] === '' ||
            $data['nombre'] === '' ||
            $data['descripcion'] === '' ||
            $data['precio_compra'] === '' ||
            $data['precio_venta'] === '' ||
            $data['existencia'] === ''
        ) {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?route=productos/create');
            exit;
        }

        if (!is_numeric($data['precio_compra']) || !is_numeric($data['precio_venta'])
            || !is_numeric($data['existencia'])) {
            $_SESSION['error'] = 'Precio de compra, precio de venta y existencia deben ser numéricos.';
            header('Location: index.php?route=productos/create');
            exit;
        }

        $precioCompra = (float)$data['precio_compra'];
        $precioVenta = (float)$data['precio_venta'];
        $existencia = (int)$data['existencia'];

        if ($precioCompra < 0 || $precioVenta < 0 || $existencia < 0) {
            $_SESSION['error'] = 'No se permiten valores negativos en precios o existencias.';
            header('Location: index.php?route=productos/create');
            exit;
        }

        if ($precioVenta < $precioCompra) {
            $_SESSION['error'] = 'El precio de venta no puede ser menor que el precio de compra.';
            header('Location: index.php?route=productos/create');
            exit;
        }

        if ($this->productoModel->verificarSkuDuplicado($data['sku'])) {
            $_SESSION['error'] = 'El SKU ya se encuentra registrado.';
            header('Location: index.php?route=productos/create');
            exit;
        }

        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'La imagen del producto es obligatoria.';
            header('Location: index.php?route=productos/create');
            exit;
        }

        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName = $_FILES['imagen']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExtension, $extensionesPermitidas)) {
            $_SESSION['error'] = 'Formato de imagen no permitido (Solo JPG, PNG, WEBP).';
            header('Location: index.php?route=productos/create');
            exit;
        }

        $nuevoNombreImagen = md5(time() . $fileName) . '.' . $fileExtension;
        $rutaDestino = __DIR__ . '/../views/img/' . $nuevoNombreImagen;

        if (move_uploaded_file($fileTmpPath, $rutaDestino)) {
            $data['imagen'] = $nuevoNombreImagen; 
        } else {
            $_SESSION['error'] = 'Error al guardar la imagen en el servidor.';
            header('Location: index.php?route=productos/create');
            exit;
        }

        if ($this->productoModel->crear($data)) {
            $_SESSION['success'] = 'Producto registrado correctamente.';
            $this->registrarEnLog('CREAR', 'Registró el producto con SKU: ' . $data['sku']);
        } else {
            $_SESSION['error'] = 'No fue posible registrar el producto.';
        }

        header('Location: index.php?route=productos');
        exit;
    }

    /**
     * Muestra el formulario para modificar un producto según su ID.
     */
    public function edit(): void
    {
        $this->verificarSesion();

        $id = (int)($_GET['id'] ?? 0);
        $producto = $this->productoModel->obtenerPorId($id);

        if (!$producto) {
            $_SESSION['error'] = 'Producto no encontrado.';
            header('Location: index.php?route=productos');
            exit;
        }

        require_once __DIR__ . '/../views/productos/edit.php';
    }

    /**
     * Procesa los cambios de un producto, valida datos y actualiza opcionalmente su imagen.
     */
    public function update(): void
    {
        $this->verificarSesion();

        $id = (int)($_POST['id'] ?? 0);

        $tokenFormulario = $_POST['csrf_token'] ?? '';
        $tokenSesion = $_SESSION['csrf_token'] ?? '';

        if ($tokenFormulario === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            $_SESSION['error'] = 'Error de seguridad: Origen de formulario no válido (CSRF Inválido).';
            header('Location: index.php?route=productos/edit&id=' . $id);
            exit;
        }

        $productoActual = $this->productoModel->obtenerPorId($id);
        if (!$productoActual) {
            $_SESSION['error'] = 'Producto no encontrado.';
            header('Location: index.php?route=productos');
            exit;
        }

        $data = [
            'sku' => trim($_POST['sku'] ?? ''),
            'nombre' => trim($_POST['nombre'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio_compra' => trim($_POST['precio_compra'] ?? ''),
            'precio_venta' => trim($_POST['precio_venta'] ?? ''),
            'existencia' => trim($_POST['existencia'] ?? ''),
            'imagen' => $productoActual['imagen'] 
        ];

        if ($id <= 0) {
            $_SESSION['error'] = 'ID inválido.';
            header('Location: index.php?route=productos');
            exit;
        }

        if (
            $data['sku'] === '' ||
            $data['nombre'] === '' ||
            $data['descripcion'] === '' ||
            $data['precio_compra'] === '' ||
            $data['precio_venta'] === '' ||
            $data['existencia'] === ''
        ) {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?route=productos/edit&id=' . $id);
            exit;
        }

        if (!is_numeric($data['precio_compra']) || !is_numeric($data['precio_venta'])
            || !is_numeric($data['existencia'])) {
            $_SESSION['error'] = 'Precio de compra, precio de venta y existencia deben ser numéricos.';
            header('Location: index.php?route=productos/edit&id=' . $id);
            exit;
        }

        $precioCompra = (float)$data['precio_compra'];
        $precioVenta = (float)$data['precio_venta'];
        $existencia = (int)$data['existencia'];

        if ($precioCompra < 0 || $precioVenta < 0 || $existencia < 0) {
            $_SESSION['error'] = 'No se permiten valores negativos en precios o existencias.';
            header('Location: index.php?route=productos/edit&id=' . $id);
            exit;
        }

        if ($precioVenta < $precioCompra) {
            $_SESSION['error'] = 'El precio de venta no puede ser menor que el precio de compra.';
            header('Location: index.php?route=productos/edit&id=' . $id);
            exit;
        }

        if ($this->productoModel->verificarSkuDuplicado($data['sku'], $id)) {
            $_SESSION['error'] = 'El SKU ya pertenece a otro producto.';
            header('Location: index.php?route=productos/edit&id=' . $id);
            exit;
        }

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['imagen']['tmp_name'];
            $fileName = $_FILES['imagen']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($fileExtension, $extensionesPermitidas)) {
                $_SESSION['error'] = 'Formato de imagen no permitido (Solo JPG, PNG, WEBP).';
                header('Location: index.php?route=productos/edit&id=' . $id);
                exit;
            }

            $nuevoNombreImagen = md5(time() . $fileName) . '.' . $fileExtension;
            $rutaDestino = __DIR__ . '/../views/img/' . $nuevoNombreImagen;

            if (move_uploaded_file($fileTmpPath, $rutaDestino)) {
                if (!empty($productoActual['imagen']) && file_exists(__DIR__ . '/../views/img/' . $productoActual['imagen'])) {
                    unlink(__DIR__ . '/../views/img/' . $productoActual['imagen']);
                }
                $data['imagen'] = $nuevoNombreImagen; 
            } else {
                $_SESSION['error'] = 'Error al guardar la nueva imagen en el servidor.';
                header('Location: index.php?route=productos/edit&id=' . $id);
                exit;
            }
        }

        if ($this->productoModel->actualizar($id, $data)) {
            $_SESSION['success'] = 'Producto actualizado correctamente.';
            $this->registrarEnLog('EDITAR', "Actualizó el producto ID: $id (SKU: " . $data['sku'] . ")");
        } else {
            $_SESSION['error'] = 'No fue posible actualizar el producto.';
        }

        header('Location: index.php?route=productos');
        exit;
    }

    /**
     * Elimina un producto de la base de datos junto con su imagen física.
     */
    public function delete(): void
    {
        $this->verificarSesion();

        $tokenFormulario = $_POST['csrf_token'] ?? '';
        $tokenSesion = $_SESSION['csrf_token'] ?? '';

        if ($tokenFormulario === '' || !hash_equals($tokenSesion, $tokenFormulario)) {
            $_SESSION['error'] = 'Error de seguridad: Origen de formulario no válido (CSRF Inválido).';
            header('Location: index.php?route=productos');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'ID inválido.';
            header('Location: index.php?route=productos');
            exit;
        }

        $producto = $this->productoModel->obtenerPorId($id);
        if ($producto && !empty($producto['imagen']) && file_exists(__DIR__ . '/../views/img/' . $producto['imagen'])) {
            unlink(__DIR__ . '/../views/img/' . $producto['imagen']);
        }

        if ($this->productoModel->eliminar($id)) {
            $_SESSION['success'] = 'Producto eliminado correctamente.';
            $this->registrarEnLog('ELIMINAR', "Eliminó el producto ID: $id");
        } else {
            $_SESSION['error'] = 'No fue posible eliminar el producto.';
        }

        header('Location: index.php?route=productos');
        exit;
    }

    /**
     * Escribe las operaciones exitosas del administrador en el archivo bitacora.log.
     */
    private function registrarEnLog(string $accion, string $detalle): void
    {
        $usuario = $_SESSION['admin']['username'] ?? 'Desconocido';
        $fecha = date('Y-m-d H:i:s');
        
        $linea = "[$fecha] USUARIO: $usuario | ACCIÓN: $accion | DETALLE: $detalle" . PHP_EOL;
        
        file_put_contents(__DIR__ . '/../bitacora.log', $linea, FILE_APPEND);
    }

    /**
     * Endpoint API REST: Retorna el catálogo completo de productos en formato JSON.
     * Acceso público y libre de sesiones o renderizado de layouts HTML.
     */
    public function apiIndex(): void
    {
        // Limpiar el búfer de salida para garantizar un JSON sin caracteres basura
        if (ob_get_length()) {
            ob_clean();
        }

        // Configuración de encabezados HTTP para una API REST pública
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: GET");

        try {
            // Se utiliza el método nativo de tu modelo para traer todos los registros
            $productos = $this->productoModel->obtenerTodos();

            if (!empty($productos)) {
                http_response_code(200);
                echo json_encode($productos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "No se encontraron productos registrados."], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Error interno en el servidor",
                "details" => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        
        exit; 
    }
}