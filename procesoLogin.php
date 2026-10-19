<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errores = [];
$tokenInvalido = (
    !isset($_POST['token']) ||
    !is_string($_POST['token']) ||
    !isset($_SESSION['token']) ||
    !is_string($_SESSION['token']) ||
    !hash_equals($_SESSION['token'], $_POST['token'])
);

if ($tokenInvalido) {
    $errores[] = "Token de seguridad inválido o ausente.";
}

unset($_SESSION['token']);

$captchaInput = isset($_POST['captcha']) && is_string($_POST['captcha'])
    ? strtoupper(trim($_POST['captcha']))
    : '';
$captchaCode = isset($_SESSION['captcha_code']) && is_string($_SESSION['captcha_code'])
    ? $_SESSION['captcha_code']
    : '';

if ($captchaInput === '' || $captchaCode === '' || !hash_equals($captchaCode, $captchaInput)) {
    $errores[] = "CAPTCHA inválido o ausente.";
}

unset($_SESSION['captcha_code']);

// Solo se procesa si llega por POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header("Location: index.php");
    exit;
}

$dni = isset($_POST['dni']) && is_string($_POST['dni']) ? trim($_POST['dni']) : '';
$contrasena = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? $_POST['contrasena'] : '';

// Validación server-side: nunca confiar solo en la validación de JS
if ($dni === '' || $contrasena === '') {
    $errores[] = 'DNI o contraseña incorrectos.';
}

if ($errores === []) {
    require_once __DIR__ . '/includes/conexion.php';

    try {
        $conexion = conectarBaseDeDatos();
        $consulta = $conexion->prepare(
            'SELECT id_usuario, nombre, dni, password FROM usuario WHERE dni = ? LIMIT 1'
        );

        if ($consulta === false) {
            throw new RuntimeException('No se pudo preparar la consulta de autenticación.');
        }

        $consulta->bind_param('s', $dni);
        if (!$consulta->execute()) {
            $consulta->close();
            $conexion->close();
            throw new RuntimeException('No se pudo ejecutar la consulta de autenticación.');
        }
        $consulta->bind_result($idUsuario, $nombre, $dniRegistrado, $hashPassword);

        $usuarioEncontrado = $consulta->fetch();
        $consulta->close();
        $conexion->close();

        if (!$usuarioEncontrado || !password_verify($contrasena, $hashPassword)) {
            $errores[] = 'DNI o contraseña incorrectos.';
        } else {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = (int) $idUsuario;
            $_SESSION['dni'] = $dniRegistrado;
            $_SESSION['nombre'] = $nombre;
            unset($_SESSION['error']);

            header('Location: inicio.php');
            exit;
        }
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
    header("Location: index.php");
    exit;
}
?>
