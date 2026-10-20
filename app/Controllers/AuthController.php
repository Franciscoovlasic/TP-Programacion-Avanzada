<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use mysqli_sql_exception;
use RuntimeException;

/**
 * Lógica de autenticación migrada desde procesoLogin.php, procesoRegistro.php y logout.php.
 * El comportamiento (validaciones, mensajes, redirecciones y claves de sesión) es el original.
 */
final class AuthController extends Controller
{
    private const MENSAJE_SERVICIO = 'El servicio no está disponible en este momento.';
    private const MENSAJE_CREDENCIALES = 'DNI o contraseña incorrectos.';
    private const MENSAJE_DUPLICADO = 'El DNI o email ya se encuentra registrado.';

    /**
     * Muestra el formulario de acceso (antes en index.php). Mantiene el orden original:
     * sesión, redirección si ya hay sesión, token CSRF, CAPTCHA y mensajes flash.
     */
    public function mostrarLogin(): void
    {
        iniciarSesionSegura();

        if (sesionAutenticada()) {
            redirigirSegunRol();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;

        $captchaAlphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $captchaCode = '';
        for ($i = 0; $i < 6; $i++) {
            $captchaCode .= $captchaAlphabet[random_int(0, strlen($captchaAlphabet) - 1)];
        }
        $_SESSION['captcha_code'] = $captchaCode;

        $exito = null;
        if (isset($_SESSION['exito'])) {
            $exito = (string) $_SESSION['exito'];
            unset($_SESSION['exito']);
        }

        $errores = [];
        if (isset($_SESSION['error'])) {
            $errores = is_array($_SESSION['error']) ? $_SESSION['error'] : [$_SESSION['error']];
            unset($_SESSION['error']);
        }

        $this->render('auth/login', [
            'token' => $token,
            'exito' => $exito,
            'errores' => $errores,
        ]);
    }

    public function login(): void
    {
        $errores = [];
        $tokenInvalido = (
            !isset($_POST['token']) ||
            !is_string($_POST['token']) ||
            !isset($_SESSION['token']) ||
            !is_string($_SESSION['token']) ||
            !hash_equals($_SESSION['token'], $_POST['token'])
        );

        if ($tokenInvalido) {
            $errores[] = 'Token de seguridad inválido o ausente.';
        }

        unset($_SESSION['token']);

        $captchaInput = isset($_POST['captcha']) && is_string($_POST['captcha'])
            ? strtoupper(trim($_POST['captcha']))
            : '';
        $captchaCode = isset($_SESSION['captcha_code']) && is_string($_SESSION['captcha_code'])
            ? $_SESSION['captcha_code']
            : '';

        if ($captchaInput === '' || $captchaCode === '' || !hash_equals($captchaCode, $captchaInput)) {
            $errores[] = 'CAPTCHA inválido o ausente.';
        }

        unset($_SESSION['captcha_code']);

        // Solo se procesa si llega por POST
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect('index.php');
        }

        $dni = isset($_POST['dni']) && is_string($_POST['dni']) ? trim($_POST['dni']) : '';
        $contrasena = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? $_POST['contrasena'] : '';

        // Validación server-side: nunca confiar solo en la validación de JS
        if ($dni === '' || $contrasena === '') {
            $errores[] = self::MENSAJE_CREDENCIALES;
        }

        if ($errores === []) {
            try {
                $usuario = (new Usuario())->buscarPorDni($dni);

                if (
                    $usuario === null
                    || !password_verify($contrasena, $usuario['contrasenia'])
                    || !in_array($usuario['rol'], ROLES_VALIDOS, true)
                ) {
                    $errores[] = self::MENSAJE_CREDENCIALES;
                } else {
                    session_regenerate_id(true);
                    $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
                    $_SESSION['dni'] = $usuario['dni'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['rol'] = $usuario['rol'];
                    unset($_SESSION['error']);

                    redirigirSegunRol();
                }
            } catch (RuntimeException $excepcion) {
                error_log($excepcion->getMessage());
                $errores[] = self::MENSAJE_SERVICIO;
            } catch (mysqli_sql_exception $excepcion) {
                error_log($excepcion->getMessage());
                $errores[] = self::MENSAJE_SERVICIO;
            }
        }

        if ($errores !== []) {
            $_SESSION['error'] = $errores;
            $this->redirect('index.php');
        }
    }

    public function registro(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect('registro.php');
        }

        $errores = [];
        $tokenValido = isset($_POST['token'], $_SESSION['token_registro'])
            && is_string($_POST['token'])
            && is_string($_SESSION['token_registro'])
            && hash_equals($_SESSION['token_registro'], $_POST['token']);
        unset($_SESSION['token_registro']);

        if (!$tokenValido) {
            $errores[] = 'Token de seguridad inválido o ausente.';
        }

        $nombre = isset($_POST['nombre']) && is_string($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
        $dni = isset($_POST['dni']) && is_string($_POST['dni']) ? trim($_POST['dni']) : '';
        $contrasena = isset($_POST['contrasena']) && is_string($_POST['contrasena']) ? $_POST['contrasena'] : '';
        $confirmacion = isset($_POST['confirmar_contrasena']) && is_string($_POST['confirmar_contrasena'])
            ? $_POST['confirmar_contrasena']
            : '';

        if ($nombre === '' || $email === '' || $dni === '' || $contrasena === '' || $confirmacion === '') {
            $errores[] = 'Todos los campos son obligatorios.';
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errores[] = 'Ingresá un email válido.';
        }
        if ($nombre !== '' && mb_strlen($nombre, 'UTF-8') > 100) {
            $errores[] = 'El nombre no puede superar los 100 caracteres.';
        }
        if ($email !== '' && mb_strlen($email, 'UTF-8') > 100) {
            $errores[] = 'El email no puede superar los 100 caracteres.';
        }
        if ($dni !== '' && mb_strlen($dni, 'UTF-8') > 50) {
            $errores[] = 'El DNI no puede superar los 50 caracteres.';
        }
        if ($contrasena !== '' && strlen($contrasena) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if (strlen($contrasena) > 72 || strlen($confirmacion) > 72) {
            $errores[] = 'La contraseña no puede superar los 72 caracteres.';
        }
        if ($contrasena !== $confirmacion) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        // Identificadores reservados para la cuenta maestra de administración.
        // El registro público nunca puede crear ni apropiarse de esta cuenta.
        if ($dni === '99999999' || strtolower($email) === 'admin@forjagym.local') {
            $errores[] = 'Ese DNI o email está reservado para la cuenta de administración.';
        }

        if ($errores === []) {
            try {
                $usuario = new Usuario();

                if ($usuario->existeDniOEmail($dni, $email)) {
                    $errores[] = self::MENSAJE_DUPLICADO;
                }

                if ($errores === []) {
                    $hashPassword = password_hash($contrasena, PASSWORD_DEFAULT);

                    if (!$usuario->crear($nombre, $email, $dni, $hashPassword)) {
                        $errores[] = self::MENSAJE_DUPLICADO;
                    }
                }
            } catch (RuntimeException $excepcion) {
                error_log($excepcion->getMessage());
                $errores[] = self::MENSAJE_SERVICIO;
            } catch (mysqli_sql_exception $excepcion) {
                error_log($excepcion->getMessage());
                $errores[] = self::MENSAJE_SERVICIO;
            }
        }

        if ($errores !== []) {
            $_SESSION['error'] = $errores;
            $this->redirect('registro.php');
        }

        $_SESSION['exito'] = 'Registro exitoso. Ya podés iniciar sesión.';
        $this->redirect('index.php');
    }

    public function logout(): void
    {
        cerrarSesion();

        $this->redirect('index.php');
    }
}
