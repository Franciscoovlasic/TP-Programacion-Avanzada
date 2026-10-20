<?php
require_once __DIR__ . '/auth.php';
iniciarSesionSegura();
$rutaBase = $rutaBase ?? '';
$tituloPagina = $tituloPagina ?? 'FORJA GYM - Acceso a tu cuenta';

// El logo lleva a la página principal del rol (panel o área personal) y, sin
// sesión, al login. Así nunca pasa por una pantalla intermedia.
if (sesionAutenticada()) {
    $destinoLogo = $rutaBase . ($_SESSION['rol'] === 'ADMIN' ? 'admin/index.php' : 'usuario/index.php');
} else {
    $destinoLogo = $rutaBase . 'index.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= htmlspecialchars($rutaBase . 'css/style.css', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (!empty($usaDataTables)): ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/3.0.2/css/dataTables.dataTables.min.css">
    <?php endif; ?>
</head>
<body>

    <header class="site-header">
        <a href="<?= htmlspecialchars($destinoLogo, ENT_QUOTES, 'UTF-8') ?>" class="brand">
            <img src="<?= htmlspecialchars($rutaBase . 'img/logo.png', ENT_QUOTES, 'UTF-8') ?>" alt="Logo FORJA GYM" class="brand-mark">
            <span class="brand-name">FORJA <strong>GYM</strong></span>
        </a>
        <?php if (sesionAutenticada()): ?>
            <div class="session-status">
                <span class="session-status__user">Logueado como: <?= htmlspecialchars(nombreCompletoSesion(), ENT_QUOTES, 'UTF-8') ?></span>
                <form action="<?= htmlspecialchars($rutaBase . 'logout.php', ENT_QUOTES, 'UTF-8') ?>" method="post" class="session-status__form">
                    <button type="submit" class="session-status__logout" aria-label="Cerrar sesión">
                        <svg class="logout-icon" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="32" height="32" viewBox="0 0 24 24"><g fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M5 2h11a3 3 0 0 1 3 3v14a1 1 0 0 1-1 1h-3"></path><path d="m5 2l7.588 1.518A3 3 0 0 1 15 6.459V20.78a1 1 0 0 1-1.196.98l-7.196-1.438A2 2 0 0 1 5 18.36zm7 10v2"></path></g></svg>
                    </button>
                    <span class="logout-tooltip" role="tooltip">Cerrar Sesión</span>
                </form>
            </div>
        <?php endif; ?>
    </header>
