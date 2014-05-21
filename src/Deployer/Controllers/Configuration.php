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

class Configuration 
{
	private $config;
	private $hostname;
	private $username;
	private $password;
	private $notifyEmail;

	public static $serverAvailable = array(
		'apache2',
		'nginx'
	);

	public function __construct(){
		
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

		while (empty($line) || strpos($line, '/')) {
			echo "Choose a valid server name (hostname): ".PHP_EOL."> ";
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

		while (empty($line)) {
			echo "Choose an username: ".PHP_EOL."> ";
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
			echo "[ERROR] Your must configure a MySQL server host.".PHP_EOL;
			return false;
		}
		if (empty($this->config['mysql_user'])) {
			echo "[ERROR] Your must configure a MySQL super-user.".PHP_EOL;
			return false;
		}
		if (empty($this->config['mysql_password'])) {
			echo "[ERROR] Your must configure the MySQL super-user password.".PHP_EOL;
			return false;
		}
		if (empty($this->config['webserver_root']) || 
			!is_writable($this->config['webserver_root'])) {
			
			echo "[ERROR] Your webserver root path is not writable.".PHP_EOL;
			return false;
		}
		if (empty($this->config['webserver_root']) || 
			!is_writable($this->config['webserver_root'])) {
			
			echo "[ERROR] Your webserver root path is not writable.".PHP_EOL;
			return false;
		}
		if (empty($this->config['vhosts_path']) || 
			!is_writable($this->config['vhosts_path'])) {
			
			echo "[ERROR] Your virtual hosts path is not writable.".PHP_EOL;
			return false;
		}
		if (empty($this->config['vhosts_enabled_path']) || 
			!is_writable($this->config['vhosts_enabled_path'])) {
			
			echo "[ERROR] Your enabled virtual hosts path is not writable.".PHP_EOL;
			return false;
		}
		if (empty($this->config['phpfpm_pools_path']) || 
			!is_writable($this->config['phpfpm_pools_path'])) {
			
			echo "[ERROR] Your PHP FPM pools path is not writable.".PHP_EOL;
			return false;
		}
		if (empty($this->config['webserver_type']) || 
			!in_array($this->config['webserver_type'], static::$serverAvailable)) {
			
			echo "[ERROR] Your server type is not available, you must choose between [".implode(", ", static::$serverAvailable)."].".PHP_EOL;
			return false;
		}
		if (empty($this->config['notification_email']) || 
			filter_var($this->config['notification_email'], FILTER_VALIDATE_EMAIL) === false) {
			
			echo "[ERROR] You must use a valid notification email.".PHP_EOL;
			return false;
		}


		return true;
	}
}
