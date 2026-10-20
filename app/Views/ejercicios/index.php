<?php
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result">
    <section class="catalogo-panel">
        <h1 class="login-title">Catálogo de ejercicios</h1>
        <p class="resultado-texto">Administrá los ejercicios reutilizables para las rutinas.</p>
        <?php if ($mensaje): ?><p class="mensaje exito"><?= e($mensaje) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
        <p class="acciones-agregar"><a class="btn-primario btn-auto" href="nuevo.php">+ Agregar ejercicio</a></p>
        <?php if ($ejercicios === [] && !$error): ?><p class="resultado-texto">Todavía no hay ejercicios cargados.</p><?php endif; ?>
        <div class="tabla-contenedor">
            <table class="tabla-catalogo tabla-datatable">
                <thead><tr><th>Nombre</th><th class="no-ordenar">Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($ejercicios as $ejercicio): ?>
                    <tr>
                        <td><?= e($ejercicio['nombre_ejercicio']) ?></td>
                        <td>
                            <div class="acciones-catalogo">
                                <a class="btn-secundario btn-sm" href="editar.php?id=<?= (int) $ejercicio['id_ejercicio'] ?>">Modificar</a>
                                <form action="eliminar.php" method="post" data-confirmar="<?= e('¿Eliminar el ejercicio "' . $ejercicio['nombre_ejercicio'] . '"? Esta acción no se puede deshacer.') ?>">
                                    <input type="hidden" name="token" value="<?= e($token) ?>">
                                    <input type="hidden" name="id_ejercicio" value="<?= (int) $ejercicio['id_ejercicio'] ?>">
                                    <button class="btn-peligro btn-sm" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a class="btn-registro" href="../index.php">Volver al área ADMIN</a>
    </section>
</main>
<script src="<?= e($rutaBase) ?>js/confirmar.js" defer></script>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
