<?php
require_once __DIR__ . '/../../app/bootstrap.php';

(new App\Controllers\UsuarioController())->eliminar($_POST['id_usuario'] ?? null);
