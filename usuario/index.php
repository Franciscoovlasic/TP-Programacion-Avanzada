<?php
require_once __DIR__ . '/../includes/auth.php';
// El área de usuario admite ambos roles de forma explícita; ADMIN no obtiene
// permisos administrativos por acceder aquí.
requiereRol(['USUARIO', 'ADMIN']);
$rutaBase = '../';
include __DIR__ . '/../includes/header.php';
?>

<main class="hero hero-result">
    <div class="login-card">
        <h1 class="login-title">Área de usuario</h1>
        <p class="resultado-texto">Bienvenido/a, <?= htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8') ?>.</p>
        <p class="resultado-texto">Consultá los ejercicios disponibles para usarlos posteriormente en tus rutinas.</p>
        <a class="btn-registro" href="ejercicios.php">Consultar catálogo de ejercicios</a>
        <a class="btn-registro" href="rutinas.php">Consultar mis rutinas</a>
        <a class="btn-registro" href="../inicio.php">Volver al inicio</a>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
