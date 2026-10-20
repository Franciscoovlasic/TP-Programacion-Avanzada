<?php
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result"><section class="login-card">
    <h1 class="login-title"><?= $editar ? 'Modificar rutina' : 'Agregar rutina' ?></h1>
    <?php if ($mensaje): ?><p class="mensaje error"><?= e($mensaje) ?></p><?php endif; ?>
    <form action="<?= $editar ? 'editar.php?id=' . (int) $id : 'nuevo.php' ?>" method="post">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="form-group">
            <label for="id_usuario">Usuario propietario</label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">Seleccioná un usuario</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= (int) $usuario['id_usuario'] ?>" <?= (int) ($rutina['id_usuario'] ?? 0) === (int) $usuario['id_usuario'] ? 'selected' : '' ?>><?= e($usuario['nombre'] . ' (' . $usuario['dni'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="nombre_rutina">Nombre</label>
            <input id="nombre_rutina" name="nombre_rutina" maxlength="100" required value="<?= e($rutina['nombre_rutina'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="5"><?= e($rutina['descripcion'] ?? '') ?></textarea>
        </div>

        <button class="btn-primario" type="submit">Guardar</button>
    </form>

    <a class="btn-registro" href="index.php">Cancelar</a>
</section></main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
