<?php
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result">
    <section class="catalogo-panel">
        <h1 class="login-title">Rutinas</h1>
        <p class="resultado-texto">Administrá las rutinas y sus usuarios propietarios.</p>
        <?php if ($mensaje): ?><p class="mensaje exito"><?= e($mensaje) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
        <p class="acciones-agregar"><a class="btn-primario btn-auto" href="nuevo.php">+ Agregar rutina</a></p>
        <div class="tabla-contenedor">
            <table class="tabla-catalogo tabla-datatable">
                <thead><tr><th>Nombre</th><th>Propietario</th><th>Descripción</th><th class="no-ordenar">Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($rutinas as $rutina): ?>
                    <tr>
                        <td><?= e($rutina['nombre_rutina']) ?></td>
                        <td><?= e($rutina['nombre_usuario'] . ' (' . $rutina['dni'] . ')') ?></td>
                        <td><?= e($rutina['descripcion'] ?? '') ?></td>
                        <td>
                            <div class="acciones-catalogo">
                                <a class="btn-secundario btn-sm" href="detalle.php?id=<?= (int) $rutina['id_rutina'] ?>">Ver</a>
                                <a class="btn-secundario btn-sm" href="editar.php?id=<?= (int) $rutina['id_rutina'] ?>">Modificar</a>
                                <form action="eliminar.php" method="post">
                                    <input type="hidden" name="token" value="<?= e($token) ?>">
                                    <input type="hidden" name="id_rutina" value="<?= (int) $rutina['id_rutina'] ?>">
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
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
