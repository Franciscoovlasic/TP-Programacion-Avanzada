<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;

final class Usuario extends Model
{
    /** Código de MySQL para violación de clave única (DNI o email duplicado). */
    private const ERROR_DUPLICADO = 1062;

    /** Columnas seguras para vistas: nunca se incluye el hash de contrasenia. */
    private const COLUMNAS_PUBLICAS = 'id_usuario, nombre, apellido, email, dni, rol, fecha_registro';

    /**
     * @return array{id_usuario: int|string, nombre: string, apellido: string|null, dni: string, contrasenia: string, rol: string}|null
     */
    public function buscarPorDni(string $dni): ?array
    {
        $fila = $this->fetchOne(
            'SELECT id_usuario, nombre, apellido, dni, contrasenia, rol FROM usuario WHERE dni = ? LIMIT 1',
            's',
            [$dni]
        );

        return $fila;
    }

    public function existeDniOEmail(string $dni, string $email): bool
    {
        return $this->fetchOne(
            'SELECT dni, email FROM usuario WHERE dni = ? OR email = ? LIMIT 1',
            'ss',
            [$dni, $email]
        ) !== null;
    }

    /**
     * True si otro usuario (distinto de $idExcluido) ya tiene ese DNI o ese email.
     */
    public function existeDniOEmailExcepto(string $dni, string $email, int $idExcluido): bool
    {
        return $this->fetchOne(
            'SELECT id_usuario FROM usuario WHERE (dni = ? OR email = ?) AND id_usuario <> ? LIMIT 1',
            'ssi',
            [$dni, $email, $idExcluido]
        ) !== null;
    }

    /**
     * Inserta un usuario con rol USUARIO. Devuelve false si el DNI o el email ya existen.
     * Cualquier otro fallo se propaga como RuntimeException.
     */
    public function crear(string $nombre, string $apellido, string $email, string $dni, string $hash): bool
    {
        try {
            $this->query(
                'INSERT INTO usuario (nombre, apellido, email, dni, contrasenia, rol) VALUES (?, ?, ?, ?, ?, ?)',
                'ssssss',
                [$nombre, $apellido, $email, $dni, $hash, 'USUARIO']
            )->close();
        } catch (RuntimeException $excepcion) {
            if ($excepcion->getCode() === self::ERROR_DUPLICADO) {
                return false;
            }

            throw $excepcion;
        }

        return true;
    }

    /**
     * Todos los usuarios, sin hash de contraseña.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listar(): array
    {
        return $this->fetchAll(
            'SELECT ' . self::COLUMNAS_PUBLICAS . ' FROM usuario ORDER BY nombre ASC, apellido ASC'
        );
    }

    /**
     * Alumnos (rol USUARIO) con su rutina más reciente, si tienen alguna.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarConUltimaRutina(): array
    {
        return $this->fetchAll(
            'SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.dni, r.nombre_rutina, r.fecha_creacion
             FROM usuario u
             LEFT JOIN rutina r ON r.id_rutina = (
                 SELECT r2.id_rutina FROM rutina r2 WHERE r2.id_usuario = u.id_usuario
                 ORDER BY r2.fecha_creacion DESC, r2.id_rutina DESC LIMIT 1
             )
             WHERE u.rol = ? ORDER BY u.nombre ASC, u.apellido ASC',
            's',
            ['USUARIO']
        );
    }

    /**
     * Datos de un usuario sin el hash de contraseña.
     *
     * @return array<string, mixed>|null
     */
    public function obtener(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT ' . self::COLUMNAS_PUBLICAS . ' FROM usuario WHERE id_usuario = ? LIMIT 1',
            'i',
            [$id]
        );
    }

    /**
     * Actualiza nombre, email, dni y rol. Si $hashContrasenia es null, la contraseña no cambia.
     * Devuelve false si el DNI o el email ya pertenecen a otro usuario (1062).
     * Cualquier otro fallo se propaga como RuntimeException.
     */
    public function actualizar(
        int $id,
        string $nombre,
        string $email,
        string $dni,
        string $rol,
        ?string $hashContrasenia = null
    ): bool {
        try {
            if ($hashContrasenia === null) {
                $this->query(
                    'UPDATE usuario SET nombre = ?, email = ?, dni = ?, rol = ? WHERE id_usuario = ?',
                    'ssssi',
                    [$nombre, $email, $dni, $rol, $id]
                )->close();
            } else {
                $this->query(
                    'UPDATE usuario SET nombre = ?, email = ?, dni = ?, rol = ?, contrasenia = ? WHERE id_usuario = ?',
                    'sssssi',
                    [$nombre, $email, $dni, $rol, $hashContrasenia, $id]
                )->close();
            }
        } catch (RuntimeException $excepcion) {
            if ($excepcion->getCode() === self::ERROR_DUPLICADO) {
                return false;
            }

            throw $excepcion;
        }

        return true;
    }

    /**
     * Elimina el usuario. Sus rutinas (y días/ejercicios) se borran por ON DELETE CASCADE.
     * Devuelve false si no existía.
     */
    public function eliminar(int $id): bool
    {
        $stmt = $this->query('DELETE FROM usuario WHERE id_usuario = ?', 'i', [$id]);
        $filasAfectadas = (int) $stmt->affected_rows;
        $stmt->close();

        return $filasAfectadas === 1;
    }
}
