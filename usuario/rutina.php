<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/rutinas.php'; requiereRol(['USUARIO', 'ADMIN']);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); $rutina = $id ? obtenerRutina((int) $id, (int) $_SESSION['id_usuario']) : null;
if ($rutina === null) { $_SESSION['mensaje'] = 'La rutina no existe o no está asociada a tu usuario.'; header('Location: rutinas.php'); exit; }
$rutaBase = '../'; include __DIR__ . '/../includes/header.php';
?>
<main class="hero hero-result"><section class="login-card"><h1 class="login-title"><?= htmlspecialchars((string) $rutina['nombre_rutina'], ENT_QUOTES, 'UTF-8') ?></h1><p class="resultado-texto"><?= nl2br(htmlspecialchars((string) ($rutina['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p><a class="btn-registro" href="rutinas.php">Volver a mis rutinas</a></section></main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
