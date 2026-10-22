<?php
require_once __DIR__ . '/../../app/bootstrap.php';

(new App\Controllers\UsuarioController())->editar($_GET['id'] ?? null);
