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

use rezozero\Deployer\Controllers\Kernel;

$user = trim(shell_exec('whoami'));
if ($user != 'root') {
	echo "[".$user."] — You must have super-user privileges to go on.".PHP_EOL;
}
else {
	if( php_sapi_name() === 'cli' || 
		defined('STDIN') ) {
		Kernel::getInstance()->run();
	}
	else {
		echo "[".$user."] — You must invoke RZDeployer from commandline only.".PHP_EOL;
	}
}
