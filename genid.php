<?php
if(!isset($_GET['token'])) badness(-1, 'No token');
define('HVZ', 1);
define('NOSETUP', 1);
require_once("settings.php");
require_once("includes/RIB64.php");
require_once("includes/string.php");
require_once("phpqrcode/phpqrcode.php");
set_error_handler('badness');
$tokenbits = explode('|', decodeString(rawurldecode($_GET['token'])));
if(count($tokenbits) != 5) badness(-1, 'Wrong tokenbit count');
if($tokenbits[4] != md5($secretsalt . $tokenbits[0] . $tokenbits[1] . $tokenbits[2] . $tokenbits[3])) badness(-1, 'Wrong hash');
//figure out year
$month = date('n');
$year = date('Y');
if($month < 7) {
    $year--;
}
$y1 = implode('  ', str_split($year));
$y2 = implode('  ', str_split($year+1));
$pn = implode(' ', str_split(str_pad($tokenbits[3] % 10000, 4, '0', STR_PAD_LEFT)));
$im = imagecreatefromjpeg('images/id2.jpg');
$black = imagecolorallocate($im, 0, 0, 0);
$qr = QRCode::png('https://tamuhvz.com/?page=main&tab=reportkill&victimid=' . $tokenbits[1], false, QR_ECLEVEL_L, 3.5, 0);
imagefttext($im, 14, 0, 50, 315, $black, 'fonts/arial.ttf', $tokenbits[0]);
imagefttext($im, 14, 0, 395, 315, $black, 'fonts/arial.ttf', $tokenbits[1]);
imagefttext($im, 14, 0, 75, 335, $black, 'fonts/arial.ttf', $tokenbits[2]);
imagefttext($im, 20, 0, 45, 155, $black, 'fonts/arial.ttf', $y1);
imagefttext($im, 20, 0, 45, 185, $black, 'fonts/arial.ttf', $y2);
imagefttext($im, 28, 0, 385, 125, $black, 'fonts/ClvATT-Bold.otf', $pn);
imagefttext($im, 8, 0, 410, 335, $black, 'fonts/arial.ttf', "Printed " . date('n/j/Y g:i a'));
imagecopy($im, $qr, 390, 150, 0, 0, imagesx($qr), imagesy($qr));
header('Content-type: image/jpeg');
imagejpeg($im, null, 100);
imagedestroy($im);
imagedestroy($qr);

function badness($errno, $errstr) {
    echo $errstr;
    die($errno);
}
