<?php 
/**
 * Vista de administración de productos (Panel de Control).
 * Despliega el catálogo completo en formato de tabla interactiva con accesos 
 * directos para operaciones CRUD y formularios de eliminación protegidos por CSRF.
 */
require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Administración de productos</h2>
    <div>
        <a href="index.php?route=productos/create" class="btn btn-success">Nuevo producto</a>
        <a href="index.php?route=logout" class="btn btn-danger">Cerrar sesión</a>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>SKU</th>
            <th>Nombre</th>
            <th>Precio compra</th>
            <th>Precio venta</th>
            <th>Existencia</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?= (int)$producto['id']; ?></td>
                    <td><?= htmlspecialchars($producto['sku']); ?></td>
                    <td><?= htmlspecialchars($producto['nombre']); ?></td>
                    <td>$<?= number_format((float)$producto['precio_compra'], 2); ?></td>
                    <td>$<?= number_format((float)$producto['precio_venta'], 2); ?></td>
                    <td><?= (int)$producto['existencia']; ?></td>
                    <td>
                        <a href="index.php?route=productos/edit&id=<?= (int)$producto['id']; ?>" 
                           class="btn btn-primary btn-sm">Editar</a>

                        <form action="index.php?route=productos/delete" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
                            <input type="hidden" name="id" value="<?= (int)$producto['id']; ?>">
                            
                            <button type="submit" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('¿Deseas eliminar este producto?');">
                                Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center text-muted">No hay productos registrados en la base de datos.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>