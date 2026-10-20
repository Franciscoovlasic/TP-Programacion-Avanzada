<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/Core/Database.php';
requiereRol('ADMIN');
use App\Core\Database;
header('Content-Type: text/html; charset=UTF-8');
$id=filter_input(INPUT_GET,'id_usuario',FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$id){http_response_code(400);echo '<p>Alumno inválido.</p>';exit;}
$db=Database::connection();
$stmt=$db->prepare("SELECT nombre FROM usuario WHERE id_usuario=? AND rol='USUARIO'");$stmt->bind_param('i',$id);$stmt->execute();$alumno=$stmt->get_result()->fetch_assoc();$stmt->close();
if(!$alumno){http_response_code(404);echo '<p>No se encontró el alumno.</p>';exit;}
$stmt=$db->prepare('SELECT id_rutina,nombre_rutina,descripcion,fecha_creacion FROM rutina WHERE id_usuario=? ORDER BY fecha_creacion DESC,id_rutina DESC');$stmt->bind_param('i',$id);$stmt->execute();$rutinas=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
echo '<p class="resultado-texto">Historial de <strong>'.h($alumno['nombre']).'</strong></p>';
if(!$rutinas){echo '<p>Este alumno todavía no tiene rutinas.</p>';exit;}
echo '<div class="tabla-contenedor"><table class="tabla-catalogo"><thead><tr><th>Rutina</th><th>Fecha</th><th>Descripción</th><th>Acción</th></tr></thead><tbody>';
foreach($rutinas as $r){echo '<tr><td>'.h($r['nombre_rutina']).'</td><td>'.h(date('d/m/Y',strtotime($r['fecha_creacion']))).'</td><td>'.h($r['descripcion']??'').'</td><td><a class="btn-secundario" href="rutinas/constructor.php?id='.(int)$r['id_rutina'].'">Ver / editar</a></td></tr>';}
echo '</tbody></table></div>';
