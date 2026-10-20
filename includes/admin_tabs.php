<?php
/**
 * Pestañas de navegación del área de administración (Panel / Mi perfil).
 *
 * Se incluye desde admin/index.php y admin/perfil.php para que ambas pantallas
 * compartan exactamente la misma navegación.
 *
 * Variables esperadas:
 *   $tabActiva  'panel' | 'perfil'
 *   $rutaBase   prefijo relativo a la raíz del proyecto (por ejemplo '../')
 */
$tabActiva = $tabActiva ?? 'panel';
$rutaBase = $rutaBase ?? '';

$tabsAdmin = [
    'panel' => [
        'href' => $rutaBase . 'admin/index.php',
        'icono' => '▣',
        'titulo' => 'Panel de administración',
        'detalle' => 'Alumnos y rutinas',
    ],
    'perfil' => [
        'href' => $rutaBase . 'admin/perfil.php',
        'icono' => '◉',
        'titulo' => 'Mi perfil',
        'detalle' => 'Tus datos de administrador',
    ],
];
?>
<nav class="alumno-tabs admin-tabs" aria-label="Secciones del administrador">
    <?php foreach ($tabsAdmin as $clave => $tab): ?>
        <?php $activa = $clave === $tabActiva; ?>
        <a class="alumno-tab<?= $activa ? ' activo' : '' ?>"
           href="<?= htmlspecialchars($tab['href'], ENT_QUOTES, 'UTF-8') ?>"
           <?= $activa ? 'aria-current="page"' : '' ?>>
            <span class="tab-icon" aria-hidden="true"><?= $tab['icono'] ?></span>
            <span>
                <strong><?= htmlspecialchars($tab['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                <small><?= htmlspecialchars($tab['detalle'], ENT_QUOTES, 'UTF-8') ?></small>
            </span>
        </a>
    <?php endforeach; ?>
</nav>
