<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: registro.php');
    exit;
}

$errores = [];
$tokenValido = isset($_POST['token'], $_SESSION['token_registro'])
    && is_string($_POST['token'])
    && is_string($_SESSION['token_registro'])
    && hash_equals($_SESSION['token_registro'], $_POST['token']);
unset($_SESSION['token_registro']);

if (!$tokenValido) {
    $errores[] = 'Token de seguridad inválido o ausente.';
}

$nombre = isset($_POST['nombre']) && is_string($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
$dni = isset($_POST['dni']) && is_string($_POST['dni']) ? trim($_POST['dni']) : '';
$contrasena = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? $_POST['contrasena'] : '';
$confirmacion = isset($_POST['confirmar_contrasena']) && is_string($_POST['confirmar_contrasena'])
    ? $_POST['confirmar_contrasena']
    : '';

if ($nombre === '' || $email === '' || $dni === '' || $contrasena === '' || $confirmacion === '') {
    $errores[] = 'Todos los campos son obligatorios.';
}
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errores[] = 'Ingresá un email válido.';
}
if ($nombre !== '' && mb_strlen($nombre, 'UTF-8') > 100) {
    $errores[] = 'El nombre no puede superar los 100 caracteres.';
}
if ($email !== '' && mb_strlen($email, 'UTF-8') > 100) {
    $errores[] = 'El email no puede superar los 100 caracteres.';
}
if ($dni !== '' && mb_strlen($dni, 'UTF-8') > 50) {
    $errores[] = 'El DNI no puede superar los 50 caracteres.';
}
if ($contrasena !== '' && strlen($contrasena) < 8) {
    $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
}
if (strlen($contrasena) > 72 || strlen($confirmacion) > 72) {
    $errores[] = 'La contraseña no puede superar los 72 caracteres.';
}
if ($contrasena !== $confirmacion) {
    $errores[] = 'Las contraseñas no coinciden.';
}

if ($errores === []) {
    require_once __DIR__ . '/includes/conexion.php';

    try {
        $conexion = conectarBaseDeDatos();
        $consulta = $conexion->prepare('SELECT dni, email FROM usuario WHERE dni = ? OR email = ? LIMIT 1');

        if ($consulta === false) {
            throw new RuntimeException('No se pudo preparar la consulta de validación.');
        }

        $consulta->bind_param('ss', $dni, $email);
        if (!$consulta->execute()) {
            $consulta->close();
            $conexion->close();
            throw new RuntimeException('No se pudo ejecutar la consulta de validación.');
        }
        $consulta->store_result();
        if ($consulta->num_rows > 0) {
            $errores[] = 'El DNI o email ya se encuentra registrado.';
        }
        $consulta->close();

        if ($errores === []) {
            $hashPassword = password_hash($contrasena, PASSWORD_DEFAULT);
            $insertar = $conexion->prepare('INSERT INTO usuario (nombre, email, dni, contrasenia) VALUES (?, ?, ?, ?)');
            if ($insertar === false) {
                throw new RuntimeException('No se pudo preparar el registro.');
            }

            $insertar->bind_param('ssss', $nombre, $email, $dni, $hashPassword);
            if (!$insertar->execute()) {
                if ($insertar->errno === 1062) {
                    $errores[] = 'El DNI o email ya se encuentra registrado.';
                } else {
                    throw new RuntimeException('No se pudo completar el registro.');
                }
            }
            $insertar->close();
        }

        $conexion->close();
    } catch (RuntimeException $exception) {
        error_log($exception->getMessage());
        $errores[] = 'El servicio no está disponible en este momento.';
    } catch (mysqli_sql_exception $exception) {
        error_log($exception->getMessage());
        $errores[] = 'El servicio no está disponible en este momento.';
    }
}

if ($errores !== []) {
    $_SESSION['error'] = $errores;
    header('Location: registro.php');
    exit;
}

$_SESSION['exito'] = 'Registro exitoso. Ya podés iniciar sesión.';
header('Location: index.php');
exit;
