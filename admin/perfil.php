<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
requiereRol('ADMIN');

use App\Core\Database;

$db = Database::connection();
$id = (int) $_SESSION['id_usuario'];
$mensaje = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = is_string($_POST['token'] ?? null) ? $_POST['token'] : '';
    $nombre = trim(is_string($_POST['nombre'] ?? null) ? $_POST['nombre'] : '');
    $apellido = trim(is_string($_POST['apellido'] ?? null) ? $_POST['apellido'] : '');
    $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');

    if (!validarTokenCsrf($token)) {
        $error = 'La sesión del formulario venció. Actualizá la página e intentá nuevamente.';
    } elseif ($nombre === '' || mb_strlen($nombre) > 100) {
        $error = 'Ingresá un nombre de hasta 100 caracteres.';
    } elseif ($apellido === '' || mb_strlen($apellido) > 100) {
        $error = 'Ingresá un apellido de hasta 100 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
        $error = 'Ingresá un email válido de hasta 100 caracteres.';
    } else {
        $stmt = $db->prepare('UPDATE usuario SET nombre = ?, apellido = ?, email = ? WHERE id_usuario = ? AND rol = \'ADMIN\'');

        if ($stmt) {
            $stmt->bind_param('sssi', $nombre, $apellido, $email, $id);

            if ($stmt->execute()) {
                // El encabezado del sitio lee estos datos de la sesión.
                $_SESSION['nombre'] = $nombre;
                $_SESSION['apellido'] = $apellido;
                $mensaje = 'Tus datos se actualizaron correctamente.';
            } else {
                $error = $stmt->errno === 1062
                    ? 'Ese email ya está registrado en otra cuenta.'
                    : 'No se pudieron guardar los cambios. Intentá nuevamente.';
            }

            $stmt->close();
        } else {
            $error = 'No se pudo preparar la actualización de tus datos.';
        }
    }
}

$perfil = [];
$stmt = $db->prepare('SELECT nombre, apellido, email, dni FROM usuario WHERE id_usuario = ? LIMIT 1');

if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $perfil = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();
}

// Si el envío falló, se conservan los valores escritos para no hacer retipear.
$valorNombre = $error !== '' && isset($nombre) ? $nombre : ($perfil['nombre'] ?? '');
$valorApellido = $error !== '' && isset($apellido) ? $apellido : ($perfil['apellido'] ?? '');
$valorEmail = $error !== '' && isset($email) ? $email : ($perfil['email'] ?? '');

$nombreMostrado = nombreCompleto($perfil['nombre'] ?? '', $perfil['apellido'] ?? '');
$iniciales = mb_strtoupper(
    mb_substr((string) ($perfil['nombre'] ?? ''), 0, 1) . mb_substr((string) ($perfil['apellido'] ?? ''), 0, 1)
);

$rutaBase = '../';
$tituloPagina = 'FORJA GYM - Mi perfil de administrador';
$tabActiva = 'perfil';
include __DIR__ . '/../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel admin-alumnos-panel">
    <header class="admin-encabezado">
        <p class="eyebrow">FORJA GYM · ADMINISTRACIÓN</p>
        <h1 class="login-title">Mi perfil</h1>
        <p class="resultado-texto">Consultá y actualizá los datos de tu cuenta de administrador.</p>
    </header>

    <?php include __DIR__ . '/../includes/admin_tabs.php'; ?>

    <div class="admin-perfil">
        <?php if ($mensaje !== ''): ?><p class="mensaje exito" role="status"><?= e($mensaje) ?></p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="mensaje error" role="alert"><?= e($error) ?></p><?php endif; ?>

        <div class="perfil-resumen">
            <span class="perfil-avatar" aria-hidden="true"><?= e($iniciales !== '' ? $iniciales : 'AD') ?></span>
            <div>
                <h2><?= e($nombreMostrado !== '' ? $nombreMostrado : 'Administrador') ?></h2>
                <span class="chip-rol">ADMINISTRADOR</span>
                <p><?= e($perfil['email'] ?? '') ?> · DNI <?= e($perfil['dni'] ?? '') ?></p>
            </div>
        </div>

        <form method="post" class="perfil-form">
            <input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
            <div class="perfil-grid">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" maxlength="100" required autocomplete="given-name" value="<?= e($valorNombre) ?>">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" name="apellido" maxlength="100" required autocomplete="family-name" value="<?= e($valorApellido) ?>">
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input id="email" type="email" name="email" maxlength="100" required autocomplete="email" value="<?= e($valorEmail) ?>">
                </div>
                <div class="form-group">
                    <label for="dni">DNI</label>
                    <input id="dni" value="<?= e($perfil['dni'] ?? '') ?>" disabled>
                    <small class="campo-ayuda">El DNI no se puede modificar desde el perfil.</small>
                </div>
            </div>
            <div class="perfil-actions">
                <button class="btn-primario btn-auto" type="submit">Guardar cambios</button>
                <a class="btn-secundario" href="index.php">Ir al panel</a>
            </div>
        </form>
    </div>
</section></main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
