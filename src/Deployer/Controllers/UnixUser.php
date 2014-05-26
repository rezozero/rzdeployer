<?php 
/**
 * Copyright REZO ZERO 2014
 * 
 * 
 * 
 *
 * @file UnixUser.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Deployer\Controllers;

use rezozero\Deployer\Controllers\Kernel;
use rezozero\Deployer\Controllers\Password;

class UnixUser {

	private $username;
	private $password;
	private $userGroup;
	private $homeFolder;

	public function __construct( $username ){
		$mainConf = Kernel::getInstance()->getConfiguration()->getData();

		$this->username =   $username;
		$this->password =   Password::generate(8);
		$this->homeFolder = $mainConf["webserver_root"]."/".Kernel::getInstance()->getConfiguration()->getHostname();
	}

	public function createUser()
	{
		$mainConf = Kernel::getInstance()->getConfiguration()->getData();
		$hostname = Kernel::getInstance()->getConfiguration()->getHostname();

		$results = null;		

		/*
		 * Additionnal groups
		 */
		$groups = array();
		if (!empty($mainConf["webserver_group"])) {
			$groups[] = $mainConf["webserver_group"];
		}
		if (!empty($mainConf["allowssh_group"])) {
			$groups[] = $mainConf["allowssh_group"];
		}

		/*
		 * Create user without password
		 */
		$query = "useradd --home ".$this->homeFolder." -m";
		//$query .= " -p ".$this->password;
		if (count($groups) > 0) {
			$query .= " -G ".implode(',', $groups);
		}
		$query .= " -s /bin/bash";
		$query .= " ".$this->username;
		//var_dump($query);

		exec($query, $results);
		if (count($results) > 0) {
			var_dump($results);
			echo "[ERROR] Unable to create unix user.".PHP_EOL;
			return false;
		}

		/*
		 * Add password
		 */
		$pwQuery = 'usermod -p '.Password::encrypt($this->password).' '.$this->username;
		//var_dump($pwQuery);
		exec($pwQuery, $pwResults);
		if (count($pwResults) > 0) {
			var_dump($pwResults);
			echo "[ERROR] Unable to set unix user password.".PHP_EOL;
			return false;
		}

		return true;
	}

	public function getHome()
	{
		return $this->homeFolder;
	}
	public function getName()
	{
		return $this->username;
	}
	public function getPassword()
	{
		return $this->password;
	}

	public function userExists()
	{
		return file_exists($this->homeFolder);
	}

	public function createFileStructure()
	{	
		$mainConf = Kernel::getInstance()->getConfiguration()->getData();

		if (chdir($this->homeFolder) !== false) {

			/*
			 * Change user home mod to 750 and writable by www-data
			 */
			chown($this->homeFolder, $mainConf['webserver_group']);
			chgrp($this->homeFolder, $this->username);
			chmod($this->homeFolder, 0750);
			
			/*
			 * Create the php-fpm socket
			 */
			touch($this->homeFolder."/php5-fpm.sock");
			chown($this->homeFolder."/php5-fpm.sock", "root");
			chgrp($this->homeFolder."/php5-fpm.sock", "root");
			chmod($this->homeFolder."/php5-fpm.sock", 0666);

			// Create special log folder 
			$this->createFolder($this->homeFolder."/log");
			chown($this->homeFolder."/log", $this->username);
			chgrp($this->homeFolder."/log", "root");
			chmod($this->homeFolder."/log", 0770);

			// Create user folders
			$this->createFolder($this->homeFolder."/htdocs");
			$this->createFolder($this->homeFolder."/private");
			$this->createFolder($this->homeFolder."/private/git");
			$this->createFolder($this->homeFolder."/private/backup");

			/*
			 * SSH folder must be only read/writeable by user
			 */
			$this->createFolder($this->homeFolder."/.ssh");
			chmod($this->homeFolder."/.ssh", 0700);

			// Create test file
			file_put_contents($this->homeFolder."/htdocs/index.php", "<?php phpinfo(); ?>");
			chown($this->homeFolder."/htdocs/index.php", $this->username);
			chgrp($this->homeFolder."/htdocs/index.php", $this->username);
			chmod($this->homeFolder."/htdocs/index.php", 0644);

			return true;
		}
		return false;
	}

	public function createFile( $path )
	{
		touch($path);
		chown($path, $this->username);
		chgrp($path, $this->username);
		chmod($path, 0644);
	}
	public function createFolder( $path )
	{
		mkdir($path, 0755, true );
		chown($path, $this->username);
		chgrp($path, $this->username);
	}
}