<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ejercicios.php';
requiereRol('ADMIN');
$rutaBase = '../../';
try { $ejercicios = obtenerEjercicios(); } catch (RuntimeException $exception) { error_log($exception->getMessage()); $ejercicios = []; }
$usaDataTables = true;
include __DIR__ . '/../../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel">
    <h1 class="login-title">Catálogo de ejercicios</h1>
    <p class="resultado-texto">Consulta de solo lectura.</p>
    <div class="tabla-contenedor"><table class="tabla-catalogo tabla-datatable"><thead><tr><th>Nombre</th></tr></thead><tbody>
    <?php foreach ($ejercicios as $ejercicio): ?><tr><td><?= htmlspecialchars((string) $ejercicio['nombre_ejercicio'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <a class="btn-registro" href="../index.php">Volver al área ADMIN</a>
</section></main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
