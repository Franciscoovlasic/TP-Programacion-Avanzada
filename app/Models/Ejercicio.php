<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;

final class Ejercicio extends Model
{
    /** Resultado de eliminar(): la fila se borró. */
    public const ELIMINADO = 'eliminado';

    /** Resultado de eliminar(): el ejercicio está referenciado por una FK (p. ej. una rutina). */
    public const EN_USO = 'en_uso';

    /** Código de MySQL: no se puede borrar o actualizar la fila porque tiene filas hijas. */
    private const ERROR_FK_HIJOS = 1451;

    /** Código de MySQL: no se puede borrar la fila porque la referencia una FK. */
    private const ERROR_FK_REFERENCIADA = 1217;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT id_ejercicio, nombre_ejercicio FROM ejercicio ORDER BY nombre_ejercicio ASC'
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function obtener(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id_ejercicio, nombre_ejercicio FROM ejercicio WHERE id_ejercicio = ? LIMIT 1',
            'i',
            [$id]
        );
    }

    /**
     * Inserta un ejercicio y devuelve su id. Cualquier fallo se propaga como RuntimeException.
     */
    public function crear(string $nombre): int
    {
        $this->query(
            'INSERT INTO ejercicio (nombre_ejercicio) VALUES (?)',
            's',
            [$nombre]
        )->close();

        return $this->lastInsertId();
    }

    /**
     * Actualiza el nombre. Cualquier fallo se propaga como RuntimeException.
     */
    public function actualizar(int $id, string $nombre): void
    {
        $this->query(
            'UPDATE ejercicio SET nombre_ejercicio = ? WHERE id_ejercicio = ?',
            'si',
            [$nombre, $id]
        )->close();
    }

    /**
     * Elimina un ejercicio. Devuelve ELIMINADO o EN_USO cuando una FK lo impide.
     * Cualquier otro fallo se propaga como RuntimeException.
     *
     * @return self::ELIMINADO|self::EN_USO
     */
    public function eliminar(int $id): string
    {
        try {
            $this->query(
                'DELETE FROM ejercicio WHERE id_ejercicio = ?',
                'i',
                [$id]
            )->close();
        } catch (RuntimeException $excepcion) {
            if (in_array($excepcion->getCode(), [self::ERROR_FK_HIJOS, self::ERROR_FK_REFERENCIADA], true)) {
                return self::EN_USO;
            }

            throw $excepcion;
        }

        return self::ELIMINADO;
    }
}
