<?php 
/**
 * Vista del formulario para modificar un producto existente.
 * Muestra los valores actuales precargados de forma segura mediante htmlspecialchars()
 * e incluye controles para la actualización opcional de archivos de imagen.
 */
require_once __DIR__ . '/../layouts/header.php'; ?>

<h2>Editar producto</h2>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<form action="index.php?route=productos/update" method="POST" enctype="multipart/form-data">
    
    <input type="hidden" name="id" value="<?= (int)$producto['id']; ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">

    <div class="mb-3">
        <label class="form-label">Imagen actual / Cambiar imagen</label>
        <?php if (!empty($producto['imagen'])): ?>
            <div class="mb-2">
                <img src="views/img/<?= htmlspecialchars($producto['imagen']); ?>" alt="Actual" style="max-height: 100px; object-fit: cover;" class="img-thumbnail">
            </div>
        <?php endif; ?>
        <input type="file" name="imagen" id="imagen" accept="image/*" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($producto['sku']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($producto['descripcion']); ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Precio compra</label>
        <input type="number" step="0.01" name="precio_compra" class="form-control" value="<?= htmlspecialchars((string)$producto['precio_compra']); ?>" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Precio venta</label>
        <input type="number" step="0.01" name="precio_venta" class="form-control"
               value="<?= htmlspecialchars((string)$producto['precio_venta']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Existencia</label>
        <input type="number" name="existencia" class="form-control" value="<?= (int)$producto['existencia']; ?>" required>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="index.php?route=productos" class="btn btn-secondary">Cancelar</a>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>