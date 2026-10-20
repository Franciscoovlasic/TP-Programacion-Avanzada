<?php
require_once __DIR__ . '/includes/auth.php';
requiereAutenticacion();

include 'includes/header.php';
?>

<main class="hero hero-result">
    <div class="login-card">
        <?php if (isset($_SESSION['error'])): ?>
            <?php foreach ((array) $_SESSION['error'] as $error): ?>
                <p class="mensaje error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; unset($_SESSION['error']); ?>
        <?php else: ?>
            <p class="mensaje exito">Ingreso correcto.</p>
        <?php endif; ?>
        <p class="resultado-texto">Bienvenido/a, <?php echo htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8'); ?>.</p>
        <p class="resultado-texto">Rol: <strong><?= htmlspecialchars($_SESSION['rol'], ENT_QUOTES, 'UTF-8') ?></strong></p>
        <nav aria-label="Áreas disponibles">
            <?php if ($_SESSION['rol'] === 'ADMIN'): ?>
                <a class="btn-registro" href="admin/index.php">Ir al área ADMIN</a>
            <?php elseif ($_SESSION['rol'] === 'USUARIO'): ?>
                <a class="btn-registro" href="usuario/index.php">Ir al área de usuario</a>
            <?php endif; ?>
        </nav>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
