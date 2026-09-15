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

// Credenciales válidas (fijas, según consigna del TP)
$usuarioValido    = "fcytuader";
$contrasenaValida = "programacionavanzada";

// Solo se procesa si llega por POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header("Location: form.php");
    exit;
}

$usuario    = isset($_POST['usuario']) && is_string($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasena = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

// Validación server-side: nunca confiar solo en la validación de JS
if ($usuario === '' || $contrasena === '') {
    $errores[] = "Debe completar usuario y contraseña.";
} elseif ($usuario !== $usuarioValido || $contrasena !== $contrasenaValida) {
    // Usuario o contraseña incorrectos: se informa y se vuelve al form inicial
    $errores[] = "Usuario o contraseña incorrectos.";
}

if ($errores !== []) {
    $_SESSION['error'] = $errores;
    header("Location: form.php");
    exit;
}

// Comprobación de usuario y contraseña
if ($usuario === $usuarioValido && $contrasena === $contrasenaValida) {
    session_regenerate_id(true);
    $_SESSION['usuario'] = $usuario;
    unset($_SESSION['error']);

    header('Location: inicio.php');
    exit;

}
?>
