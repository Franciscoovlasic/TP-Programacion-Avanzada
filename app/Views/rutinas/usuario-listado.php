<?php
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel">
    <h1 class="login-title">Mis rutinas</h1>
    <p class="resultado-texto">Consulta de solo lectura.</p>
    <?php if ($error): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
    <div class="tabla-contenedor">
        <table class="tabla-catalogo tabla-datatable">
            <thead><tr><th>Nombre</th><th>Descripción</th><th class="no-ordenar">Acción</th></tr></thead>
            <tbody>
            <?php foreach ($rutinas as $rutina): ?>
                <tr>
                    <td><?= e($rutina['nombre_rutina']) ?></td>
                    <td><?= e($rutina['descripcion'] ?? '') ?></td>
                    <td><a class="btn-secundario" href="rutina.php?id=<?= (int) $rutina['id_rutina'] ?>">Ver detalle</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a class="btn-registro" href="index.php">Volver al área de usuario</a>
</section></main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
