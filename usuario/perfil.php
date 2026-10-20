<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
requiereRol('USUARIO');
use App\Core\Database;
$db = Database::connection();
$id = (int) $_SESSION['id_usuario'];
$mensaje = '';
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
    $nombre = trim(is_string($_POST['nombre'] ?? null) ? $_POST['nombre'] : '');
    $apellido = trim(is_string($_POST['apellido'] ?? null) ? $_POST['apellido'] : '');
    $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
    $pesoRaw = trim(is_string($_POST['peso_kg'] ?? null) ? $_POST['peso_kg'] : '');
    $alturaRaw = trim(is_string($_POST['altura_cm'] ?? null) ? $_POST['altura_cm'] : '');
    $peso = $pesoRaw === '' ? null : filter_var($pesoRaw, FILTER_VALIDATE_FLOAT);
    $altura = $alturaRaw === '' ? null : filter_var($alturaRaw, FILTER_VALIDATE_FLOAT);
    if (!validarTokenCsrf($token)) $error = 'La sesión del formulario venció. Actualizá la página e intentá nuevamente.';
    elseif ($nombre === '' || mb_strlen($nombre) > 100) $error = 'Ingresá un nombre de hasta 100 caracteres.';
    elseif ($apellido === '' || mb_strlen($apellido) > 100) $error = 'Ingresá un apellido de hasta 100 caracteres.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) $error = 'Ingresá un email válido de hasta 100 caracteres.';
    elseif ($pesoRaw !== '' && ($peso === false || $peso <= 0 || $peso > 500)) $error = 'El peso debe ser un número entre 0 y 500 kg.';
    elseif ($alturaRaw !== '' && ($altura === false || $altura <= 0 || $altura > 300)) $error = 'La altura debe ser un número entre 0 y 300 cm.';
    else {
        $stmt = $db->prepare('UPDATE usuario SET nombre = ?, apellido = ?, email = ?, peso_kg = ?, altura_cm = ? WHERE id_usuario = ?');
        if ($stmt) {
            $pesoParam = $peso === false ? null : $peso;
            $alturaParam = $altura === false ? null : $altura;
            $stmt->bind_param('sssddi', $nombre, $apellido, $email, $pesoParam, $alturaParam, $id);
            if ($stmt->execute()) {
                $_SESSION['nombre'] = $nombre;
                $_SESSION['apellido'] = $apellido;
                $mensaje = 'Tus datos se actualizaron correctamente.';
            } else {
                $error = $stmt->errno === 1062 ? 'Ese email ya está registrado en otra cuenta.' : 'No se pudieron guardar los cambios. Intentá nuevamente.';
            }
            $stmt->close();
        } else $error = 'No se pudo preparar la actualización de tus datos.';
    }
}
$stmt = $db->prepare('SELECT nombre, apellido, email, dni, peso_kg, altura_cm FROM usuario WHERE id_usuario = ? LIMIT 1');
$stmt->bind_param('i', $id); $stmt->execute(); $perfil = $stmt->get_result()->fetch_assoc() ?: []; $stmt->close();
$rutaBase = '../';
include __DIR__ . '/../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel alumno-home">
    <p class="eyebrow">FORJA GYM · ÁREA PERSONAL</p><h1 class="login-title">Mi perfil</h1>
    <p class="resultado-texto">Mantené tus datos actualizados. El peso se expresa en kilogramos y la altura en centímetros.</p>
    <nav class="alumno-tabs" aria-label="Secciones del alumno"><a class="alumno-tab activo" href="perfil.php"><span class="tab-icon">◉</span><span><strong>Mi perfil</strong><small>Tus datos personales</small></span></a><a class="alumno-tab" href="rutinas.php"><span class="tab-icon">▤</span><span><strong>Mi rutina</strong><small>Rutina actual e historial</small></span></a></nav>
    <?php if ($mensaje !== ''): ?><p class="mensaje exito"><?= e($mensaje) ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="perfil-form"><input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>">
        <div class="perfil-grid">
            <div class="form-group"><label for="nombre">Nombre</label><input id="nombre" name="nombre" maxlength="100" required autocomplete="given-name" value="<?= e($perfil['nombre'] ?? '') ?>"></div>
            <div class="form-group"><label for="apellido">Apellido</label><input id="apellido" name="apellido" maxlength="100" required autocomplete="family-name" value="<?= e($perfil['apellido'] ?? '') ?>"></div>
            <div class="form-group"><label for="email">Correo electrónico</label><input id="email" type="email" name="email" maxlength="100" required value="<?= e($perfil['email'] ?? '') ?>"></div>
            <div class="form-group"><label for="dni">DNI</label><input id="dni" value="<?= e($perfil['dni'] ?? '') ?>" disabled><small class="campo-ayuda">El DNI no se puede modificar desde el perfil.</small></div>
            <div class="form-group"><label for="peso_kg">Peso (kg)</label><input id="peso_kg" type="number" data-stepper name="peso_kg" min="0.1" max="500" step="0.1" placeholder="Ej. 75.5" value="<?= e($perfil['peso_kg'] ?? '') ?>"></div>
            <div class="form-group"><label for="altura_cm">Altura (cm)</label><input id="altura_cm" type="number" data-stepper name="altura_cm" min="1" max="300" step="0.1" placeholder="Ej. 175" value="<?= e($perfil['altura_cm'] ?? '') ?>"></div>
        </div>
        <div class="perfil-actions"><button class="btn-primario btn-auto" type="submit">Guardar cambios</button><a class="btn-secundario" href="index.php">Volver</a></div>
    </form>
</section></main>
<script src="<?= e($rutaBase) ?>js/stepper.js" defer></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
