<?php
use App\Core\Database;
$db = Database::connection();
$dias = [];
$stmt = $db->prepare('SELECT id_dia, numero_dia, nombre_dia FROM dia_rutina WHERE id_rutina = ? ORDER BY numero_dia ASC');
$stmt->bind_param('i', $rutina['id_rutina']); $stmt->execute(); $res = $stmt->get_result();
while ($dia = $res->fetch_assoc()) {
    $ejStmt = $db->prepare('SELECT e.nombre_ejercicio, dre.series, dre.repeticiones, dre.descanso_segundos, dre.orden FROM dia_rutina_ejercicio dre INNER JOIN ejercicio e ON e.id_ejercicio = dre.id_ejercicio WHERE dre.id_dia = ? ORDER BY dre.orden ASC, e.nombre_ejercicio ASC');
    $ejStmt->bind_param('i', $dia['id_dia']); $ejStmt->execute(); $dia['ejercicios'] = $ejStmt->get_result()->fetch_all(MYSQLI_ASSOC); $ejStmt->close(); $dias[] = $dia;
}
$stmt->close();
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel alumno-home">
    <p class="eyebrow">FORJA GYM · SOLO LECTURA</p><span class="estado-rutina">DETALLE DE RUTINA</span><h1 class="login-title rutina-detalle-titulo"><?= e($rutina['nombre_rutina']) ?></h1>
    <p class="resultado-texto"><?= nl2br(e($rutina['descripcion'] ?: 'Sin descripción.')) ?></p>
    <?php if (!$dias): ?><div class="rutina-vacia"><h2>Esta rutina todavía no tiene días cargados</h2><p>Consultá con tu entrenador si necesitás más información.</p></div><?php else: ?>
        <div class="dias-consulta"><?php foreach ($dias as $dia): ?><article class="dia-consulta"><div class="dia-consulta-titulo"><span>DÍA <?= (int)$dia['numero_dia'] ?></span><h2><?= e($dia['nombre_dia']) ?></h2></div>
            <?php if (!$dia['ejercicios']): ?><p class="resultado-texto">No hay ejercicios asignados para este día.</p><?php else: ?><div class="tabla-contenedor"><table class="tabla-catalogo"><thead><tr><th>Ejercicio</th><th>Series</th><th>Repeticiones</th><th>Descanso</th></tr></thead><tbody><?php foreach ($dia['ejercicios'] as $ej): ?><tr><td><?= e($ej['nombre_ejercicio']) ?></td><td><?= (int)$ej['series'] ?></td><td><?= (int)$ej['repeticiones'] ?></td><td><?= (int)$ej['descanso_segundos'] ?> s</td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        </article><?php endforeach; ?></div>
    <?php endif; ?>
    <p class="aviso-solo-lectura">Esta rutina es de solo lectura. Para solicitar cambios, hablá con tu entrenador.</p>
    <a class="btn-registro" href="rutinas.php">Volver a mi rutina</a>
</section></main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
