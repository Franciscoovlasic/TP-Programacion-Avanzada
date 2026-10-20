<?php
declare(strict_types=1);

/**
 * Helpers globales. Este archivo NO declara namespace a propósito: así e()
 * queda disponible como función global dentro de las vistas.
 */

if (!function_exists('e')) {
    /**
     * Escapa un valor para HTML. Usar en todas las salidas de vistas.
     */
    function e(mixed $valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
