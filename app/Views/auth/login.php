<?php include __DIR__ . '/../../../includes/header.php'; ?>

    <main class="hero hero-login">

        <!-- Video decorativo de fondo: solo en el body del login (no cubre header ni footer).
             Silenciado y en bucle para permitir el autoplay; sin controles porque no aporta contenido. -->
        <video class="login-video" autoplay muted loop playsinline preload="auto"
               poster="img/login-poster.jpg" aria-hidden="true" tabindex="-1">
            <source src="Videos/gemini_generated_video_96d3787c.mp4" type="video/mp4">
        </video>

        <div class="hero-content">
            <h2 class="hero-title">Cada repetición forja tu progreso.</h2>
            <p class="hero-text">
                Accedé a tu cuenta para seguir tu rutina, tus cargas y tus objetivos.
            </p>
        </div>

        <div class="login-card">
            <h1 class="login-title">Acceder</h1>

            <?php if ($exito !== null): ?>
                <p class="mensaje exito"><?= e($exito) ?></p>
            <?php endif; ?>

            <?php foreach ($errores as $error): ?>
                <p class="mensaje error"><?= e($error) ?></p>
            <?php endforeach; ?>

            <form id="loginForm" action="procesoLogin.php" method="POST" novalidate>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="form-group">
                    <label for="dni">DNI</label>
                    <input type="text" id="dni" name="dni" autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <input type="password" id="contrasena" name="contrasena" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="captcha">Código de verificación</label>
                    <img class="captcha-image" src="<?= e('captcha.php?v=' . time()) ?>" alt="Código de verificación visual">
                    <input type="text" id="captcha" name="captcha" maxlength="6" autocomplete="off">
                </div>

                <span id="msgValidacion" class="mensaje-validacion"></span>

                <button type="submit" id="btnIngresar" class="btn-primario" disabled>Ingresar</button>
            </form>

            <a class="btn-registro" href="registro.php">Crear una cuenta</a>
        </div>

    </main>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
<script src="js/validacion.js"></script>
