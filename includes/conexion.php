<?php
declare(strict_types=1);

/**
 * Crea la conexión centralizada a MySQL para el entorno local de XAMPP.
 *
 * Las variables de entorno permiten cambiar la configuración sin guardar
 * credenciales en el repositorio.
 */
function conectarBaseDeDatos(): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);

    cargarConfiguracionEntorno();

    $host = getenv('DB_HOST') ?: 'localhost';
    $usuario = getenv('DB_USER') ?: 'root';
    $contrasena = getenv('DB_PASSWORD') ?: '';
    $baseDeDatos = getenv('DB_NAME') ?: 'forjagym';
    $puerto = (int) (getenv('DB_PORT') ?: 3306);

    $conexion = new mysqli($host, $usuario, $contrasena, $baseDeDatos, $puerto);

    if ($conexion->connect_errno !== 0) {
        error_log('No se pudo conectar a MySQL: ' . $conexion->connect_error);
        throw new RuntimeException('No se pudo establecer la conexión con la base de datos.');
    }

    if (!$conexion->set_charset('utf8mb4')) {
        $conexion->close();
        throw new RuntimeException('No se pudo configurar la codificación de la base de datos.');
    }

    return $conexion;
}

/**
 * Carga las variables de base de datos desde el .env local, si existe.
 *
 * No reemplaza variables ya definidas por el entorno y solo acepta las claves
 * que utiliza esta conexión. El archivo es opcional y nunca se informa su
 * contenido en los mensajes de error.
 */
function cargarConfiguracionEntorno(): void
{
    static $cargado = false;

    if ($cargado) {
        return;
    }

    $cargado = true;
    $archivo = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

    if (!is_readable($archivo)) {
        return;
    }

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lineas === false) {
        return;
    }

    $clavesPermitidas = ['DB_HOST', 'DB_USER', 'DB_PASSWORD', 'DB_NAME', 'DB_PORT'];

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || $linea[0] === '#') {
            continue;
        }

        if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/', $linea, $coincidencias)) {
            continue;
        }

        $clave = $coincidencias[1];

        if (!in_array($clave, $clavesPermitidas, true) || getenv($clave) !== false) {
            continue;
        }

        $valor = trim($coincidencias[2]);

        if (strlen($valor) >= 2) {
            $primero = $valor[0];
            $ultimo = $valor[strlen($valor) - 1];

            if (($primero === '"' && $ultimo === '"') || ($primero === "'" && $ultimo === "'")) {
                $valor = substr($valor, 1, -1);
            }
        }

        putenv($clave . '=' . $valor);
    }
}
