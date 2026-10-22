<?php
include __DIR__ . '/../../../includes/header.php';

$nombreUsuario = nombreCompleto($usuario['nombre'] ?? '', $usuario['apellido'] ?? '');
$fechaRegistro = !empty($usuario['fecha_registro'])
    ? date('d/m/Y H:i', strtotime((string) $usuario['fecha_registro']))
    : '—';
?>
<main class="hero hero-result"><section class="login-card">
    <h1 class="login-title">Datos del usuario</h1>
    <p class="resultado-texto"><strong>Nombre:</strong> <?= e($nombreUsuario) ?></p>
    <p class="resultado-texto"><strong>Email:</strong> <?= e($usuario['email'] ?? '') ?></p>
    <p class="resultado-texto"><strong>DNI:</strong> <?= e($usuario['dni'] ?? '') ?></p>
    <p class="resultado-texto"><strong>Rol:</strong> <?= e($usuario['rol'] ?? '') ?></p>
    <p class="resultado-texto"><strong>Fecha de registro:</strong> <?= e($fechaRegistro) ?></p>
    <div class="perfil-actions">
        <a class="btn-secundario" href="<?= e($rutaBase) ?>admin/usuarios/editar.php?id=<?= (int) $usuario['id_usuario'] ?>">Editar</a>
        <a class="btn-registro" href="<?= e($rutaBase) ?>admin/index.php">Volver al panel</a>
    </div>
</section></main>
<?php include __DIR__ . '/../../../includes/footer.php'; ?>
