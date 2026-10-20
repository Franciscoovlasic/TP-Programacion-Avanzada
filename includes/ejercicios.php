<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

function obtenerEjercicios(): array
{
    $conexion = conectarBaseDeDatos();
    $consulta = $conexion->prepare(
        'SELECT id_ejercicio, nombre_ejercicio FROM ejercicio ORDER BY nombre_ejercicio ASC'
    );

    if ($consulta === false || !$consulta->execute()) {
        $conexion->close();
        throw new RuntimeException('No se pudo consultar el catálogo de ejercicios.');
    }

    $resultado = $consulta->get_result();
    $ejercicios = $resultado->fetch_all(MYSQLI_ASSOC);
    $consulta->close();
    $conexion->close();

    return $ejercicios;
}

function obtenerEjercicio(int $id): ?array
{
    $conexion = conectarBaseDeDatos();
    $consulta = $conexion->prepare(
        'SELECT id_ejercicio, nombre_ejercicio FROM ejercicio WHERE id_ejercicio = ?'
    );

    if ($consulta === false) {
        $conexion->close();
        throw new RuntimeException('No se pudo consultar el ejercicio.');
    }

    $consulta->bind_param('i', $id);
    if (!$consulta->execute()) {
        $consulta->close();
        $conexion->close();
        throw new RuntimeException('No se pudo consultar el ejercicio.');
    }

    $resultado = $consulta->get_result();
    $ejercicio = $resultado->fetch_assoc() ?: null;
    $consulta->close();
    $conexion->close();

    return $ejercicio;
}

function guardarEjercicio(?int $id, string $nombre): void
{
    $conexion = conectarBaseDeDatos();
    if ($id === null) {
        $consulta = $conexion->prepare(
            'INSERT INTO ejercicio (nombre_ejercicio) VALUES (?)'
        );
        $tipo = 's';
        $consulta?->bind_param($tipo, $nombre);
    } else {
        $consulta = $conexion->prepare(
            'UPDATE ejercicio SET nombre_ejercicio = ? WHERE id_ejercicio = ?'
        );
        $tipo = 'si';
        $consulta?->bind_param($tipo, $nombre, $id);
    }

    if ($consulta === false || !$consulta->execute()) {
        $consulta?->close();
        $conexion->close();
        throw new RuntimeException('No se pudo guardar el ejercicio.');
    }

    $consulta->close();
    $conexion->close();
}

function eliminarEjercicio(int $id): bool
{
    $conexion = conectarBaseDeDatos();
    $consulta = $conexion->prepare('DELETE FROM ejercicio WHERE id_ejercicio = ?');
    if ($consulta === false) {
        $conexion->close();
        throw new RuntimeException('No se pudo eliminar el ejercicio.');
    }

    $consulta->bind_param('i', $id);
    $eliminado = $consulta->execute();
    $error = $consulta->errno;
    $consulta->close();
    $conexion->close();

    if (!$eliminado && in_array($error, [1217, 1451], true)) {
        return false;
    }
    if (!$eliminado) {
        throw new RuntimeException('No se pudo eliminar el ejercicio.');
    }

    return true;
}
