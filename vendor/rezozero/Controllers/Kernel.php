<?php 


namespace rezozero\Controllers;

use rezozero\Controllers\Configuration;
/**
* 
*/
class Kernel 
{
	private static $instance = null;

	private $configuration;

	private function __construct(){
		
		$this->configuration = new Configuration();
		if ($this->configuration->setConfigFile( APP_ROOT.'/conf/config.json' ) !== false) {
			if ($this->configuration->verifyConfiguration()) {
				$this->configuration->requestHostname();
				$this->configuration->requestUsername();
			}
		}
		else {
			echo "Unable to load configuration file (".APP_ROOT."/conf/config.json).".PHP_EOL;
			exit;
		}
	}

	public static function getInstance()
	{
		if (static::$instance === null) {
			static::$instance = new Kernel();
		}

		return static::$instance;
	}
}