<?php
declare(strict_types=1);

/**
 * Arranque de la capa MVC. Reutiliza la conexión y la sesión existentes en includes/
 * y registra el autoloader para el namespace App\ (mapeado a app/).
 */

require_once dirname(__DIR__) . '/includes/conexion.php';
require_once dirname(__DIR__) . '/includes/auth.php';

iniciarSesionSegura();

// Helper global e() disponible para todas las vistas.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Core/View.php';

spl_autoload_register(static function (string $clase): void {
    $prefijo = 'App\\';

    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }

    $relativa = str_replace('\\', '/', substr($clase, strlen($prefijo)));
    $archivo = __DIR__ . '/' . $relativa . '.php';

    if (is_file($archivo)) {
        require_once $archivo;
    }
});
