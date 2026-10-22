<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use RuntimeException;

/**
 * Panel principal del administrador: listado de alumnos con su última rutina.
 * Migrado desde admin/index.php, con la misma estructura visual y los mismos mensajes.
 */
final class AdminController extends Controller
{
    private const MENSAJE_CARGA = 'No se pudo cargar la lista de alumnos.';

    public function index(): void
    {
        $this->requiereRol('ADMIN');

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);
        $erroresFlash = consumirErroresFlash();

        $alumnos = [];
        $error = null;

        try {
            $alumnos = (new Usuario())->listarConUltimaRutina();
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $error = self::MENSAJE_CARGA;
        }

        $this->render('admin/index', [
            'rutaBase' => '../',
            'tituloPagina' => 'FORJA GYM - Panel de administración',
            'tabActiva' => 'panel',
            'usaDataTables' => true,
            'mensaje' => $mensaje,
            'erroresFlash' => $erroresFlash,
            'error' => $error,
            'alumnos' => $alumnos,
            'token' => tokenCsrf(),
        ]);
    }
}
