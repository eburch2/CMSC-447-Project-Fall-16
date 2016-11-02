<?php
session_start();
header('Content-type: image/png');

function displayImage() {
	$im = imagecreate(240, 50);
	$bg = imagecolorallocate($im, 0, 0, 0);
	$textcolor = imagecolorallocate($im, 255, 255, 255);
	$value = "";
	$value1 = $_SESSION["umbcbazaar_captcha"];
	for($i=0; $i < strlen($value1); $i++) {
		$value .= " " . $value1[$i];
	}
	imagestring($im, 10, 15, 15, $value, $textcolor);
	imagepng($im);
	imagedestroy($im);
}
function generateCaptcha() {
	$captchaLength = 10;
	$options = "ABCDEFGHJKLMNPQRTUVWXYZabcdefghjkmnpqrstuvwxyz2346789@#?$=";
	$value = "";
	for($i=0; $i < $captchaLength; $i++) {
		$value .= $options[rand(0, strlen($options) - 1)];
	}
	$_SESSION["umbcbazaar_captcha"] = $value;
}
generateCaptcha();
displayImage();

?>