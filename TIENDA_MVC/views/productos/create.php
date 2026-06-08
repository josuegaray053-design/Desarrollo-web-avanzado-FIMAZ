<?php 
/**
 * Vista del formulario para registrar nuevos productos en el sistema.
 * Incluye validación por token CSRF y soporte para carga de archivos multimedia.
 */
require_once __DIR__ . '/../layouts/header.php'; ?>

<h2>Registrar producto</h2>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<form action="index.php?route=productos/store" method="POST" enctype="multipart/form-data">
    
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? ''; ?>">
    
    <div class="form-group mb-3">
        <label for="imagen" class="form-label">Agrega imagen:</label>
        <input type="file" name="imagen" id="imagen" accept="image/*" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Precio compra</label>
        <input type="number" step="0.01" name="precio_compra" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Precio venta</label>
        <input type="number" step="0.01" name="precio_venta" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Existencia</label>
        <input type="number" name="existencia" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="index.php?route=productos" class="btn btn-secondary">Cancelar</a>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>