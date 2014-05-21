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
namespace rezozero\Controllers;

use rezozero\Controllers\Kernel;
use rezozero\Controllers\Password;

class UnixUser {

	private $username;
	private $password;
	private $userGroup;
	private $homeFolder;

	public function __construct( $username ){
		$this->username = $username;
		$this->password = Password::generate(8);
	}

	public function createUser()
	{
		$mainConf = Kernel::getInstance()->getConfiguration()->getData();
		$hostname = Kernel::getInstance()->getConfiguration()->getHostname();

		$results = null;		
		$this->homeFolder = $mainConf["webserver_root"]."/".$hostname;

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
	public function getUser()
	{
		return $this->username;
	}
	public function getPassword()
	{
		return $this->password;
	}

	public function createFileStructure()
	{	
		$mainConf = Kernel::getInstance()->getConfiguration()->getData();

		if (chdir($this->homeFolder) !== false) {

			chown($this->homeFolder, $mainConf['webserver_group']);
			chgrp($this->homeFolder, $this->username);
			
			touch($this->homeFolder."/php5-fpm.sock");
			chmod($this->homeFolder."/php5-fpm.sock", 0666);

			$this->createFolder($this->homeFolder."/htdocs");
			$this->createFolder($this->homeFolder."/private");
			$this->createFolder($this->homeFolder."/private/git");
			$this->createFolder($this->homeFolder."/private/backup");
			$this->createFolder($this->homeFolder."/.ssh");

			chmod($this->homeFolder."/.ssh", 0700);

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