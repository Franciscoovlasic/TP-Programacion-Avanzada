<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ejercicios.php';
requiereRol('ADMIN');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    include __DIR__ . '/formulario.php';
    exit;
}

$token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
$nombre = isset($_POST['nombre_ejercicio']) && is_string($_POST['nombre_ejercicio']) ? trim($_POST['nombre_ejercicio']) : '';

$_SESSION['ejercicio_formulario'] = [
    'id' => null,
    'nombre_ejercicio' => $nombre,
];

if (!validarTokenCsrf($token) || $nombre === '' || mb_strlen($nombre) > 100) {
    $_SESSION['mensaje'] = 'El nombre es obligatorio y debe tener hasta 100 caracteres.';
    header('Location: formulario.php');
    exit;
}

try {
    guardarEjercicio(null, $nombre);
    unset($_SESSION['ejercicio_formulario']);
    $_SESSION['mensaje'] = 'Ejercicio agregado correctamente.';
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    $_SESSION['mensaje'] = 'No se pudo guardar el ejercicio. Revisá los datos e intentá nuevamente.';
}
header('Location: index.php');
exit;
