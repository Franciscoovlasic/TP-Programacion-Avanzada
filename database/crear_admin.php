<?php
declare(strict_types=1);

/**
 * Crea la cuenta maestra de FORJA GYM una sola vez.
 * Ejecutar desde XAMPP Shell/terminal, nunca desde el navegador:
 * php database/crear_admin.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('No encontrado.');
}

require_once dirname(__DIR__) . '/includes/conexion.php';

const ADMIN_DNI = '99999999';
const ADMIN_EMAIL = 'admin@forjagym.local';
const ADMIN_PASSWORD = 'ForjaAdmin2026!';

try {
    $db = conectarBaseDeDatos();
    $stmt = $db->prepare('SELECT id_usuario, rol FROM usuario WHERE dni = ? OR email = ? LIMIT 1');
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la consulta de verificación.');
    }
    $stmt->bind_param('ss', $dni, $email);
    $dni = ADMIN_DNI;
    $email = ADMIN_EMAIL;
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($existing !== null) {
        if (($existing['rol'] ?? '') === 'ADMIN') {
            echo "La cuenta maestra ya existe.\nDNI: " . ADMIN_DNI . "\n";
            echo "Si no cambiaste la contraseña, la contraseña inicial es: " . ADMIN_PASSWORD . "\n";
        } else {
            fwrite(STDERR, "ERROR: el DNI o email reservado ya pertenece a una cuenta USUARIO. No se modificó ni promovió esa cuenta.\n");
            exit(1);
        }
        exit(0);
    }

    $hash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $rol = 'ADMIN';
    $nombre = 'Administrador FORJA GYM';
    $insert = $db->prepare('INSERT INTO usuario (nombre, email, dni, contrasenia, rol) VALUES (?, ?, ?, ?, ?)');
    if (!$insert) {
        throw new RuntimeException('No se pudo preparar la creación de la cuenta maestra.');
    }
    $insert->bind_param('sssss', $nombre, $email, $dni, $hash, $rol);
    $dni = ADMIN_DNI;
    $email = ADMIN_EMAIL;
    if (!$insert->execute()) {
        throw new RuntimeException('MySQL no pudo crear la cuenta maestra: ' . $insert->error);
    }
    $insert->close();
    $db->close();

    echo "Cuenta maestra creada correctamente.\n";
    echo "DNI: " . ADMIN_DNI . "\n";
    echo "Contraseña: " . ADMIN_PASSWORD . "\n";
    echo "Email: " . ADMIN_EMAIL . "\n";
    echo "Iniciá sesión en FORJA GYM y luego eliminá/archivá este script si ya no lo necesitás.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(1);
}
