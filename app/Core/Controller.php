<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * @param array<string, mixed> $datos
     */
    protected function render(string $vista, array $datos = []): void
    {
        View::render($vista, $datos);
    }

    /**
     * Redirige y termina la ejecución. Recibe una URL ya resuelta (por ejemplo,
     * generada con rutaAplicacion()).
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Delega en requiereRol() de includes/auth.php, sin duplicar su lógica.
     *
     * @param string|array<int, string> $rol
     */
    protected function requiereRol(string|array $rol): void
    {
        \requiereRol($rol);
    }
}
