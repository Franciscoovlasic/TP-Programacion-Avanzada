<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Credenciales válidas (fijas, según consigna del TP)
$usuarioValido    = "fcytuader";
$contrasenaValida = "programacionavanzada";

// Solo se procesa si llega por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$usuario    = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

// Validación server-side: nunca confiar solo en la validación de JS
if ($usuario === '' || $contrasena === '') {
    $_SESSION['error'] = "Debe completar usuario y contraseña.";
    header("Location: index.php");
    exit;
}

// Comprobación de usuario y contraseña
if ($usuario === $usuarioValido && $contrasena === $contrasenaValida) {

    include 'includes/header.php';
    ?>
    <main class="hero hero-result">
        <div class="login-card">
            <p class="mensaje exito">ingreso correctamente</p>
            <p class="resultado-texto">Bienvenido/a, <?php echo htmlspecialchars($usuario); ?>.</p>
            <a href="index.php" class="btn-volver">Volver al inicio</a>
        </div>
    </main>
    <?php
    include 'includes/footer.php';

} else {
    // Usuario o contraseña incorrectos: se informa y se vuelve al form inicial
    $_SESSION['error'] = "Usuario o contraseña incorrectos.";
    header("Location: index.php");
    exit;
}
?>
