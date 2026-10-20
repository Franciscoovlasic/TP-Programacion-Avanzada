<?php
require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/rutinas.php'; requiereRol('ADMIN');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); $editar = $id !== false && $id !== null; $rutina = $editar ? obtenerRutina((int) $id) : null;
if ($editar && $rutina === null) { $_SESSION['mensaje'] = 'La rutina no existe.'; header('Location: index.php'); exit; }
$datos = $_SESSION['rutina_formulario'] ?? null; if (is_array($datos) && (($datos['id'] ?? null) === ($editar ? (int) $id : null))) { $rutina = array_merge($rutina ?? [], $datos); unset($_SESSION['rutina_formulario']); }
$mensaje = $_SESSION['mensaje'] ?? null; unset($_SESSION['mensaje']); try { $usuarios = obtenerUsuariosRutina(); } catch (RuntimeException $exception) { error_log($exception->getMessage()); $usuarios = []; $mensaje = 'No se pudo cargar la lista de propietarios.'; }
$rutaBase = '../../'; $token = tokenCsrf(); include __DIR__ . '/../../includes/header.php';
?>
<main class="hero hero-result"><section class="login-card"><h1 class="login-title"><?= $editar ? 'Modificar rutina' : 'Agregar rutina' ?></h1><?php if ($mensaje): ?><p class="mensaje error"><?= htmlspecialchars((string) $mensaje, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><form action="<?= $editar ? 'editar.php?id=' . (int) $id : 'nuevo.php' ?>" method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label for="id_usuario">Usuario propietario</label>
            <select id="id_usuario" name="id_usuario" required>
                <option value="">Seleccioná un usuario</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= (int) $usuario['id_usuario'] ?>" <?= (int) ($rutina['id_usuario'] ?? 0) === (int) $usuario['id_usuario'] ? 'selected' : '' ?>><?= htmlspecialchars((string) $usuario['nombre'] . ' (' . (string) $usuario['dni'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="nombre_rutina">Nombre</label>
            <input id="nombre_rutina" name="nombre_rutina" maxlength="100" required value="<?= htmlspecialchars((string) ($rutina['nombre_rutina'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="5"><?= htmlspecialchars((string) ($rutina['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <button class="btn-primario" type="submit">Guardar</button>
    </form>

    <a class="btn-registro" href="index.php">Cancelar</a>
</section></main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
