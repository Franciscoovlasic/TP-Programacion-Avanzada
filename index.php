<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario'])) {
    header('Location: inicio.php');
    exit;
}
include 'includes/header.php';
?>

    <main class="hero">

        <div class="hero-content">
            <!-- Ilustración vectorial por defecto (barra con discos).
                 Si agregás tu propia foto en css/style.css, podés borrar
                 este bloque <svg> completo si querés. -->
            <div class="hero-media" aria-hidden="true">
                <svg viewBox="0 0 480 240" xmlns="http://www.w3.org/2000/svg" role="img">
                    <rect x="60" y="112" width="360" height="16" rx="3" fill="#8B909B" opacity="0.9"/>
                    <rect x="18" y="60" width="22" height="120" rx="6" fill="#D6472C"/>
                    <rect x="44" y="78" width="16" height="84" rx="5" fill="#8B909B" opacity="0.85"/>
                    <rect x="440" y="60" width="22" height="120" rx="6" fill="#D6472C"/>
                    <rect x="420" y="78" width="16" height="84" rx="5" fill="#8B909B" opacity="0.85"/>
                </svg>
            </div>

            <h2 class="hero-title">Cada repetición forja tu progreso.</h2>
            <p class="hero-text">
                Accedé a tu cuenta para seguir tu rutina, tus cargas y tus objetivos.
            </p>
        </div>

        <div class="login-card">
            <h1 class="login-title">Acceder</h1>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="mensaje error">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }
            ?>

            <form id="loginForm" action="procesoLogin.php" method="POST" novalidate>
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" autocomplete="off">
                </div>

                <span id="msgValidacion" class="mensaje-validacion"></span>

                <button type="submit" id="btnIngresar" disabled>Ingresar</button>
            </form>
        </div>

    </main>

<?php
include 'includes/footer.php';
?>
<script src="js/validacion.js"></script>
