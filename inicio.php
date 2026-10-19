<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<main class="hero hero-result">
    <div class="login-card">
        <p class="mensaje exito">ingreso correctamente</p>
        <p class="resultado-texto">Bienvenido/a, <?php echo htmlspecialchars($_SESSION['nombre'], ENT_QUOTES, 'UTF-8'); ?>.</p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
