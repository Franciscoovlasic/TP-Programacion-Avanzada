<?php
declare(strict_types=1);

namespace App\Core;

use mysqli;

require_once dirname(__DIR__, 2) . '/includes/conexion.php';

final class Database
{
    private static ?mysqli $conexion = null;

    public static function connection(): mysqli
    {
        if (self::$conexion === null) {
            self::$conexion = \conectarBaseDeDatos();
        }

        return self::$conexion;
    }
}
