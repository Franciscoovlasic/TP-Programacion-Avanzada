<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

function obtenerUsuariosRutina(): array
{
    $conexion = conectarBaseDeDatos();
    $consulta = $conexion->prepare('SELECT id_usuario, nombre, dni FROM usuario ORDER BY nombre ASC, dni ASC');
    if ($consulta === false || !$consulta->execute()) { $consulta?->close(); $conexion->close(); throw new RuntimeException('No se pudo consultar la lista de usuarios.'); }
    $usuarios = $consulta->get_result()->fetch_all(MYSQLI_ASSOC); $consulta->close(); $conexion->close(); return $usuarios;
}

function existeUsuarioRutina(int $idUsuario): bool
{
    $conexion = conectarBaseDeDatos(); $consulta = $conexion->prepare('SELECT id_usuario FROM usuario WHERE id_usuario = ? LIMIT 1');
    if ($consulta === false) { $conexion->close(); throw new RuntimeException('No se pudo validar el usuario propietario.'); }
    $consulta->bind_param('i', $idUsuario);
    if (!$consulta->execute()) { $consulta->close(); $conexion->close(); throw new RuntimeException('No se pudo validar el usuario propietario.'); }
    $existe = $consulta->get_result()->num_rows === 1; $consulta->close(); $conexion->close(); return $existe;
}

function obtenerRutinas(?int $idUsuario = null): array
{
    $conexion = conectarBaseDeDatos();
    $sql = 'SELECT r.id_rutina, r.id_usuario, r.nombre_rutina, r.descripcion, r.fecha_creacion, u.nombre AS nombre_usuario, u.dni FROM rutina r INNER JOIN usuario u ON u.id_usuario = r.id_usuario';
    if ($idUsuario !== null) { $sql .= ' WHERE r.id_usuario = ?'; }
    $sql .= ' ORDER BY r.fecha_creacion DESC, r.id_rutina DESC'; $consulta = $conexion->prepare($sql);
    if ($consulta === false) { $conexion->close(); throw new RuntimeException('No se pudieron consultar las rutinas.'); }
    if ($idUsuario !== null) { $consulta->bind_param('i', $idUsuario); }
    if (!$consulta->execute()) { $consulta->close(); $conexion->close(); throw new RuntimeException('No se pudieron consultar las rutinas.'); }
    $rutinas = $consulta->get_result()->fetch_all(MYSQLI_ASSOC); $consulta->close(); $conexion->close(); return $rutinas;
}

function obtenerRutina(int $idRutina, ?int $idUsuario = null): ?array
{
    $conexion = conectarBaseDeDatos(); $sql = 'SELECT r.id_rutina, r.id_usuario, r.nombre_rutina, r.descripcion, r.fecha_creacion, u.nombre AS nombre_usuario, u.dni FROM rutina r INNER JOIN usuario u ON u.id_usuario = r.id_usuario WHERE r.id_rutina = ?';
    if ($idUsuario !== null) { $sql .= ' AND r.id_usuario = ?'; } $consulta = $conexion->prepare($sql);
    if ($consulta === false) { $conexion->close(); throw new RuntimeException('No se pudo consultar la rutina.'); }
    if ($idUsuario === null) { $consulta->bind_param('i', $idRutina); } else { $consulta->bind_param('ii', $idRutina, $idUsuario); }
    if (!$consulta->execute()) { $consulta->close(); $conexion->close(); throw new RuntimeException('No se pudo consultar la rutina.'); }
    $rutina = $consulta->get_result()->fetch_assoc() ?: null; $consulta->close(); $conexion->close(); return $rutina;
}

function guardarRutina(?int $idRutina, int $idUsuario, string $nombre, string $descripcion): void
{
    $conexion = conectarBaseDeDatos();
    if ($idRutina === null) { $consulta = $conexion->prepare('INSERT INTO rutina (id_usuario, nombre_rutina, descripcion) VALUES (?, ?, ?)'); $consulta?->bind_param('iss', $idUsuario, $nombre, $descripcion); }
    else { $consulta = $conexion->prepare('UPDATE rutina SET id_usuario = ?, nombre_rutina = ?, descripcion = ? WHERE id_rutina = ?'); $consulta?->bind_param('issi', $idUsuario, $nombre, $descripcion, $idRutina); }
    if ($consulta === false || !$consulta->execute()) { $error = $consulta?->errno ?? 0; $consulta?->close(); $conexion->close(); if ($error === 1452) { throw new InvalidArgumentException('El usuario propietario no existe.'); } throw new RuntimeException('No se pudo guardar la rutina.'); }
    $consulta->close(); $conexion->close();
}

function eliminarRutina(int $idRutina): bool
{
    $conexion = conectarBaseDeDatos(); $consulta = $conexion->prepare('DELETE FROM rutina WHERE id_rutina = ?');
    if ($consulta === false) { $conexion->close(); throw new RuntimeException('No se pudo eliminar la rutina.'); }
    $consulta->bind_param('i', $idRutina);
    if (!$consulta->execute()) { $error = $consulta->errno; $consulta->close(); $conexion->close(); if (in_array($error, [1217, 1451], true)) { throw new RuntimeException('La rutina no se puede eliminar porque tiene datos asociados.'); } throw new RuntimeException('No se pudo eliminar la rutina.'); }
    $eliminada = $consulta->affected_rows === 1; $consulta->close(); $conexion->close(); return $eliminada;
}
