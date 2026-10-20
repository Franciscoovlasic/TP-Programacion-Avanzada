<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ejercicios.php';
requiereRol('ADMIN');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editar = $id !== false && $id !== null;
$ejercicio = $editar ? obtenerEjercicio((int) $id) : null;
if ($editar && $ejercicio === null) { $_SESSION['mensaje'] = 'El ejercicio no existe.'; header('Location: index.php'); exit; }
$datosFormulario = $_SESSION['ejercicio_formulario'] ?? null;
if (is_array($datosFormulario) && (($datosFormulario['id'] ?? null) === ($editar ? (int) $id : null))) {
    $ejercicio = [
        'nombre_ejercicio' => (string) ($datosFormulario['nombre_ejercicio'] ?? ''),
    ];
    unset($_SESSION['ejercicio_formulario']);
}
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);
$rutaBase = '../../';
$token = tokenCsrf();
include __DIR__ . '/../../includes/header.php';
?>
<main class="hero hero-result"><section class="login-card">
    <h1 class="login-title"><?= $editar ? 'Modificar ejercicio' : 'Agregar ejercicio' ?></h1>
    <?php if ($mensaje): ?><p class="mensaje error"><?= htmlspecialchars((string) $mensaje, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form action="<?= $editar ? 'editar.php' : 'nuevo.php' ?><?= $editar ? '?id=' . (int) $id : '' ?>" method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label for="nombre_ejercicio">Nombre</label>
            <input id="nombre_ejercicio" name="nombre_ejercicio" maxlength="100" required value="<?= htmlspecialchars((string) ($ejercicio['nombre_ejercicio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <button class="btn-primario" type="submit">Guardar</button>
    </form>

    <a class="btn-registro" href="index.php">Cancelar</a>
</section></main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
