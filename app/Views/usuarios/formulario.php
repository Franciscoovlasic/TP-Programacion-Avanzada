<?php
include __DIR__ . '/../../../includes/header.php';

$nombreUsuario = nombreCompleto($usuario['nombre'] ?? '', $usuario['apellido'] ?? '');
$idUsuario = (int) $usuario['id_usuario'];
?>
<main class="hero hero-result"><section class="login-card">
    <h1 class="login-title">Editar usuario</h1>
    <p class="resultado-texto">Usuario: <strong><?= e($nombreUsuario) ?></strong></p>
    <?php foreach ($errores as $error): ?><p class="mensaje error"><?= e($error) ?></p><?php endforeach; ?>

    <form action="<?= e($rutaBase) ?>admin/usuarios/editar.php?id=<?= $idUsuario ?>" method="post">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="form-group">
            <label for="usuario_nombre">Nombre</label>
            <input id="usuario_nombre" name="nombre" maxlength="100" required value="<?= e($valores['nombre'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="usuario_email">Email</label>
            <input id="usuario_email" type="email" name="email" maxlength="100" required value="<?= e($valores['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="usuario_dni">DNI</label>
            <input id="usuario_dni" name="dni" maxlength="50" required value="<?= e($valores['dni'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="usuario_rol">Rol</label>
            <select id="usuario_rol" name="rol" required>
                <?php foreach (ROLES_VALIDOS as $rol): ?>
                    <option value="<?= e($rol) ?>" <?= ($valores['rol'] ?? '') === $rol ? 'selected' : '' ?>><?= e($rol) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="usuario_contrasena">Nueva contraseña (opcional)</label>
            <input id="usuario_contrasena" type="password" name="contrasena" maxlength="72" autocomplete="new-password">
            <small class="campo-ayuda">Dejala vacía para mantener la contraseña actual. Si la completás, debe tener entre 8 y 72 caracteres.</small>
        </div>

        <button class="btn-primario" type="submit">Guardar cambios</button>
    </form>

    <a class="btn-registro" href="<?= e($rutaBase) ?>admin/index.php">Cancelar</a>
</section></main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
