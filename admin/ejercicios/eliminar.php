<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ejercicios.php';
requiereRol('ADMIN');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Location: index.php'); exit; }
$token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
$id = filter_input(INPUT_POST, 'id_ejercicio', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!validarTokenCsrf($token) || $id === false || $id === null) { $_SESSION['mensaje'] = 'La solicitud no es válida.'; header('Location: index.php'); exit; }
try { $_SESSION['mensaje'] = eliminarEjercicio((int) $id) ? 'Ejercicio eliminado correctamente.' : 'No se puede eliminar porque el ejercicio ya está asignado a una rutina.'; } catch (RuntimeException $exception) { error_log($exception->getMessage()); $_SESSION['mensaje'] = 'No se pudo eliminar el ejercicio en este momento.'; }
header('Location: index.php');
exit;
