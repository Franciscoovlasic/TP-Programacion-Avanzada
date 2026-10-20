<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;
use RuntimeException;

final class Rutina extends Model
{
    /** Resultado de eliminar(): la fila se borró. */
    public const ELIMINADA = 'eliminada';

    /** Resultado de eliminar(): no existía ninguna rutina con ese id. */
    public const NO_EXISTE = 'no_existe';

    /** Resultado de eliminar(): la rutina está referenciada por una FK. */
    public const EN_USO = 'en_uso';

    /** Código de MySQL: no se puede borrar o actualizar la fila porque tiene filas hijas. */
    private const ERROR_FK_HIJOS = 1451;

    /** Código de MySQL: no se puede borrar la fila porque la referencia una FK. */
    private const ERROR_FK_REFERENCIADA = 1217;

    /** Código de MySQL: la FK id_usuario apunta a un usuario inexistente. */
    private const ERROR_FK_PROPIETARIO = 1452;

    private const MENSAJE_PROPIETARIO_INEXISTENTE = 'El usuario propietario no existe.';

    private const SELECT_CON_PROPIETARIO = 'SELECT r.id_rutina, r.id_usuario, r.nombre_rutina, r.descripcion, r.fecha_creacion, u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, u.dni FROM rutina r INNER JOIN usuario u ON u.id_usuario = r.id_usuario';

    /**
     * Usuarios disponibles como propietarios (selector del formulario de admin).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarUsuarios(): array
    {
        return $this->fetchAll(
            'SELECT id_usuario, nombre, apellido, dni FROM usuario ORDER BY nombre ASC, apellido ASC, dni ASC'
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPorUsuario(int $idUsuario): array
    {
        return $this->fetchAll(
            self::SELECT_CON_PROPIETARIO . ' WHERE r.id_usuario = ? ORDER BY r.fecha_creacion DESC, r.id_rutina DESC',
            'i',
            [$idUsuario]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodas(): array
    {
        return $this->fetchAll(
            self::SELECT_CON_PROPIETARIO . ' ORDER BY r.fecha_creacion DESC, r.id_rutina DESC'
        );
    }

    /**
     * Fila simple de la rutina, sin datos del propietario.
     *
     * @return array<string, mixed>|null
     */
    public function obtener(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id_rutina, id_usuario, nombre_rutina, descripcion, fecha_creacion FROM rutina WHERE id_rutina = ? LIMIT 1',
            'i',
            [$id]
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function obtenerConPropietario(int $id): ?array
    {
        return $this->fetchOne(
            self::SELECT_CON_PROPIETARIO . ' WHERE r.id_rutina = ? LIMIT 1',
            'i',
            [$id]
        );
    }

    /**
     * Devuelve la rutina solo si pertenece al usuario indicado. Es el único camino
     * que usa el área de usuario: una rutina ajena equivale a "no existe".
     *
     * @return array<string, mixed>|null
     */
    public function obtenerDeUsuario(int $id, int $idUsuario): ?array
    {
        return $this->fetchOne(
            self::SELECT_CON_PROPIETARIO . ' WHERE r.id_rutina = ? AND r.id_usuario = ? LIMIT 1',
            'ii',
            [$id, $idUsuario]
        );
    }

    public function existeUsuario(int $id): bool
    {
        return $this->fetchOne(
            'SELECT id_usuario FROM usuario WHERE id_usuario = ? LIMIT 1',
            'i',
            [$id]
        ) !== null;
    }

    /**
     * Inserta una rutina y devuelve su id. Un propietario inexistente se informa como
     * InvalidArgumentException; cualquier otro fallo se propaga como RuntimeException.
     */
    public function crear(int $idUsuario, string $nombre, string $descripcion): int
    {
        try {
            $this->query(
                'INSERT INTO rutina (id_usuario, nombre_rutina, descripcion) VALUES (?, ?, ?)',
                'iss',
                [$idUsuario, $nombre, $descripcion]
            )->close();
        } catch (RuntimeException $excepcion) {
            if ($excepcion->getCode() === self::ERROR_FK_PROPIETARIO) {
                throw new InvalidArgumentException(self::MENSAJE_PROPIETARIO_INEXISTENTE, 0, $excepcion);
            }

            throw $excepcion;
        }

        return $this->lastInsertId();
    }

    /**
     * Actualiza propietario, nombre y descripción. Mismas reglas de error que crear().
     */
    public function actualizar(int $id, int $idUsuario, string $nombre, string $descripcion): void
    {
        try {
            $this->query(
                'UPDATE rutina SET id_usuario = ?, nombre_rutina = ?, descripcion = ? WHERE id_rutina = ?',
                'issi',
                [$idUsuario, $nombre, $descripcion, $id]
            )->close();
        } catch (RuntimeException $excepcion) {
            if ($excepcion->getCode() === self::ERROR_FK_PROPIETARIO) {
                throw new InvalidArgumentException(self::MENSAJE_PROPIETARIO_INEXISTENTE, 0, $excepcion);
            }

            throw $excepcion;
        }
    }

    /**
     * Devuelve ELIMINADA, NO_EXISTE o EN_USO. Cualquier otro fallo se propaga como RuntimeException.
     *
     * @return self::ELIMINADA|self::NO_EXISTE|self::EN_USO
     */
    public function eliminar(int $id): string
    {
        try {
            $stmt = $this->query('DELETE FROM rutina WHERE id_rutina = ?', 'i', [$id]);
            $filasAfectadas = (int) $stmt->affected_rows;
            $stmt->close();
        } catch (RuntimeException $excepcion) {
            if (in_array($excepcion->getCode(), [self::ERROR_FK_HIJOS, self::ERROR_FK_REFERENCIADA], true)) {
                return self::EN_USO;
            }

            throw $excepcion;
        }

        return $filasAfectadas === 1 ? self::ELIMINADA : self::NO_EXISTE;
    }
}
