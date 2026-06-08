<?php
namespace Models;

use Config\Database;
use PDO;
use PDOException;

class ProductoModel
{
    private PDO $conexion;

    /**
     * Establece la conexión interna con la base de datos MySQL al instanciar el modelo.
     */
    public function __construct()
    {
        $db = new Database();
        $this->conexion = $db->connect();
    }

    /**
     * Obtiene todos los productos registrados ordenados de forma descendente por ID.
     */
    public function obtenerTodos(): array
    {
        try {
            $sql = 'SELECT * FROM productos ORDER BY id DESC';
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Busca productos por coincidencia en nombre o descripción para la tienda pública.
     */
    public function buscarPublico(string $termino = ''): array
    {
        try {
            if (trim($termino) === '') {
                return $this->obtenerTodos();
            }

            $sql = 'SELECT * FROM productos WHERE nombre LIKE :termino OR descripcion LIKE :termino ORDER BY id DESC';
            $stmt = $this->conexion->prepare($sql);
            $busqueda = '%' . $termino . '%';
            $stmt->bindParam(':termino', $busqueda);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Recupera la información completa de un producto específico mediante su ID.
     */
    public function obtenerPorId(int $id): ?array
    {
        try {
            $sql = 'SELECT * FROM productos WHERE id = :id LIMIT 1';
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $producto = $stmt->fetch();
            return $producto ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Verifica la existencia de un SKU en la base de datos para evitar registros duplicados.
     */
    public function verificarSkuDuplicado(string $sku, int $idExcluir = 0): bool
    {
        try {
            $sql = 'SELECT COUNT(*) FROM productos WHERE sku = :sku';
            if ($idExcluir > 0) {
                $sql .= ' AND id != :id_excluir';
            }

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':sku', $sku);
            
            if ($idExcluir > 0) {
                $stmt->bindParam(':id_excluir', $idExcluir, PDO::PARAM_INT);
            }

            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return true; 
        }
    }

    /**
     * Inserta un nuevo producto usando transacciones seguras para proteger la consistencia.
     */
    public function crear(array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            $sql = 'INSERT INTO productos (sku, nombre, descripcion, precio_compra, precio_venta, existencia, imagen)
                    VALUES (:sku, :nombre, :descripcion, :precio_compra, :precio_venta, :existencia, :imagen)';
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':precio_compra', $data['precio_compra']);
            $stmt->bindParam(':precio_venta', $data['precio_venta']);
            $stmt->bindParam(':existencia', $data['existencia'], PDO::PARAM_INT);
            $stmt->bindParam(':imagen', $data['imagen']); 

            $resultado = $stmt->execute();
            if (!$resultado) {
                $this->conexion->rollBack();
                return false;
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    /**
     * Actualiza las propiedades de un producto existente aplicando una transacción segura.
     */
    public function actualizar(int $id, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            $sql = 'UPDATE productos SET
                        sku = :sku,
                        nombre = :nombre,
                        descripcion = :descripcion,
                        precio_compra = :precio_compra,
                        precio_venta = :precio_venta,
                        existencia = :existencia,
                        imagen = :imagen
                    WHERE id = :id';

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':precio_compra', $data['precio_compra']);
            $stmt->bindParam(':precio_venta', $data['precio_venta']);
            $stmt->bindParam(':existencia', $data['existencia'], PDO::PARAM_INT);
            $stmt->bindParam(':imagen', $data['imagen']); 
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    /**
     * Borra físicamente un registro de producto confirmando que existan filas afectadas.
     */
    public function eliminar(int $id): bool
    {
        try {
            $this->conexion->beginTransaction();
            $sql = 'DELETE FROM productos WHERE id = :id';
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->conexion->rollBack();
                return false;
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    /**
     * Recupera un lote segmentado de productos basado en un límite y un desplazamiento (Paginación).
     */
    public function obtenerPorPagina(int $limite, int $offset): array
    {
        try {
            $sql = 'SELECT * FROM productos ORDER BY id DESC LIMIT :limite OFFSET :offset';
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Cuenta la cantidad total de registros de productos para calcular el número de páginas.
     */
    public function contarTotal(): int
    {
        try {
            $sql = 'SELECT COUNT(*) FROM productos';
            $stmt = $this->conexion->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}