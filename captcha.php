<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
iniciarSesionSegura();

if (!isset($_SESSION['captcha_code']) || !is_string($_SESSION['captcha_code']) || $_SESSION['captcha_code'] === '') {
    http_response_code(404);
    exit;
}

// SVG generado directamente por PHP: no requiere la extensión GD de PHP.
$code = $_SESSION['captcha_code'];
$width = 180;
$height = 60;
$escapedCode = htmlspecialchars($code, ENT_QUOTES | ENT_XML1, 'UTF-8');

header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<svg xmlns="http://www.w3.org/2000/svg" width="<?= $width ?>" height="<?= $height ?>" viewBox="0 0 <?= $width ?> <?= $height ?>" role="img" aria-label="Código CAPTCHA">
  <rect width="180" height="60" rx="6" fill="#f5f5f5"/>
  <g stroke="#969696" stroke-width="1" opacity="0.65">
    <path d="M5 12 L170 48 M15 55 L165 5 M0 35 L180 20 M40 0 L120 60"/>
  </g>
  <g fill="#232323" font-family="monospace" font-size="25" font-weight="700" letter-spacing="3">
    <text x="90" y="39" text-anchor="middle"><?= $escapedCode ?></text>
  </g>
  <rect x="0.5" y="0.5" width="179" height="59" rx="5.5" fill="none" stroke="#c7c7c7"/>
</svg>
