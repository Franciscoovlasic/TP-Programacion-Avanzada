<?php
require_once __DIR__ . '/../includes/auth.php';
requiereRol('USUARIO');
$rutaBase = '../';
include __DIR__ . '/../includes/header.php';
?>
<main class="hero hero-result">
    <section class="catalogo-panel alumno-home">
        <p class="eyebrow">FORJA GYM · ÁREA PERSONAL</p>
        <h1 class="login-title">¡Bienvenido/a, <?= htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p class="resultado-texto">Gestioná tus datos personales y consultá las rutinas que tu entrenador preparó para vos.</p>
        <nav class="alumno-tabs" aria-label="Secciones del alumno">
            <a class="alumno-tab activo" href="perfil.php"><span class="tab-icon">◉</span><span><strong>Mi perfil</strong><small>Tus datos personales</small></span></a>
            <a class="alumno-tab" href="rutinas.php"><span class="tab-icon">▤</span><span><strong>Mi rutina</strong><small>Rutina actual e historial</small></span></a>
        </nav>
        <div class="alumno-atajos"><a class="btn-secundario" href="perfil.php">Editar mi perfil</a><a class="btn-registro" href="rutinas.php">Ver mi rutina actual</a></div>
    </section>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
