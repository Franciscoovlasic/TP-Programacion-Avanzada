<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_POST['token']) ||
    !is_string($_POST['token']) ||
    !isset($_SESSION['token']) ||
    !is_string($_SESSION['token']) ||
    !hash_equals($_SESSION['token'], $_POST['token'])
) {
    $_SESSION['error'] = "Token de seguridad inválido o ausente.";
    header("Location: index.php");
    exit;
}

unset($_SESSION['token']);

// Credenciales válidas (fijas, según consigna del TP)
$usuarioValido    = "fcytuader";
$contrasenaValida = "programacionavanzada";

// Solo se procesa si llega por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$usuario    = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

// Validación server-side: nunca confiar solo en la validación de JS
if ($usuario === '' || $contrasena === '') {
    $_SESSION['error'] = "Debe completar usuario y contraseña.";
    header("Location: index.php");
    exit;
}

// Comprobación de usuario y contraseña
if ($usuario === $usuarioValido && $contrasena === $contrasenaValida) {
    session_regenerate_id(true);
    $_SESSION['usuario'] = $usuario;

    header('Location: inicio.php');
    exit;

} else {
    // Usuario o contraseña incorrectos: se informa y se vuelve al form inicial
    $_SESSION['error'] = "Usuario o contraseña incorrectos.";
    header("Location: index.php");
    exit;
}
?>
