<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use RuntimeException;

/**
 * Gestión de usuarios desde el panel de administración: ver, editar y eliminar.
 * Todas las rutas exigen rol ADMIN; las mutaciones exigen POST y token CSRF.
 */
final class UsuarioController extends Controller
{
    private const MENSAJE_CONSULTA = 'No se pudo consultar el usuario en este momento.';
    private const MENSAJE_NO_EXISTE = 'El usuario no existe.';
    private const MENSAJE_SOLICITUD_INVALIDA = 'La solicitud no es válida.';
    private const MENSAJE_TOKEN = 'La sesión del formulario venció. Recargá la página e intentá nuevamente.';
    private const MENSAJE_DUPLICADO = 'El DNI o el email ya pertenece a otro usuario.';
    private const MENSAJE_GUARDADO = 'No se pudieron guardar los cambios en este momento.';
    private const MENSAJE_ELIMINADO = 'No se pudo eliminar el usuario en este momento.';
    private const MENSAJE_ACTUALIZADO = 'Usuario actualizado correctamente.';
    private const MENSAJE_ELIMINACION_OK = 'Usuario eliminado correctamente. Sus rutinas también se eliminaron.';

    /** Identificadores reservados para la cuenta maestra (mismos valores que el registro público). */
    private const DNI_RESERVADO = '99999999';
    private const EMAIL_RESERVADO = 'admin@forjagym.local';

    /** Ficha de solo lectura del usuario. */
    public function ver(mixed $id): void
    {
        $this->requiereRol('ADMIN');

        $usuario = $this->cargarUsuario($id);

        $this->render('usuarios/ver', [
            'rutaBase' => '../../',
            'usaDataTables' => false,
            'usuario' => $usuario,
        ]);
    }

    /**
     * GET muestra el formulario; POST valida y guarda los cambios.
     */
    public function editar(mixed $id): void
    {
        $this->requiereRol('ADMIN');

        $usuario = $this->cargarUsuario($id);

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $this->guardar((int) $usuario['id_usuario'], $usuario);
            return;
        }

        $this->mostrarFormulario($usuario, [
            'nombre' => (string) $usuario['nombre'],
            'email' => (string) $usuario['email'],
            'dni' => (string) $usuario['dni'],
            'rol' => (string) $usuario['rol'],
        ], []);
    }

    /**
     * Solo acepta POST. Elimina el usuario; sus rutinas se borran en cascada por la FK de la BD.
     */
    public function eliminar(mixed $id): void
    {
        $this->requiereRol('ADMIN');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect(rutaAplicacion('admin/index.php'));
        }

        $token = $this->textoDePost('token');
        $idValido = $this->idEntero($id);

        if (!validarTokenCsrf($token) || $idValido === null) {
            $this->volverAlPanel(self::MENSAJE_SOLICITUD_INVALIDA);
        }

        if ($idValido === $this->idSesion()) {
            $this->volverAlPanel('No podés eliminar tu propia cuenta de administrador.');
        }

        try {
            $eliminado = (new Usuario())->eliminar($idValido);
            $mensaje = $eliminado ? self::MENSAJE_ELIMINACION_OK : self::MENSAJE_NO_EXISTE;
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $mensaje = self::MENSAJE_ELIMINADO;
        }

        $this->volverAlPanel($mensaje);
    }

    /**
     * Valida y actualiza. Si hay errores, vuelve a mostrar el formulario con los datos ingresados
     * (la contraseña nunca se repone en el formulario).
     *
     * @param array<string, mixed> $actual
     */
    private function guardar(int $id, array $actual): void
    {
        $datos = [
            'nombre' => trim($this->textoDePost('nombre')),
            'email' => trim($this->textoDePost('email')),
            'dni' => trim($this->textoDePost('dni')),
            'rol' => $this->textoDePost('rol'),
            'contrasena' => $this->textoDePost('contrasena'),
        ];

        $errores = $this->validarEdicion($id, $actual, $datos, $this->textoDePost('token'));

        if ($errores === []) {
            try {
                $modelo = new Usuario();
                $hash = $datos['contrasena'] !== ''
                    ? password_hash($datos['contrasena'], PASSWORD_DEFAULT)
                    : null;

                if ($modelo->existeDniOEmailExcepto($datos['dni'], $datos['email'], $id)) {
                    $errores[] = self::MENSAJE_DUPLICADO;
                } elseif (!$modelo->actualizar($id, $datos['nombre'], $datos['email'], $datos['dni'], $datos['rol'], $hash)) {
                    // Carrera: otro alta tomó el DNI o email entre la consulta y la actualización.
                    $errores[] = self::MENSAJE_DUPLICADO;
                }
            } catch (RuntimeException $excepcion) {
                error_log($excepcion->getMessage());
                $errores[] = self::MENSAJE_GUARDADO;
            }
        }

        if ($errores !== []) {
            $this->mostrarFormulario($actual, $datos, $errores);
            return;
        }

        // Si el admin edita su propia cuenta, el encabezado del sitio lee estos datos de la sesión.
        if ($id === $this->idSesion()) {
            $_SESSION['nombre'] = $datos['nombre'];
            $_SESSION['dni'] = $datos['dni'];
        }

        $this->volverAlPanel(self::MENSAJE_ACTUALIZADO);
    }

    /**
     * @param array<string, mixed> $actual
     * @param array{nombre: string, email: string, dni: string, rol: string, contrasena: string} $datos
     * @return array<int, string>
     */
    private function validarEdicion(int $id, array $actual, array $datos, string $token): array
    {
        $errores = [];

        if (!validarTokenCsrf($token)) {
            $errores[] = self::MENSAJE_TOKEN;
        }

        if ($datos['nombre'] === '' || mb_strlen($datos['nombre'], 'UTF-8') > 100) {
            $errores[] = 'Ingresá un nombre de hasta 100 caracteres.';
        }

        if (
            $datos['email'] === ''
            || mb_strlen($datos['email'], 'UTF-8') > 100
            || filter_var($datos['email'], FILTER_VALIDATE_EMAIL) === false
        ) {
            $errores[] = 'Ingresá un email válido de hasta 100 caracteres.';
        }

        if ($datos['dni'] === '' || mb_strlen($datos['dni'], 'UTF-8') > 50) {
            $errores[] = 'Ingresá un DNI de hasta 50 caracteres.';
        }

        if (!in_array($datos['rol'], ROLES_VALIDOS, true)) {
            $errores[] = 'Seleccioná un rol válido.';
        }

        if ($id === $this->idSesion() && $datos['rol'] !== 'ADMIN') {
            $errores[] = 'No podés quitarte tu propio rol de administrador.';
        }

        // Solo se bloquea si el valor reservado se intenta asignar a otra cuenta.
        $dniReservado = $datos['dni'] === self::DNI_RESERVADO && (string) $actual['dni'] !== self::DNI_RESERVADO;
        $emailReservado = strtolower($datos['email']) === self::EMAIL_RESERVADO
            && strtolower((string) $actual['email']) !== self::EMAIL_RESERVADO;

        if ($dniReservado || $emailReservado) {
            $errores[] = 'Ese DNI o email está reservado para la cuenta de administración.';
        }

        // Vacía = no cambiar la contraseña. Si se completa, debe tener entre 8 y 72 bytes (límite de bcrypt).
        if ($datos['contrasena'] !== '' && (strlen($datos['contrasena']) < 8 || strlen($datos['contrasena']) > 72)) {
            $errores[] = 'La nueva contraseña debe tener entre 8 y 72 caracteres. Dejala vacía para no cambiarla.';
        }

        return $errores;
    }

    /**
     * @param array<string, mixed> $usuario
     * @param array<string, string> $valores
     * @param array<int, string> $errores
     */
    private function mostrarFormulario(array $usuario, array $valores, array $errores): void
    {
        $this->render('usuarios/formulario', [
            'rutaBase' => '../../',
            'usaDataTables' => false,
            'usuario' => $usuario,
            'valores' => $valores,
            'errores' => $errores,
            'token' => tokenCsrf(),
        ]);
    }

    /**
     * Carga el usuario o vuelve al panel con un mensaje amigable. Nunca devuelve null.
     *
     * @return array<string, mixed>
     */
    private function cargarUsuario(mixed $id): array
    {
        $idValido = $this->idEntero($id);

        if ($idValido === null) {
            $this->volverAlPanel(self::MENSAJE_SOLICITUD_INVALIDA);
        }

        try {
            $usuario = (new Usuario())->obtener($idValido);
        } catch (RuntimeException $excepcion) {
            error_log($excepcion->getMessage());
            $this->volverAlPanel(self::MENSAJE_CONSULTA);
        }

        if ($usuario === null) {
            $this->volverAlPanel(self::MENSAJE_NO_EXISTE);
        }

        return $usuario;
    }

    private function volverAlPanel(string $mensaje): void
    {
        $_SESSION['mensaje'] = $mensaje;
        $this->redirect(rutaAplicacion('admin/index.php'));
    }

    /**
     * Entero positivo o null. Acepta el valor crudo de $_GET/$_POST.
     */
    private function idEntero(mixed $valor): ?int
    {
        $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return is_int($id) ? $id : null;
    }

    private function idSesion(): int
    {
        return (int) ($_SESSION['id_usuario'] ?? 0);
    }

    private function textoDePost(string $clave): string
    {
        return isset($_POST[$clave]) && is_string($_POST[$clave]) ? $_POST[$clave] : '';
    }
}
