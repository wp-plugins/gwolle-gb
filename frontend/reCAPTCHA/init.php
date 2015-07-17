<?php

function gwolle_gb_recaptcha_init($recaptcha_privateKey) {
	// Taken from https://github.com/google/recaptcha
	require('autoload.php');

	$recaptcha = new \ReCaptcha\ReCaptcha($recaptcha_privateKey);
	$resp = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);
	return $resp;
}
