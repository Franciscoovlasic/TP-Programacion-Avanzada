<?php
require_once __DIR__ . '/../../app/bootstrap.php';

(new App\Controllers\RutinaController())->historialUsuario($_GET['id_usuario'] ?? null);
