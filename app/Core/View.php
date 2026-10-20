<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    /**
     * Incluye app/Views/{vista}.php con los datos extraídos como variables locales.
     *
     * @param array<string, mixed> $datos
     */
    public static function render(string $vista, array $datos = []): void
    {
        if (preg_match('/^[A-Za-z0-9_\/-]+$/', $vista) !== 1 || str_contains($vista, '..')) {
            throw new RuntimeException('Nombre de vista no válido.');
        }

        $archivo = dirname(__DIR__) . '/Views/' . $vista . '.php';

        if (!is_file($archivo)) {
            throw new RuntimeException('La vista solicitada no existe.');
        }

        self::incluir($archivo, $datos);
    }

    /**
     * Aislado en un método propio para que las variables locales de la vista
     * no colisionen con las de render().
     *
     * @param array<string, mixed> $__datos
     */
    private static function incluir(string $__archivo, array $__datos): void
    {
        extract($__datos, EXTR_SKIP);
        require $__archivo;
    }
}
