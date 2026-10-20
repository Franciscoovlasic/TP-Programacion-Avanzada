<?php
include __DIR__ . '/../../../includes/header.php';
$actual = $rutinas[0] ?? null;
$historial = array_slice($rutinas, 1);
?>
<main class="hero hero-result"><section class="catalogo-panel alumno-home">
    <p class="eyebrow">FORJA GYM · ÁREA PERSONAL</p><h1 class="login-title">Mi rutina</h1>
    <p class="resultado-texto">Consultá tu entrenamiento actual o elegí una rutina anterior de tu historial. Las rutinas son de solo lectura.</p>
    <nav class="alumno-tabs" aria-label="Secciones del alumno"><a class="alumno-tab" href="perfil.php"><span class="tab-icon">◉</span><span><strong>Mi perfil</strong><small>Tus datos personales</small></span></a><a class="alumno-tab activo" href="rutinas.php"><span class="tab-icon">▤</span><span><strong>Mi rutina</strong><small>Rutina actual e historial</small></span></a></nav>
    <?php if ($error): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
    <?php if (!$actual): ?><div class="rutina-vacia"><span class="tab-icon">▤</span><h2>Todavía no tenés una rutina asignada</h2><p>Cuando tu entrenador cree una rutina, aparecerá acá.</p></div>
    <?php else: ?>
        <article class="rutina-actual-card"><div class="rutina-card-top"><span class="estado-rutina">RUTINA ACTUAL</span><span class="rutina-fecha">Creada el <?= e(date('d/m/Y', strtotime((string)$actual['fecha_creacion']))) ?></span></div><h2><?= e($actual['nombre_rutina']) ?></h2><p><?= e($actual['descripcion'] ?: 'Sin descripción.') ?></p><a class="btn-registro" href="rutina.php?id=<?= (int)$actual['id_rutina'] ?>">Ver rutina completa</a></article>
        <section class="historial-rutinas"><div class="historial-heading"><div><h2>Historial de rutinas</h2><p>Elegí una rutina anterior para volver a consultarla.</p></div><button type="button" class="btn-secundario" id="toggle-historial" aria-expanded="false">Ver historial (<?= count($historial) ?>)</button></div>
            <div id="lista-historial" hidden>
            <?php if (!$historial): ?><p class="resultado-texto">Todavía no hay rutinas anteriores.</p><?php else: ?><div class="historial-lista"><?php foreach ($historial as $rutina): ?><article class="historial-item"><div><h3><?= e($rutina['nombre_rutina']) ?></h3><p><?= e($rutina['descripcion'] ?: 'Sin descripción.') ?></p><small>Creada el <?= e(date('d/m/Y', strtotime((string)$rutina['fecha_creacion']))) ?></small></div><a class="btn-secundario" href="rutina.php?id=<?= (int)$rutina['id_rutina'] ?>">Consultar</a></article><?php endforeach; ?></div><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
    <a class="enlace-volver" href="index.php">Volver al área personal</a>
</section></main>
<script>document.getElementById('toggle-historial')?.addEventListener('click', function(){const lista=document.getElementById('lista-historial');const abrir=lista.hidden;lista.hidden=!abrir;this.setAttribute('aria-expanded',String(abrir));this.textContent=(abrir?'Ocultar historial':'Ver historial')+' (<?= count($historial) ?>)';});</script>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
