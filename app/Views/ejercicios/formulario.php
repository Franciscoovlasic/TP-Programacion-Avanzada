<?php
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result"><section class="login-card">
    <h1 class="login-title"><?= $editar ? 'Modificar ejercicio' : 'Agregar ejercicio' ?></h1>
    <?php if ($mensaje): ?><p class="mensaje error"><?= e($mensaje) ?></p><?php endif; ?>
    <form action="<?= $editar ? 'editar.php?id=' . (int) $id : 'nuevo.php' ?>" method="post">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="form-group">
            <label for="nombre_ejercicio">Nombre</label>
            <input id="nombre_ejercicio" name="nombre_ejercicio" maxlength="100" required value="<?= e($ejercicio['nombre_ejercicio'] ?? '') ?>">
        </div>

        <button class="btn-primario" type="submit">Guardar</button>
    </form>

    <a class="btn-registro" href="index.php">Cancelar</a>
</section></main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
