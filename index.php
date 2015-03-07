<?php
/**
* AUTOLOAD
*/
require('vendor/autoload.php');

$f3 = Base::instance();

/**
* CONFIGURATIONS
*/
// Globals
$f3->config('app/config/globals.ini');
// Routing
$f3->config('app/config/routes.ini');
// Databases
$f3->config('app/config/db.ini');

/*
* ERRORS
*/
$f3->set('ONERROR', function($f3){
	// $error = $f3->get('ERROR');
	\app\helpers\API::error(404, 'Something wrong happened...');
});


/**
* WHERE THE MAGIC HAPPENS WITH FAT FREE
*/
$f3->run();
?>