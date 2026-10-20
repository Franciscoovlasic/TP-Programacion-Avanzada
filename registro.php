<?php
require_once __DIR__ . '/includes/auth.php';
iniciarSesionSegura();

if (sesionAutenticada()) {
    header('Location: inicio.php');
    exit;
}

$tokenRegistro = bin2hex(random_bytes(32));
$_SESSION['token_registro'] = $tokenRegistro;

include 'includes/header.php';
?>

<main class="hero hero-result">
    <div class="login-card">
        <h1 class="login-title">Crear cuenta</h1>
        <p class="resultado-texto">Completá tus datos para registrarte en FORJA GYM.</p>

        <?php if (isset($_SESSION['error'])): ?>
            <?php foreach ((array) $_SESSION['error'] as $error): ?>
                <p class="mensaje error"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="procesoRegistro.php" method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($tokenRegistro, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" maxlength="100" required autocomplete="name">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="100" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" maxlength="50" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" minlength="8" maxlength="72" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" minlength="8" maxlength="72" required autocomplete="new-password">
            </div>

            <button type="submit" id="btnIngresar" class="btn-primario">Registrarme</button>
        </form>

        <a class="btn-registro" href="index.php">Volver al inicio de sesión</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
