<?php 
/**
 * Copyright REZO ZERO 2014
 * 
 * 
 * 
 *
 * @file Database.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Deployer\Controllers;

use rezozero\Deployer\Controllers\Kernel;
use rezozero\Deployer\Controllers\Password;

class Database {

	private $dbUsername;
	private $dbPassword;

	public function __construct( $username ) {
		$this->dbUsername = substr($username, 0, 15);
		$this->dbPassword = Password::generate(8);
	}

	public function createUserDatabase()
	{
		$mainConf = Kernel::getInstance()->getConfiguration()->getData();


		try {

			$dsn = $dsn = "mysql:host=".$mainConf['mysql_host'];
	    	$pdo = new \PDO($dsn,$mainConf['mysql_user'],$mainConf['mysql_password']);

	    	//Creation of user "user_name"
	    	$pdo->query("CREATE USER '".$this->dbUsername."'@'".$mainConf['mysql_host']."' IDENTIFIED BY '".$this->dbPassword."';");
	    	//Creation of database "new_db"
	    	$pdo->query("CREATE DATABASE `".$this->dbUsername."`;");
	    	//Adding all privileges on our newly created database
	    	$pdo->query("GRANT ALL PRIVILEGES on `".$this->dbUsername."`.* TO '".$this->dbUsername."'@'".$mainConf['mysql_host']."';");

	    	return true;
		}
		catch(\PDOException $e){

			echo "[ERROR] ".$e->getMessage().PHP_EOL;
			return false;
		}
	}

	public function getDatabase()
	{
		return $this->dbUsername;
	}
	public function getUser()
	{
		return $this->dbUsername;
	}
	public function getPassword()
	{
		return $this->dbPassword;
	}
}