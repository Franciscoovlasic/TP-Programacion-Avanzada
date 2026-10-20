<?php
require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/rutinas.php';
requiereRol('ADMIN');
$rutaBase = '../../'; $mensaje = $_SESSION['mensaje'] ?? null; unset($_SESSION['mensaje']); $error = null;
try { $rutinas = obtenerRutinas(); } catch (RuntimeException $exception) { error_log($exception->getMessage()); $rutinas = []; $error = 'No se pudieron cargar las rutinas en este momento.'; }
$token = tokenCsrf(); $usaDataTables = true; include __DIR__ . '/../../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel">
    <h1 class="login-title">Rutinas</h1><p class="resultado-texto">Administrá las rutinas y sus usuarios propietarios.</p>
    <?php if ($mensaje): ?><p class="mensaje exito"><?= htmlspecialchars((string) $mensaje, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if ($error): ?><p class="mensaje error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <p><a class="btn-registro" href="nuevo.php">Agregar rutina</a></p>
    <div class="tabla-contenedor"><table class="tabla-catalogo tabla-datatable"><thead><tr><th>Nombre</th><th>Propietario</th><th>Descripción</th><th class="no-ordenar">Acciones</th></tr></thead><tbody>
    <?php foreach ($rutinas as $rutina): ?><tr><td><?= htmlspecialchars((string) $rutina['nombre_rutina'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $rutina['nombre_usuario'] . ' (' . (string) $rutina['dni'] . ')', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($rutina['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><td class="acciones-catalogo"><a class="btn-secundario" href="detalle.php?id=<?= (int) $rutina['id_rutina'] ?>">Ver</a> <a class="btn-secundario" href="editar.php?id=<?= (int) $rutina['id_rutina'] ?>">Modificar</a><form action="eliminar.php" method="post"><input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id_rutina" value="<?= (int) $rutina['id_rutina'] ?>"><button class="btn-peligro" type="submit">Eliminar</button></form></td></tr><?php endforeach; ?></tbody></table></div>
    <a class="btn-registro" href="../index.php">Volver al área ADMIN</a>
</section></main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
