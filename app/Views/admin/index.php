<?php
include __DIR__ . '/../../../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel admin-alumnos-panel">
    <header class="admin-encabezado">
        <p class="eyebrow">FORJA GYM · ADMINISTRACIÓN</p>
        <h1 class="login-title">Panel de administración</h1>
        <p class="resultado-texto">Alumnos y seguimiento de rutinas.</p>
    </header>
    <?php include __DIR__ . '/../../../includes/admin_tabs.php'; ?>
    <?php if ($mensaje): ?><p class="mensaje exito"><?= e($mensaje) ?></p><?php endif; ?>
    <?php foreach ($erroresFlash as $mensajeError): ?><p class="mensaje error"><?= e($mensajeError) ?></p><?php endforeach; ?>
    <?php if ($error): ?><p class="mensaje error"><?= e($error) ?></p><?php endif; ?>
    <?php if (!$alumnos && !$error): ?><p class="resultado-texto">No hay alumnos registrados.</p><?php endif; ?>
    <div class="tabla-contenedor"><table class="tabla-catalogo tabla-datatable">
        <thead><tr><th>Alumno</th><th>Email</th><th>Última rutina activa</th><th>Fecha de creación</th><th class="no-ordenar">Acciones</th></tr></thead>
        <tbody><?php foreach ($alumnos as $a): ?>
        <tr>
            <td><?= e(nombreCompleto($a['nombre'], $a['apellido'] ?? '')) ?></td>
            <td><?= e($a['email']) ?></td>
            <td><?= e($a['nombre_rutina'] ?? 'Sin rutina') ?></td>
            <td><?= $a['fecha_creacion'] ? e(date('d/m/Y', strtotime($a['fecha_creacion']))) : '—' ?></td>
            <td>
                <div class="acciones-catalogo">
                    <button class="btn-primario btn-sm admin-btn" type="button" onclick="abrirHistorial(<?= (int) $a['id_usuario'] ?>)">Gestionar rutinas</button>
                    <a class="btn-secundario btn-sm" href="usuarios/ver.php?id=<?= (int) $a['id_usuario'] ?>">Ver</a>
                    <a class="btn-secundario btn-sm" href="usuarios/editar.php?id=<?= (int) $a['id_usuario'] ?>">Editar</a>
                    <form action="usuarios/eliminar.php" method="post" data-confirmar="<?= e('¿Eliminar al alumno "' . nombreCompleto($a['nombre'], $a['apellido'] ?? '') . '"? Se eliminarán también todas sus rutinas. Esta acción no se puede deshacer.') ?>">
                        <input type="hidden" name="token" value="<?= e($token) ?>">
                        <input type="hidden" name="id_usuario" value="<?= (int) $a['id_usuario'] ?>">
                        <button class="btn-peligro btn-sm" type="submit">Eliminar</button>
                    </form>
                </div>
            </td>
        </tr><?php endforeach; ?></tbody>
    </table></div>
    <div class="admin-links"><a class="btn-primario btn-auto" href="ejercicios/index.php">Administrar ejercicios</a></div>
</section></main>
<div class="modal-overlay" id="modalHistorial" aria-hidden="true"><section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="historialTitulo"><button class="modal-cerrar" type="button" onclick="cerrarHistorial()" aria-label="Cerrar" title="Cerrar">×</button><div id="historialContenido" aria-live="polite">Cargando...</div></section></div>
<script>
let disparadorHistorial=null;
async function abrirHistorial(id){disparadorHistorial=document.activeElement;const modal=document.getElementById('modalHistorial');modal.classList.add('visible');modal.setAttribute('aria-hidden','false');modal.querySelector('.modal-cerrar').focus();document.getElementById('historialContenido').textContent='Cargando...';try{const r=await fetch('rutinas/historial.php?id_usuario='+encodeURIComponent(id));document.getElementById('historialContenido').innerHTML=await r.text();}catch(e){document.getElementById('historialContenido').textContent='No se pudo cargar las rutinas del alumno.';}}
function cerrarHistorial(){const m=document.getElementById('modalHistorial');m.classList.remove('visible');m.setAttribute('aria-hidden','true');if(disparadorHistorial&&disparadorHistorial.focus){disparadorHistorial.focus();disparadorHistorial=null;}}
document.getElementById('modalHistorial').addEventListener('click',e=>{if(e.target.id==='modalHistorial')cerrarHistorial()});
document.addEventListener('keydown',e=>{const m=document.getElementById('modalHistorial');if(!m.classList.contains('visible'))return;if(e.key==='Escape'){cerrarHistorial();return;}if(e.key==='Tab'){const f=[...m.querySelectorAll('a[href],button:not([disabled])')];if(!f.length)return;const primero=f[0],ultimo=f[f.length-1];if(e.shiftKey&&document.activeElement===primero){e.preventDefault();ultimo.focus();}else if(!e.shiftKey&&document.activeElement===ultimo){e.preventDefault();primero.focus();}}});
</script>
<script src="<?= e($rutaBase) ?>js/confirmar.js" defer></script>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
