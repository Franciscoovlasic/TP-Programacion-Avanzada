<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ejercicios.php';
requiereRol('ADMIN');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$metodo = $_SERVER['REQUEST_METHOD'] ?? '';
if ($id === false || $id === null) { header('Location: index.php'); exit; }
if ($metodo === 'GET') {
    include __DIR__ . '/formulario.php';
    exit;
}
if ($metodo !== 'POST') { header('Location: index.php'); exit; }
$token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
$nombre = isset($_POST['nombre_ejercicio']) && is_string($_POST['nombre_ejercicio']) ? trim($_POST['nombre_ejercicio']) : '';
$_SESSION['ejercicio_formulario'] = [
    'id' => (int) $id,
    'nombre_ejercicio' => $nombre,
];
if (!validarTokenCsrf($token) || $nombre === '' || mb_strlen($nombre) > 100) { $_SESSION['mensaje'] = 'El nombre es obligatorio y debe tener hasta 100 caracteres.'; header('Location: formulario.php?id=' . (int) $id); exit; }
try { guardarEjercicio((int) $id, $nombre); unset($_SESSION['ejercicio_formulario']); $_SESSION['mensaje'] = 'Ejercicio modificado correctamente.'; } catch (RuntimeException $exception) { error_log($exception->getMessage()); $_SESSION['mensaje'] = 'No se pudo modificar el ejercicio.'; }
if (isset($_SESSION['ejercicio_formulario'])) { header('Location: formulario.php?id=' . (int) $id); exit; }
header('Location: index.php');
exit;
