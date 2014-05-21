<?php 
/**
 * Copyright REZO ZERO 2014
 * 
 * 
 * 
 * 
 *
 * @file app.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
define('APP_ROOT', dirname(__FILE__));

require("vendor/autoload.php");

use rezozero\Controllers\Kernel;

ini_set('display_errors', 1);
error_reporting(E_ALL);


$user = trim(shell_exec('whoami'));
if ($user != 'root') {
	echo "[".$user."] — You must have super-user privileges to go on.".PHP_EOL;
	exit;
}

if( php_sapi_name() === 'cli' OR defined('STDIN') ) {
	Kernel::getInstance()->run();
}
else {
	echo "[".$user."] — You must invoke RZDeployer from commandline only.".PHP_EOL;
	exit;
}