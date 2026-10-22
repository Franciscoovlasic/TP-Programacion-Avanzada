<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Rutina;
use App\Models\Usuario;
use InvalidArgumentException;
use RuntimeException;

/**
 * Lógica de rutinas migrada desde admin/rutinas/*.php, usuario/rutinas.php y usuario/rutina.php.
 * Guards, validaciones, mensajes, redirecciones y claves de sesión son los originales, salvo
 * los mensajes de error de BD por FK en eliminar() y de consulta en detalle()/verPropia()/formulario(),
 * que ahora son amigables en lugar de dejar caer la página.
 */
final class RutinaController extends Controller
{
    private const MENSAJE_DATOS_INVALIDOS = 'El propietario es obligatorio y el nombre debe tener hasta 100 caracteres.';
    private const MENSAJE_SOLICITUD_INVALIDA = 'La solicitud no es válida.';
    private const MENSAJE_CARGA = 'No se pudieron cargar las rutinas en este momento.';
    private const MENSAJE_CARGA_PROPIAS = 'No se pudieron cargar tus rutinas en este momento.';
    private const MENSAJE_CONSULTA = 'No se pudo consultar la rutina en este momento.';
    private const MENSAJE_PROPIETARIOS = 'No se pudo cargar la lista de propietarios.';
    private const MENSAJE_NO_EXISTE = 'La rutina no existe.';
    private const MENSAJE_NO_PROPIA = 'La rutina no existe o no está asociada a tu usuario.';
    private const MENSAJE_EN_USO = 'No se puede eliminar la rutina porque tiene datos asociados.';

    public function index(): void
    {
        $this->requiereRol('ADMIN');

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);
        $error = null;

        try {
            $rutinas = (new Rutina())->listarTodas();
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $rutinas = [];
            $error = self::MENSAJE_CARGA;
        }

        $this->render('rutinas/index', [
            'rutaBase' => '../../',
            'usaDataTables' => true,
            'mensaje' => $mensaje,
            'error' => $error,
            'rutinas' => $rutinas,
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
        $rutina = null;

        if ($editar) {
            try {
                $rutina = (new Rutina())->obtener($id);
            } catch (RuntimeException $excepcion) {
                error_log($excepcion->getMessage());
                $_SESSION['mensaje'] = self::MENSAJE_CONSULTA;
                $this->redirect('index.php');
            }

            if ($rutina === null) {
                $_SESSION['mensaje'] = self::MENSAJE_NO_EXISTE;
                $this->redirect('index.php');
            }
        }

        // Si la última operación falló por validación, se reutilizan los datos enviados.
        $datosFormulario = $_SESSION['rutina_formulario'] ?? null;
        if (is_array($datosFormulario) && (($datosFormulario['id'] ?? null) === ($editar ? $id : null))) {
            $rutina = array_merge($rutina ?? [], $datosFormulario);
            unset($_SESSION['rutina_formulario']);
        }

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);

        try {
            $usuarios = (new Rutina())->listarUsuarios();
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $usuarios = [];
            $mensaje = self::MENSAJE_PROPIETARIOS;
        }

        $this->render('rutinas/formulario', [
            'rutaBase' => '../../',
            'usaDataTables' => false,
            'editar' => $editar,
            'id' => $id,
            'rutina' => $rutina,
            'usuarios' => $usuarios,
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
        $idUsuario = $this->idUsuarioDePost();
        $nombre = trim($this->textoDePost('nombre_rutina'));
        $descripcion = trim($this->textoDePost('descripcion'));

        $_SESSION['rutina_formulario'] = [
            'id' => null,
            'id_usuario' => $idUsuario ?? 0,
            'nombre_rutina' => $nombre,
            'descripcion' => $descripcion,
        ];

        if (!validarTokenCsrf($token) || $idUsuario === null || !$this->nombreValido($nombre)) {
            $_SESSION['mensaje'] = self::MENSAJE_DATOS_INVALIDOS;
            $this->redirect('formulario.php');
        }

        try {
            $modelo = new Rutina();

            if (!$modelo->existeUsuario($idUsuario)) {
                throw new InvalidArgumentException('El usuario propietario no existe.');
            }

            $modelo->crear($idUsuario, $nombre, $descripcion);
            unset($_SESSION['rutina_formulario']);
            $_SESSION['mensaje'] = 'Rutina agregada correctamente.';
        } catch (InvalidArgumentException $excepcion) {
            $_SESSION['mensaje'] = $excepcion->getMessage();
            $this->redirect('formulario.php');
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = 'No se pudo guardar la rutina. Revisá los datos e intentá nuevamente.';
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
        $idUsuario = $this->idUsuarioDePost();
        $nombre = trim($this->textoDePost('nombre_rutina'));
        $descripcion = trim($this->textoDePost('descripcion'));

        $_SESSION['rutina_formulario'] = [
            'id' => $id,
            'id_usuario' => $idUsuario ?? 0,
            'nombre_rutina' => $nombre,
            'descripcion' => $descripcion,
        ];

        if (!validarTokenCsrf($token) || $idUsuario === null || !$this->nombreValido($nombre)) {
            $_SESSION['mensaje'] = self::MENSAJE_DATOS_INVALIDOS;
            $this->redirect('formulario.php?id=' . $id);
        }

        try {
            $modelo = new Rutina();

            if (!$modelo->existeUsuario($idUsuario)) {
                throw new InvalidArgumentException('El usuario propietario no existe.');
            }

            if ($modelo->obtener($id) === null) {
                throw new InvalidArgumentException(self::MENSAJE_NO_EXISTE);
            }

            $modelo->actualizar($id, $idUsuario, $nombre, $descripcion);
            unset($_SESSION['rutina_formulario']);
            $_SESSION['mensaje'] = 'Rutina modificada correctamente.';
        } catch (InvalidArgumentException $excepcion) {
            $_SESSION['mensaje'] = $excepcion->getMessage();
            $this->redirect('formulario.php?id=' . $id);
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = 'No se pudo modificar la rutina.';
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
        $id = filter_var($_POST['id_rutina'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!validarTokenCsrf($token) || !is_int($id)) {
            $_SESSION['mensaje'] = self::MENSAJE_SOLICITUD_INVALIDA;
            $this->redirect($this->destinoTrasEliminar());
        }

        try {
            $resultado = (new Rutina())->eliminar($id);
            $_SESSION['mensaje'] = match ($resultado) {
                Rutina::ELIMINADA => 'Rutina eliminada correctamente.',
                Rutina::EN_USO => self::MENSAJE_EN_USO,
                default => self::MENSAJE_NO_EXISTE,
            };
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = 'No se pudo eliminar la rutina en este momento.';
        }

        $this->redirect($this->destinoTrasEliminar());
    }

    /**
     * Fragmento HTML con las rutinas de un alumno, para la ventana "Gestionar rutinas" del panel.
     * Lo pide admin/rutinas/historial.php por fetch. Recibe el id sin confiar en él: se valida como entero.
     */
    public function historialUsuario(mixed $idUsuario): void
    {
        $this->requiereRol('ADMIN');

        header('Content-Type: text/html; charset=UTF-8');

        $id = filter_var($idUsuario, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (!is_int($id)) {
            http_response_code(400);
            $this->render('rutinas/historial-fragmento', ['error' => 'Alumno inválido.', 'alumno' => null, 'rutinas' => [], 'token' => tokenCsrf()]);
            return;
        }

        try {
            $alumno = (new Usuario())->obtener($id);
            $rutinas = $alumno !== null && $alumno['rol'] === 'USUARIO' ? (new Rutina())->listarPorUsuario($id) : [];
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            http_response_code(500);
            $this->render('rutinas/historial-fragmento', ['error' => 'No se pudo cargar el historial.', 'alumno' => null, 'rutinas' => [], 'token' => tokenCsrf()]);
            return;
        }

        if ($alumno === null || $alumno['rol'] !== 'USUARIO') {
            http_response_code(404);
            $this->render('rutinas/historial-fragmento', ['error' => 'No se encontró el alumno.', 'alumno' => null, 'rutinas' => [], 'token' => tokenCsrf()]);
            return;
        }

        $this->render('rutinas/historial-fragmento', [
            'error' => null,
            'alumno' => $alumno,
            'rutinas' => $rutinas,
            'token' => tokenCsrf(),
        ]);
    }

    /**
     * Destino tras eliminar. Solo se acepta la clave "panel" (lista blanca); cualquier otro
     * valor vuelve al listado de rutinas, como antes. Nunca se redirige a una URL recibida.
     */
    private function destinoTrasEliminar(): string
    {
        return $this->textoDePost('retorno') === 'panel'
            ? rutaAplicacion('admin/index.php')
            : 'index.php';
    }

    /**
     * Detalle de solo lectura dentro del área ADMIN (cualquier rutina).
     */
    public function detalle(): void
    {
        $this->requiereRol('ADMIN');

        $id = $this->idDeGet();

        try {
            $rutina = $id !== null ? (new Rutina())->obtenerConPropietario($id) : null;
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = self::MENSAJE_CONSULTA;
            $this->redirect('index.php');
        }

        if ($rutina === null) {
            $_SESSION['mensaje'] = self::MENSAJE_NO_EXISTE;
            $this->redirect('index.php');
        }

        $this->render('rutinas/detalle', [
            'rutaBase' => '../../',
            'usaDataTables' => false,
            'rutina' => $rutina,
        ]);
    }

    /**
     * Listado de solo lectura de las rutinas propias (USUARIO y ADMIN).
     */
    public function listarPropias(): void
    {
        $this->requiereRol(['USUARIO', 'ADMIN']);

        $error = null;

        try {
            $rutinas = (new Rutina())->listarPorUsuario((int) $_SESSION['id_usuario']);
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $rutinas = [];
            $error = self::MENSAJE_CARGA_PROPIAS;
        }

        $this->render('rutinas/usuario-listado', [
            'rutaBase' => '../',
            'usaDataTables' => true,
            'error' => $error,
            'rutinas' => $rutinas,
        ]);
    }

    /**
     * Detalle de solo lectura de una rutina propia. La búsqueda filtra por el id de la sesión:
     * una rutina ajena responde igual que una inexistente y no se muestra.
     */
    public function verPropia(): void
    {
        $this->requiereRol(['USUARIO', 'ADMIN']);

        $id = $this->idDeGet();
        $dias = [];

        try {
            $modelo = new Rutina();
            $rutina = $id !== null
                ? $modelo->obtenerDeUsuario($id, (int) $_SESSION['id_usuario'])
                : null;

            if ($rutina !== null) {
                $dias = $modelo->diasConEjercicios((int) $rutina['id_rutina']);
            }
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $_SESSION['mensaje'] = self::MENSAJE_CONSULTA;
            $this->redirect('rutinas.php');
        }

        if ($rutina === null) {
            $_SESSION['mensaje'] = self::MENSAJE_NO_PROPIA;
            $this->redirect('rutinas.php');
        }

        $this->render('rutinas/usuario-detalle', [
            'rutaBase' => '../',
            'usaDataTables' => false,
            'rutina' => $rutina,
            'dias' => $dias,
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

    /**
     * Lee id_usuario de POST como entero positivo. Devuelve null si falta o no es válido.
     */
    private function idUsuarioDePost(): ?int
    {
        $id = filter_var($_POST['id_usuario'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

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
