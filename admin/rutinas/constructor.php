<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/Core/Database.php';
requiereRol('ADMIN');
use App\Core\Database;

$conexion = Database::connection();
$rutaBase = '../../';
$idRutina = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: null;
$idUsuario = filter_input(INPUT_GET, 'id_usuario', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: null;
$errores = [];
$nombre = '';
$descripcion = '';
$dias = [];
$alumno = null;
$ejercicios = [];

try {
    $res = $conexion->query("SELECT id_ejercicio, nombre_ejercicio FROM ejercicio ORDER BY nombre_ejercicio");
    if ($res) { $ejercicios = $res->fetch_all(MYSQLI_ASSOC); $res->free(); }
    if ($idRutina) {
        $stmt = $conexion->prepare('SELECT id_rutina,id_usuario,nombre_rutina,descripcion FROM rutina WHERE id_rutina=?');
        $stmt->bind_param('i',$idRutina); $stmt->execute(); $rutina = $stmt->get_result()->fetch_assoc(); $stmt->close();
        if (!$rutina) { http_response_code(404); exit('La rutina solicitada no existe.'); }
        $idUsuario = (int)$rutina['id_usuario']; $nombre = $rutina['nombre_rutina']; $descripcion = $rutina['descripcion'] ?? '';
        $stmt = $conexion->prepare('SELECT d.id_dia,d.numero_dia,d.nombre_dia FROM dia_rutina d WHERE d.id_rutina=? ORDER BY d.numero_dia');
        $stmt->bind_param('i',$idRutina); $stmt->execute(); $rs = $stmt->get_result();
        while($d=$rs->fetch_assoc()){
            $d['ejercicios']=[]; $idDia=(int)$d['id_dia'];
            $se=$conexion->prepare('SELECT de.id_ejercicio,de.series,de.repeticiones,de.descanso_segundos,de.orden FROM dia_rutina_ejercicio de WHERE de.id_dia=? ORDER BY de.orden');
            $se->bind_param('i',$idDia);$se->execute();$re=$se->get_result();while($ex=$re->fetch_assoc())$d['ejercicios'][]=$ex;$se->close();$dias[]=$d;
        } $stmt->close();
    }
    if ($idUsuario) { $stmt=$conexion->prepare("SELECT id_usuario,nombre FROM usuario WHERE id_usuario=? AND rol='USUARIO'");$stmt->bind_param('i',$idUsuario);$stmt->execute();$alumno=$stmt->get_result()->fetch_assoc();$stmt->close(); }
} catch(Throwable $e) { error_log($e->getMessage()); $errores[]='No se pudieron cargar los datos. Ejecutá primero la migración 005.'; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $token=$_POST['token'] ?? '';
    $nombre=trim((string)($_POST['nombre_rutina'] ?? ''));
    $descripcion=trim((string)($_POST['descripcion'] ?? ''));
    $idUsuario=filter_var($_POST['id_usuario'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: null;
    $diasPost=$_POST['dias'] ?? [];
    if (!validarTokenCsrf($token)) $errores[]='La sesión del formulario venció. Recargá la página e intentá nuevamente.';
    if ($nombre==='' || mb_strlen($nombre)>100) $errores[]='Ingresá un nombre de rutina de hasta 100 caracteres.';
    if (!$idUsuario) $errores[]='No se identificó al alumno.';
    if (!is_array($diasPost) || count($diasPost)<1) $errores[]='Agregá al menos un día a la rutina.';
    foreach ((array)$diasPost as $di) {
        if (trim((string)($di['nombre_dia']??''))==='') $errores[]='Todos los días deben tener un nombre.';
        foreach ((array)($di['ejercicios']??[]) as $ex) {
            if ((int)($ex['id_ejercicio']??0)<1 || (int)($ex['series']??0)<1 || (int)($ex['repeticiones']??0)<1 || (int)($ex['descanso_segundos']??-1)<0) $errores[]='Revisá los ejercicios, series, repeticiones y descansos.';
        }
    }
    if (!$errores) {
        try {
            $conexion->begin_transaction();
            if ($idRutina) {
                $stmt=$conexion->prepare('UPDATE rutina SET nombre_rutina=?,descripcion=? WHERE id_rutina=? AND id_usuario=?');
                $stmt->bind_param('ssii',$nombre,$descripcion,$idRutina,$idUsuario);$stmt->execute();if($stmt->affected_rows<0)throw new RuntimeException('No se pudo actualizar la rutina.');$stmt->close();
                $stmt=$conexion->prepare('DELETE FROM dia_rutina WHERE id_rutina=?');$stmt->bind_param('i',$idRutina);$stmt->execute();$stmt->close();
            } else {
                $stmt=$conexion->prepare('INSERT INTO rutina(id_usuario,nombre_rutina,descripcion) VALUES(?,?,?)');$stmt->bind_param('iss',$idUsuario,$nombre,$descripcion);$stmt->execute();$idRutina=(int)$conexion->insert_id;$stmt->close();
            }
            $numero=0;
            foreach($diasPost as $dia){$numero++;$nombreDia=trim((string)$dia['nombre_dia']);$stmt=$conexion->prepare('INSERT INTO dia_rutina(id_rutina,numero_dia,nombre_dia) VALUES(?,?,?)');$stmt->bind_param('iis',$idRutina,$numero,$nombreDia);$stmt->execute();$idDia=(int)$conexion->insert_id;$stmt->close();
                $orden=0;foreach((array)($dia['ejercicios']??[]) as $ex){$orden++;$idEx=(int)$ex['id_ejercicio'];$series=(int)$ex['series'];$reps=(int)$ex['repeticiones'];$descanso=(int)$ex['descanso_segundos'];$stmt=$conexion->prepare('INSERT INTO dia_rutina_ejercicio(id_dia,id_ejercicio,series,repeticiones,descanso_segundos,orden) VALUES(?,?,?,?,?,?)');$stmt->bind_param('iiiiii',$idDia,$idEx,$series,$reps,$descanso,$orden);$stmt->execute();$stmt->close();}
            }
            $conexion->commit();$_SESSION['mensaje']=$idRutina && isset($_GET['id'])?'Rutina actualizada correctamente.':'Nueva rutina creada; la anterior se conserva en el historial.';header('Location: ../index.php');exit;
        } catch(Throwable $e) {$conexion->rollback();error_log($e->getMessage());$errores[]='No se pudo guardar. Verificá la migración 005 y los datos ingresados.';}
    }
    // Repoblar la interfaz con lo enviado si hay errores.
    $dias=[];foreach((array)$diasPost as $d)$dias[]=['nombre_dia'=>$d['nombre_dia']??'','ejercicios'=>$d['ejercicios']??[]];
    if($idUsuario){$stmt=$conexion->prepare("SELECT id_usuario,nombre FROM usuario WHERE id_usuario=? AND rol='USUARIO'");$stmt->bind_param('i',$idUsuario);$stmt->execute();$alumno=$stmt->get_result()->fetch_assoc();$stmt->close();}
}
if (!$dias) $dias=[['nombre_dia'=>'Día 1','ejercicios'=>[]]];
include __DIR__ . '/../../includes/header.php';
?>
<main class="hero hero-result"><section class="catalogo-panel constructor-panel">
<h1 class="login-title"><?= $idRutina?'Editar rutina':'Crear rutina nueva' ?></h1><p class="resultado-texto">Alumno: <strong><?= e($alumno['nombre'] ?? 'Alumno') ?></strong></p>
<?php foreach($errores as $error): ?><p class="mensaje error"><?= e($error) ?></p><?php endforeach; ?>
<form method="post" id="formRutina"><input type="hidden" name="token" value="<?= e(tokenCsrf()) ?>"><input type="hidden" name="id_usuario" value="<?= (int)$idUsuario ?>">
<div class="form-group"><label for="nombre_rutina">Nombre de la rutina</label><input id="nombre_rutina" name="nombre_rutina" maxlength="100" required value="<?= e($nombre) ?>"></div>
<div class="form-group"><label for="descripcion">Descripción (opcional)</label><textarea id="descripcion" name="descripcion" rows="2"><?= e($descripcion) ?></textarea></div>
<div class="constructor-heading"><h2>Días de entrenamiento</h2><button class="btn-secundario btn-sm" type="button" id="agregarDia">+ Agregar día</button></div>
<div id="diasContainer"></div><div class="constructor-actions"><button class="btn-primario btn-auto" type="submit">Guardar rutina</button><a class="btn-secundario" href="../index.php">Cancelar</a></div></form>
</section></main>
<template id="plantillaDia"><article class="dia-card"><div class="dia-card-head"><div class="form-group"><label>Nombre del día</label><input class="nombre-dia" required placeholder="Ej. Tren superior"></div><button type="button" class="btn-peligro btn-sm quitar-dia">Quitar día</button></div><div class="ejercicios-lista"></div><button type="button" class="btn-secundario btn-sm agregar-ejercicio">+ Agregar ejercicio</button></article></template>
<template id="plantillaEjercicio"><div class="ejercicio-editor"><div class="form-group ejercicio-select"><label>Ejercicio</label><select class="id-ejercicio" required><option value="">Seleccionar ejercicio</option><?php foreach($ejercicios as $ex): ?><option value="<?= (int)$ex['id_ejercicio'] ?>"><?= e($ex['nombre_ejercicio']) ?></option><?php endforeach; ?></select></div><div class="form-group"><label>Series</label><input class="series" type="number" min="1" max="20" value="3" required></div><div class="form-group"><label>Repeticiones</label><input class="repeticiones" type="number" min="1" max="100" value="10" required></div><div class="form-group"><label>Descanso (seg.)</label><input class="descanso" type="number" min="0" max="3600" step="15" value="90" required></div><button type="button" class="btn-peligro quitar-ejercicio" aria-label="Quitar ejercicio" title="Quitar ejercicio">×</button></div></template>
<script>
const initialDays=<?= json_encode(array_map(fn($d)=>['nombre_dia'=>$d['nombre_dia']??'','ejercicios'=>array_map(fn($e)=>['id_ejercicio'=>(int)$e['id_ejercicio'],'series'=>(int)$e['series'],'repeticiones'=>(int)$e['repeticiones'],'descanso_segundos'=>(int)($e['descanso_segundos']??90)],$d['ejercicios']??[])],$dias),JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
const container=document.getElementById('diasContainer');
function addExercise(day, data={}){const node=document.getElementById('plantillaEjercicio').content.firstElementChild.cloneNode(true);node.querySelector('.id-ejercicio').value=data.id_ejercicio||'';node.querySelector('.series').value=data.series||3;node.querySelector('.repeticiones').value=data.repeticiones||10;node.querySelector('.descanso').value=data.descanso_segundos??90;node.querySelector('.quitar-ejercicio').onclick=()=>node.remove();day.querySelector('.ejercicios-lista').appendChild(node);}
function renumber(){[...container.querySelectorAll('.dia-card')].forEach((day,i)=>{day.querySelector('.nombre-dia').name=`dias[${i}][nombre_dia]`;day.querySelectorAll('.ejercicio-editor').forEach((ex,j)=>{ex.querySelector('.id-ejercicio').name=`dias[${i}][ejercicios][${j}][id_ejercicio]`;ex.querySelector('.series').name=`dias[${i}][ejercicios][${j}][series]`;ex.querySelector('.repeticiones').name=`dias[${i}][ejercicios][${j}][repeticiones]`;ex.querySelector('.descanso').name=`dias[${i}][ejercicios][${j}][descanso_segundos]`;});});}
function addDay(data={}){const day=document.getElementById('plantillaDia').content.firstElementChild.cloneNode(true);day.querySelector('.nombre-dia').value=data.nombre_dia||`Día ${container.children.length+1}`;day.querySelector('.quitar-dia').onclick=()=>{day.remove();renumber()};day.querySelector('.agregar-ejercicio').onclick=()=>{addExercise(day);renumber()};container.appendChild(day);(data.ejercicios||[]).forEach(ex=>addExercise(day,ex));renumber();}
initialDays.forEach(addDay);document.getElementById('agregarDia').onclick=()=>addDay();document.getElementById('formRutina').addEventListener('submit',e=>{renumber();if(!container.children.length){e.preventDefault();alert('Agregá al menos un día.');}});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
