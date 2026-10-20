<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use RuntimeException;

final class Usuario extends Model
{
    /** Código de MySQL para violación de clave única (DNI o email duplicado). */
    private const ERROR_DUPLICADO = 1062;

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
}
