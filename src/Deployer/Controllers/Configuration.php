<?php
/**
 * Copyright REZO ZERO 2014
 *
 *
 *
 *
 * @file Configuration.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Deployer\Controllers;


use rezozero\Deployer\Controllers\Kernel;

class Configuration
{
	private $config;
	private $hostname;
	private $username;

	public static $serverAvailable = array(
		'apache2',
		'nginx'
	);

	public function __construct(){

	}

	/**
	 *
	 * @return string Class Name
	 */
	public function getVHostClass()
	{
		switch ($this->config['webserver_type']) {
			case 'apache2':

				return "rezozero\Deployer\VHosts\ApacheHost";
				break;
			case 'nginx':

				return "rezozero\Deployer\VHosts\NginxHost";
				break;
		}

		return false;
	}

	public function setConfigFile( $file )
	{
		if (is_readable( $file )) {

			$this->config = json_decode(file_get_contents($file), true);

			if (is_array($this->config)) {
				return true;
			}
			else {
				echo $file. " file is not a valid JSON file.".PHP_EOL;
				return false;
			}
		}
		else {
			echo $file. " file is not readable.".PHP_EOL;
			return false;
		}
	}

	public function getData()
	{
		return $this->config;
	}

	public function requestHostname()
	{
		$line = '';

		while (empty($line) || strpos($line, '/') || strpos($line, ' ')) {


			echo Kernel::getInstance()->getColor()->getColoredString("Choose a valid server name (hostname): ", null, 'green').PHP_EOL."> ";
			$line = trim(fgets(STDIN));
			// securize input
			$line = str_replace("|", "", $line);
			$line = str_replace("&", "", $line);
		}
		$this->hostname = $line;

	}
	public function getHostname()
	{
		return $this->hostname;
	}
	public function requestUsername()
	{
		$line = '';

		while (empty($line) || strpos($line, ' ')) {
			echo Kernel::getInstance()->getColor()->getColoredString("Choose an username: ", null, 'green').PHP_EOL."> ";
			$line = trim(fgets(STDIN));
			// securize input
			$line = str_replace("|", "", $line);
			$line = str_replace("&", "", $line);
		}
		$this->username = $line;
	}
	public function getUsername()
	{
		return $this->username;
	}

	public function verifyConfiguration()
	{
		if (empty($this->config['mysql_host'])) {
			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your must configure a MySQL server host.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['mysql_user'])) {
			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your must configure a MySQL super-user.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['mysql_password'])) {
			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your must configure the MySQL super-user password.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['webserver_root']) ||
			!is_writable($this->config['webserver_root'])) {

			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your webserver root path is not writable.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['webserver_root']) ||
			!is_writable($this->config['webserver_root'])) {

			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your webserver root path is not writable.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['vhosts_path']) ||
			!is_writable($this->config['vhosts_path'])) {

			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your virtual hosts path is not writable.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['vhosts_enabled_path']) ||
			!is_writable($this->config['vhosts_enabled_path'])) {

			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your enabled virtual hosts path is not writable.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['phpfpm_pools_path']) ||
			!is_writable($this->config['phpfpm_pools_path'])) {

			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your PHP FPM pools path is not writable.", 'red', null).PHP_EOL;
			return false;
		}
		if (empty($this->config['webserver_type']) ||
			!in_array($this->config['webserver_type'], static::$serverAvailable)) {

			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] Your server type is not available, you must choose between [".implode(", ", static::$serverAvailable)."].", 'red', null).PHP_EOL;
			return false;
		}

		if (!empty($this->config['notification_email'])) {

			if (is_array($this->config['notification_email'])) {
				foreach ($this->config['notification_email'] as $email) {
					if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
						echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] “".$email."” is not a valid notification email.", 'red', null).PHP_EOL;
						return false;
					}
				}
			} elseif (filter_var($this->config['notification_email'], FILTER_VALIDATE_EMAIL) === false) {

				echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] “".$this->config['notification_email']."” is not a valid notification email.", 'red', null).PHP_EOL;
				return false;
			}
		} else {
			echo Kernel::getInstance()->getColor()->getColoredString("[ERROR] You must provide a notification email.", 'red', null).PHP_EOL;
			return false;
		}


		return true;
	}
}
