<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/Core/Database.php';
requiereRol('ADMIN');
use App\Core\Database;

$rutaBase = '../';
$mensaje = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);
$conexion = Database::connection();
$alumnos = [];
$error = null;
try {
    $sql = "SELECT u.id_usuario, u.nombre, u.email, u.dni, r.id_rutina, r.nombre_rutina, r.fecha_creacion
            FROM usuario u
            LEFT JOIN rutina r ON r.id_rutina = (
                SELECT r2.id_rutina FROM rutina r2 WHERE r2.id_usuario = u.id_usuario
                ORDER BY r2.fecha_creacion DESC, r2.id_rutina DESC LIMIT 1
            )
            WHERE u.rol = 'USUARIO' ORDER BY u.nombre ASC";
    $resultado = $conexion->query($sql);
    if ($resultado) { $alumnos = $resultado->fetch_all(MYSQLI_ASSOC); $resultado->free(); }
} catch (Throwable $e) { error_log($e->getMessage()); $error = 'No se pudo cargar la lista de alumnos.'; }
include __DIR__ . '/../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel admin-alumnos-panel">
    <h1 class="login-title">Panel de administración</h1>
    <p class="resultado-texto">Alumnos y seguimiento de rutinas.</p>
    <?php if ($mensaje): ?><p class="mensaje exito"><?= e($mensaje) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
    <div class="tabla-contenedor"><table class="tabla-catalogo">
        <thead><tr><th>Alumno</th><th>Email</th><th>Última rutina activa</th><th>Fecha de creación</th><th>Editar rutina</th><th>Nueva rutina</th><th>Historial</th></tr></thead>
        <tbody><?php foreach ($alumnos as $a): ?>
        <tr>
            <td><?= e($a['nombre']) ?></td><td><?= e($a['email']) ?></td>
            <td><?= e($a['nombre_rutina'] ?? 'Sin rutina') ?></td>
            <td><?= $a['fecha_creacion'] ? e(date('d/m/Y', strtotime($a['fecha_creacion']))) : '—' ?></td>
            <td><?php if ($a['id_rutina']): ?><a class="btn-secundario btn-sm" href="rutinas/constructor.php?id=<?= (int)$a['id_rutina'] ?>">Editar</a><?php else: ?><span>—</span><?php endif; ?></td>
            <td><a class="btn-primario btn-sm admin-btn" href="rutinas/constructor.php?id_usuario=<?= (int)$a['id_usuario'] ?>">+ Crear</a></td>
            <td><button class="btn-secundario btn-sm" type="button" onclick="abrirHistorial(<?= (int)$a['id_usuario'] ?>)">Ver historial</button></td>
        </tr><?php endforeach; ?>
        <?php if (!$alumnos): ?><tr><td colspan="7">No hay alumnos registrados.</td></tr><?php endif; ?></tbody>
    </table></div>
    <div class="admin-links"><a class="btn-primario btn-auto" href="ejercicios/index.php">Administrar ejercicios</a><a class="btn-secundario" href="../inicio.php">Volver al inicio</a></div>
</section></main>
<div class="modal-overlay" id="modalHistorial" aria-hidden="true"><section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="historialTitulo"><button class="modal-cerrar" type="button" onclick="cerrarHistorial()" aria-label="Cerrar historial" title="Cerrar">×</button><h2 id="historialTitulo">Historial de rutinas</h2><div id="historialContenido">Cargando...</div></section></div>
<script>
async function abrirHistorial(id){const modal=document.getElementById('modalHistorial');modal.classList.add('visible');modal.setAttribute('aria-hidden','false');document.getElementById('historialContenido').textContent='Cargando...';try{const r=await fetch('rutinas/historial.php?id_usuario='+encodeURIComponent(id));if(!r.ok)throw new Error();document.getElementById('historialContenido').innerHTML=await r.text();}catch(e){document.getElementById('historialContenido').textContent='No se pudo cargar el historial.';}}
function cerrarHistorial(){const m=document.getElementById('modalHistorial');m.classList.remove('visible');m.setAttribute('aria-hidden','true');}
document.getElementById('modalHistorial').addEventListener('click',e=>{if(e.target.id==='modalHistorial')cerrarHistorial()});document.addEventListener('keydown',e=>{if(e.key==='Escape')cerrarHistorial()});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
