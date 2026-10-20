<?php
require_once __DIR__ . '/../includes/auth.php';
requiereRol('ADMIN');
$rutaBase = '../';
include __DIR__ . '/../includes/header.php';
?>

<main class="hero hero-result">
    <div class="login-card">
        <h1 class="login-title">Área ADMIN</h1>
        <p class="resultado-texto">Bienvenido/a, <?= htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8') ?>.</p>
        <p class="resultado-texto">Este es el punto de entrada protegido para la administración.</p>
        <a class="btn-registro" href="ejercicios/index.php">Administrar ejercicios</a>
        <a class="btn-registro" href="rutinas/index.php">Administrar rutinas</a>
        <a class="btn-registro" href="../inicio.php">Volver al inicio</a>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
