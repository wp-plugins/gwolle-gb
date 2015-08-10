<?php

/*
 * Handles AJAX request from Gwolle-GB Captcha AJAX check.
 * Expects that the plugin ReallySimple Captcha is enabled.
 *
 * Uses GET variables for input data.
 *
 * Returns true or false, if the CAPTCHA is filled in correctly.
 */

// This variable holds the ABSPATH
$gwolle_gb_abspath = ( isset( $_GET['abspath'] ) ? urldecode( $_GET['abspath'] ) : false );

require( $gwolle_gb_abspath . 'wp-load.php' );

// Instantiate class
$gwolle_gb_captcha = new ReallySimpleCaptcha();

// This variable holds the CAPTCHA image prefix, which corresponds to the correct answer
$gwolle_gb_captcha_prefix = ( isset( $_GET['prefix'] ) ? $_GET['prefix'] : false );

// This variable holds the CAPTCHA response, entered by the user
$gwolle_gb_captcha_code = ( isset( $_GET['code'] ) ? $_GET['code'] : false );

// This variable will hold the result of the CAPTCHA validation. Set to 'false' until CAPTCHA validation passes
$gwolle_gb_captcha_correct = ( $gwolle_gb_captcha->check( $gwolle_gb_captcha_prefix, $gwolle_gb_captcha_code ) ? 'true' : 'false' );

// Return response
echo $gwolle_gb_captcha_correct;

