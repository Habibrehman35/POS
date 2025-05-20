<?php
require 'barcode128.class.php';

$code = $_GET['code'] ?? '';
if (!$code) {
    http_response_code(400);
    exit('Invalid barcode.');
}

$barcode = new Barcode128();
$image = $barcode->draw($code);

// Output image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
