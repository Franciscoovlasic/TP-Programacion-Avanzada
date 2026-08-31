<?php
session_start();
include 'includes/header.php';
?>

    <main class="login-container">
        <h2>Iniciar Sesión</h2>

        <?php
        // Si procesoLogin.php dejó un mensaje de error, se muestra y se limpia
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
    </main>

<?php
include 'includes/footer.php';
?>
<script src="js/validacion.js"></script>
