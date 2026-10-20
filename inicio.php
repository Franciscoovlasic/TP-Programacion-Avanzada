<?php
/**
 * Punto de entrada único tras iniciar sesión.
 *
 * Antes mostraba siempre un cartel de "Ingreso correcto" aunque la persona no
 * hubiera iniciado sesión recién, y obligaba a un paso extra para llegar a su
 * área. Ahora solo deriva a la página principal de cada rol (el panel para
 * ADMIN, el área personal para USUARIO) y, sin sesión válida, al login.
 */
require_once __DIR__ . '/includes/auth.php';

redirigirSegunRol();
