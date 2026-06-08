<?php
namespace Controllers;

use Models\ProductoModel;

class PublicController
{
    /**
     * Controla la vista pública del catálogo de la tienda, gestionando
     * el buscador de productos y la paginación dinámica de 4 en 4.
     */
    public function catalogo(): void
    {
        $termino = trim($_GET['buscar'] ?? '');
        $productoModel = new ProductoModel();

        $porPagina = 4;
        $paginaActual = (int)($_GET['pagina'] ?? 1);
        if ($paginaActual < 1) { 
            $paginaActual = 1; 
        }

        if ($termino !== '') {
            $todosLosResultadosBusqueda = $productoModel->buscarPublico($termino);
            $totalProductos = count($todosLosResultadosBusqueda);

            $offset = ($paginaActual - 1) * $porPagina;

            $productos = array_slice($todosLosResultadosBusqueda, $offset, $porPagina);
        } else {
            $totalProductos = $productoModel->contarTotal();
            
            $offset = ($paginaActual - 1) * $porPagina;
            $productos = $productoModel->obtenerPorPagina($porPagina, $offset);
        }

        $totalPaginas = (int)ceil($totalProductos / $porPagina);

        require_once __DIR__ . '/../views/public/catalogo.php';
    }
}
?>