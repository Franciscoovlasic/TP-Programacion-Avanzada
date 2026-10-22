<?php
/**
 * Fragmento HTML de "Gestionar rutinas". Se inserta dentro de #modalHistorial en admin/index.php.
 *
 * Variables: $error (string|null), $alumno (array|null), $rutinas (array), $token (string).
 * Las URLs se generan con rutaAplicacion() para funcionar desde cualquier página que lo inserte.
 */
if ($error !== null || $alumno === null):
    ?>
    <p class="mensaje error"><?= e($error ?? 'No se pudo cargar el historial.') ?></p>
<?php else:
    $idAlumno = (int) $alumno['id_usuario'];
    $nombreAlumno = nombreCompleto($alumno['nombre'] ?? '', $alumno['apellido'] ?? '');
    ?>
    <header class="modal-encabezado">
        <div>
            <h2 id="historialTitulo">Rutinas de <?= e($nombreAlumno) ?></h2>
            <p class="resultado-texto">DNI <?= e($alumno['dni'] ?? '') ?> · <?= count($rutinas) ?> rutina<?= count($rutinas) === 1 ? '' : 's' ?></p>
        </div>
        <a class="btn-primario btn-sm btn-auto" href="<?= e(rutaAplicacion('admin/rutinas/constructor.php?id_usuario=' . $idAlumno)) ?>">+ Nueva rutina</a>
    </header>

    <?php if (!$rutinas): ?>
        <p class="resultado-texto">Este alumno todavía no tiene rutinas. Usá "+ Nueva rutina" para crear la primera.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla-catalogo">
                <thead><tr><th>Rutina</th><th>Fecha de creación</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($rutinas as $rutina): ?>
                    <tr>
                        <td><?= e($rutina['nombre_rutina']) ?></td>
                        <td><?= e(date('d/m/Y', strtotime((string) $rutina['fecha_creacion']))) ?></td>
                        <td>
                            <div class="acciones-catalogo">
                                <a class="btn-secundario btn-sm" href="<?= e(rutaAplicacion('admin/rutinas/constructor.php?id=' . (int) $rutina['id_rutina'])) ?>">Editar</a>
                                <form action="<?= e(rutaAplicacion('admin/rutinas/eliminar.php')) ?>" method="post" data-confirmar="<?= e('¿Eliminar la rutina "' . $rutina['nombre_rutina'] . '"? Esta acción no se puede deshacer.') ?>">
                                    <input type="hidden" name="token" value="<?= e($token) ?>">
                                    <input type="hidden" name="id_rutina" value="<?= (int) $rutina['id_rutina'] ?>">
                                    <input type="hidden" name="retorno" value="panel">
                                    <button class="btn-peligro btn-sm" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>
