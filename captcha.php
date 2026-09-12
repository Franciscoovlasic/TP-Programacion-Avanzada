<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['captcha_code']) || !is_string($_SESSION['captcha_code']) || $_SESSION['captcha_code'] === '') {
    http_response_code(404);
    exit;
}

if (!function_exists('imagecreatetruecolor')) {
    http_response_code(503);
    exit;
}

$code = $_SESSION['captcha_code'];
$width = 180;
$height = 60;
$image = imagecreatetruecolor($width, $height);
$background = imagecolorallocate($image, 245, 245, 245);
$textColor = imagecolorallocate($image, 35, 35, 35);
$noiseColor = imagecolorallocate($image, 150, 150, 150);

imagefill($image, 0, 0, $background);

for ($i = 0; $i < 40; $i++) {
    imageline(
        $image,
        random_int(0, $width - 1),
        random_int(0, $height - 1),
        random_int(0, $width - 1),
        random_int(0, $height - 1),
        $noiseColor
    );
}

for ($i = 0; $i < 250; $i++) {
    imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $noiseColor);
}

$font = 5;
$textWidth = imagefontwidth($font) * strlen($code);
$textHeight = imagefontheight($font);
$textX = (int) (($width - $textWidth) / 2);
$textY = (int) (($height - $textHeight) / 2);
imagestring($image, $font, $textX, $textY, $code, $textColor);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
