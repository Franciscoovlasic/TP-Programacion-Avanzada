<?php
declare(strict_types=1);

const ROLES_VALIDOS = ['ADMIN', 'USUARIO'];

/**
 * Returns an absolute URL path for this installation without depending on the host or port.
 */
function rutaAplicacion(string $recurso = ''): string
{
    static $basePath;

    if ($basePath === null) {
        $projectRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
            ? realpath((string) $_SERVER['DOCUMENT_ROOT'])
            : false;

        if ($projectRoot !== false && $documentRoot !== false) {
            $projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
            $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
            $documentRoot = $documentRoot === '' ? '/' : $documentRoot;

            if ($projectRoot === $documentRoot) {
                $basePath = '/';
            } else {
                $documentRootPrefix = $documentRoot === '/' ? '/' : $documentRoot . '/';

                if (strncmp($projectRoot, $documentRootPrefix, strlen($documentRootPrefix)) === 0) {
                    $basePath = '/' . trim(substr($projectRoot, strlen($documentRoot)), '/') . '/';
                }
            }
        }

        // Fallback for environments that do not expose DOCUMENT_ROOT (for example CLI).
        $basePath ??= '/tpPA/tp/';
    }

    return $basePath . ltrim($recurso, '/');
}

function iniciarSesionSegura(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);

    session_start();
}

function sesionAutenticada(): bool
{
    return isset($_SESSION['id_usuario'], $_SESSION['nombre'], $_SESSION['dni'], $_SESSION['rol'])
        && is_int($_SESSION['id_usuario'])
        && $_SESSION['id_usuario'] > 0
        && is_string($_SESSION['nombre'])
        && is_string($_SESSION['dni'])
        && is_string($_SESSION['rol'])
        && in_array($_SESSION['rol'], ROLES_VALIDOS, true);
}

function usuarioTieneRol(string $rol): bool
{
    return sesionAutenticada() && $_SESSION['rol'] === $rol;
}

/**
 * Protects pages that require any valid authenticated session.
 * Sessions from before role support are intentionally rejected.
 */
function requiereAutenticacion(): void
{
    iniciarSesionSegura();

    if (sesionAutenticada()) {
        return;
    }

    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['error'] = 'Tu sesión no es válida. Iniciá sesión nuevamente.';
    header('Location: ' . rutaAplicacion('index.php'));
    exit;
}

/**
 * Protects a page by one or more roles. Use requiereRol('ADMIN') for
 * administrative pages and requiereRol('USUARIO') for user-only pages.
 */
function requiereRol(string|array $roles): void
{
    requiereAutenticacion();
    $rolesPermitidos = is_array($roles) ? $roles : [$roles];

    if (in_array($_SESSION['rol'], $rolesPermitidos, true)) {
        return;
    }

    $_SESSION['error'] = 'No tenés permisos para acceder a esa página.';
    header('Location: ' . rutaAplicacion('inicio.php'));
    exit;
}

function redirigirSegunRol(): void
{
    requiereAutenticacion();

    $destino = $_SESSION['rol'] === 'ADMIN'
        ? rutaAplicacion('admin/index.php')
        : rutaAplicacion('usuario/index.php');

    header('Location: ' . $destino);
    exit;
}

function cerrarSesion(): void
{
    iniciarSesionSegura();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $parametros['path'], $parametros['domain'], (bool) $parametros['secure'], (bool) $parametros['httponly']);
    }

    session_destroy();
}

function tokenCsrf(string $clave = 'token_csrf'): string
{
    iniciarSesionSegura();

    if (!isset($_SESSION[$clave]) || !is_string($_SESSION[$clave])) {
        $_SESSION[$clave] = bin2hex(random_bytes(32));
    }

    return $_SESSION[$clave];
}

function validarTokenCsrf(string $token, string $clave = 'token_csrf'): bool
{
    iniciarSesionSegura();

    return isset($_SESSION[$clave])
        && is_string($_SESSION[$clave])
        && hash_equals($_SESSION[$clave], $token);
}
