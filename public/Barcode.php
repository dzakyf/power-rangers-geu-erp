<?php
function loadbarcode($cn){
require('barcode/class/BCGFont.php');
require('barcode/class/BCGColor.php');
require('barcode/class/BCGDrawing.php'); 

include('barcode/class/BCGcode128.barcode.php'); 

$font = new BCGFont('barcode/class/font/Arial.ttf', 12);

$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255); 

$code = new BCGcode128();
$code->setScale(1); // Resolution
$code->setThickness(60); // Thickness
$code->setForegroundColor($color_black); // Color of bars
$code->setBackgroundColor($color_white); // Color of spaces
$code->setFont(0); // Font (or 0)
$code->parse($cn); // Text
$drawing = new BCGDrawing('', $color_white);
$drawing->setBarcode($code);
$drawing->draw();

header('Content-Type: image/png');

$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
	
	
}
loadbarcode($_GET['id']);

?>