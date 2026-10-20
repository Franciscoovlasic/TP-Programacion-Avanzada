<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ejercicio;
use RuntimeException;

/**
 * Lógica de ejercicios migrada desde las páginas de admin/ejercicios/ y usuario/ejercicios.php.
 * Guards, validaciones, mensajes, redirecciones y claves de sesión son los originales.
 */
final class EjercicioController extends Controller
{
    private const MENSAJE_NOMBRE_INVALIDO = 'El nombre es obligatorio y debe tener hasta 100 caracteres.';
    private const MENSAJE_SOLICITUD_INVALIDA = 'La solicitud no es válida.';
    private const MENSAJE_CARGA = 'No se pudo cargar el catálogo en este momento.';
    private const MENSAJE_CONSULTA = 'No se pudo consultar el ejercicio en este momento.';

    public function index(): void
    {
        $this->requiereRol('ADMIN');

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);
        $error = null;

        try {
            $ejercicios = (new Ejercicio())->listar();
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $ejercicios = [];
            $error = self::MENSAJE_CARGA;
        }

        $this->render('ejercicios/index', [
            'rutaBase' => '../../',
            'usaDataTables' => true,
            'mensaje' => $mensaje,
            'error' => $error,
            'ejercicios' => $ejercicios,
            'token' => tokenCsrf(),
        ]);
    }

    /**
     * Formulario de alta (sin id) o de modificación (con ?id=). Lo usan GET de nuevo.php,
     * editar.php y formulario.php.
     */
    public function formulario(): void
    {
        $this->requiereRol('ADMIN');

        $id = $this->idDeGet();
        $editar = $id !== null;
        $ejercicio = null;

        if ($editar) {
            try {
                $ejercicio = (new Ejercicio())->obtener($id);
            } catch (RuntimeException $excepcion) {
                error_log($excepcion->getMessage());
                $_SESSION['mensaje'] = self::MENSAJE_CONSULTA;
                $this->redirect('index.php');
            }

            if ($ejercicio === null) {
                $_SESSION['mensaje'] = 'El ejercicio no existe.';
                $this->redirect('index.php');
            }
        }

        // Si la última operación falló por validación, se reutiliza el nombre enviado.
        $datosFormulario = $_SESSION['ejercicio_formulario'] ?? null;
        if (is_array($datosFormulario) && (($datosFormulario['id'] ?? null) === $id)) {
            $ejercicio = [
                'nombre_ejercicio' => (string) ($datosFormulario['nombre_ejercicio'] ?? ''),
            ];
            unset($_SESSION['ejercicio_formulario']);
        }

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);

        $this->render('ejercicios/formulario', [
            'rutaBase' => '../../',
            'usaDataTables' => false,
            'editar' => $editar,
            'id' => $id,
            'ejercicio' => $ejercicio,
            'mensaje' => $mensaje,
            'token' => tokenCsrf(),
        ]);
    }

    /**
     * Alta por POST. Cualquier otro método muestra el formulario.
     */
    public function nuevo(): void
    {
        $this->requiereRol('ADMIN');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->formulario();
            return;
        }

        $token = $this->textoDePost('token');
        $nombre = trim($this->textoDePost('nombre_ejercicio'));

        $_SESSION['ejercicio_formulario'] = [
            'id' => null,
            'nombre_ejercicio' => $nombre,
        ];

        if (!validarTokenCsrf($token) || !$this->nombreValido($nombre)) {
            $_SESSION['mensaje'] = self::MENSAJE_NOMBRE_INVALIDO;
            $this->redirect('formulario.php');
        }

        try {
            (new Ejercicio())->crear($nombre);
            unset($_SESSION['ejercicio_formulario']);
            $_SESSION['mensaje'] = 'Ejercicio agregado correctamente.';
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = 'No se pudo guardar el ejercicio. Revisá los datos e intentá nuevamente.';
        }

        $this->redirect('index.php');
    }

    /**
     * Modificación. GET muestra el formulario; POST guarda los cambios.
     */
    public function editar(): void
    {
        $this->requiereRol('ADMIN');

        $id = $this->idDeGet();
        if ($id === null) {
            $this->redirect('index.php');
        }

        $metodo = $_SERVER['REQUEST_METHOD'] ?? '';
        if ($metodo === 'GET') {
            $this->formulario();
            return;
        }

        if ($metodo !== 'POST') {
            $this->redirect('index.php');
        }

        $token = $this->textoDePost('token');
        $nombre = trim($this->textoDePost('nombre_ejercicio'));

        $_SESSION['ejercicio_formulario'] = [
            'id' => $id,
            'nombre_ejercicio' => $nombre,
        ];

        if (!validarTokenCsrf($token) || !$this->nombreValido($nombre)) {
            $_SESSION['mensaje'] = self::MENSAJE_NOMBRE_INVALIDO;
            $this->redirect('formulario.php?id=' . $id);
        }

        try {
            (new Ejercicio())->actualizar($id, $nombre);
            unset($_SESSION['ejercicio_formulario']);
            $_SESSION['mensaje'] = 'Ejercicio modificado correctamente.';
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = 'No se pudo modificar el ejercicio.';
        }

        // Si el guardado falló, se conservan los datos del formulario y el mensaje.
        if (isset($_SESSION['ejercicio_formulario'])) {
            $this->redirect('formulario.php?id=' . $id);
        }

        $this->redirect('index.php');
    }

    public function eliminar(): void
    {
        $this->requiereRol('ADMIN');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect('index.php');
        }

        $token = $this->textoDePost('token');
        $id = filter_var($_POST['id_ejercicio'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!validarTokenCsrf($token) || !is_int($id)) {
            $_SESSION['mensaje'] = self::MENSAJE_SOLICITUD_INVALIDA;
            $this->redirect('index.php');
        }

        try {
            $resultado = (new Ejercicio())->eliminar($id);
            $_SESSION['mensaje'] = $resultado === Ejercicio::ELIMINADO
                ? 'Ejercicio eliminado correctamente.'
                : 'No se puede eliminar porque el ejercicio ya está asignado a una rutina.';
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = 'No se pudo eliminar el ejercicio en este momento.';
        }

        $this->redirect('index.php');
    }

    /**
     * Catálogo de solo lectura para el área de usuario (USUARIO y ADMIN).
     */
    public function catalogo(): void
    {
        $this->requiereRol(['USUARIO', 'ADMIN']);

        $error = null;

        try {
            $ejercicios = (new Ejercicio())->listar();
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $ejercicios = [];
            $error = self::MENSAJE_CARGA;
        }

        $this->render('ejercicios/catalogo-usuario', [
            'rutaBase' => '../',
            'usaDataTables' => true,
            'error' => $error,
            'ejercicios' => $ejercicios,
        ]);
    }

    /**
     * Catálogo de solo lectura dentro del área ADMIN.
     */
    public function catalogoAdmin(): void
    {
        $this->requiereRol('ADMIN');

        try {
            $ejercicios = (new Ejercicio())->listar();
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $ejercicios = [];
        }

        $this->render('ejercicios/catalogo-admin', [
            'rutaBase' => '../../',
            'usaDataTables' => true,
            'ejercicios' => $ejercicios,
        ]);
    }

    /**
     * Lee ?id= como entero positivo. Devuelve null si falta o no es válido.
     */
    private function idDeGet(): ?int
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return is_int($id) ? $id : null;
    }

    private function textoDePost(string $clave): string
    {
        return isset($_POST[$clave]) && is_string($_POST[$clave]) ? $_POST[$clave] : '';
    }

    private function nombreValido(string $nombre): bool
    {
        return $nombre !== '' && mb_strlen($nombre) <= 100;
    }
}
