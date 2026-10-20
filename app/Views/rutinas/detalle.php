<?php
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result"><section class="login-card">
    <h1 class="login-title"><?= e($rutina['nombre_rutina']) ?></h1>
    <p class="resultado-texto"><strong>Propietario:</strong> <?= e($rutina['nombre_usuario'] . ' (' . $rutina['dni'] . ')') ?></p>
    <p class="resultado-texto"><?= nl2br(e($rutina['descripcion'] ?? '')) ?></p>
    <a class="btn-registro" href="index.php">Volver a rutinas</a>
</section></main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
