<?php
declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use mysqli_stmt;
use RuntimeException;

abstract class Model
{
    /**
     * Prepara, bindea y ejecuta una sentencia. Siempre usa sentencias preparadas.
     *
     * @param string  $types  Tipos de bind (i, d, s, b), uno por parámetro.
     * @param array<int, mixed> $params Valores a bindear, en orden.
     */
    protected function query(string $sql, string $types = '', array $params = []): mysqli_stmt
    {
        $params = array_values($params);

        if (strlen($types) !== count($params)) {
            throw new InvalidArgumentException('La cantidad de tipos no coincide con la cantidad de parámetros.');
        }

        $stmt = Database::connection()->prepare($sql);

        if ($stmt === false) {
            throw new RuntimeException('No se pudo preparar la sentencia SQL.');
        }

        if ($params !== []) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            // El código de error de MySQL viaja en la excepción (p. ej. 1062 = duplicado).
            $errno = $stmt->errno;
            $stmt->close();
            throw new RuntimeException('No se pudo ejecutar la sentencia SQL.', $errno);
        }

        return $stmt;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        $stmt = $this->query($sql, $types, $params);
        $resultado = $stmt->get_result();

        if ($resultado === false) {
            $stmt->close();
            return [];
        }

        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $filas;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        $filas = $this->fetchAll($sql, $types, $params);

        return $filas[0] ?? null;
    }

    protected function lastInsertId(): int
    {
        return (int) Database::connection()->insert_id;
    }
}
