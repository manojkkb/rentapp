<?php

$dir = dirname(__DIR__) . '/public/vendor/icons';
if (! is_dir($dir)) {
    mkdir($dir, 0775, true);
}

$make = function (int $size, string $path): void {
    $s = 64;
    $img = imagecreatetruecolor($s, $s);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefilledrectangle($img, 0, 0, $s, $s, $trans);
    imagealphablending($img, true);
    $bg = imagecolorallocate($img, 5, 150, 105);
    $fg = imagecolorallocate($img, 255, 255, 255);
    $pad = 4;
    imagefilledrectangle($img, $pad, $pad, $s - $pad, $s - $pad, $bg);
    imagestring($img, 5, 24, 24, 'R', $fg);
    $out = imagescale($img, $size, $size, IMG_BICUBIC);
    imagepng($out, $path, 6);
    imagedestroy($img);
    imagedestroy($out);
};

$make(192, $dir . '/icon-192.png');
$make(512, $dir . '/icon-512.png');

echo "Icons written to {$dir}\n";
