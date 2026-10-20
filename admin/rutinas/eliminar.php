<?php
require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/rutinas.php'; requiereRol('ADMIN');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Location: index.php'); exit; }
$token = is_string($_POST['token'] ?? null) ? $_POST['token'] : ''; $id = filter_var($_POST['id_rutina'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!validarTokenCsrf($token) || $id === false || $id === null) { $_SESSION['mensaje'] = 'La solicitud no es válida.'; header('Location: index.php'); exit; }
try { $_SESSION['mensaje'] = eliminarRutina((int) $id) ? 'Rutina eliminada correctamente.' : 'La rutina no existe.'; } catch (RuntimeException $exception) { error_log($exception->getMessage()); $_SESSION['mensaje'] = 'No se pudo eliminar la rutina en este momento.'; }
header('Location: index.php'); exit;
