<?php 


namespace rezozero\Deployer\Controllers;

use rezozero\Deployer\Controllers\Configuration;
use rezozero\Deployer\Controllers\UnixUser;
use rezozero\Deployer\Controllers\Database;
use rezozero\Deployer\Controllers\Password;
/**
* 
*/
class Kernel 
{
	private static $instance = null;

	private $configuration;
	private $unixUser;
	private $database;

	private function __construct(){
		
		$this->configuration = new Configuration();
	}

	public function run()
	{
		if ($this->configuration->setConfigFile( APP_ROOT.'/conf/config.json' ) !== false) {
			if ($this->configuration->verifyConfiguration()) {

				$this->configuration->requestHostname();
				$this->configuration->requestUsername();

				$this->unixUser = new UnixUser( $this->configuration->getUsername() );
				if ($this->unixUser->createUser() === false) {
					echo "[ERROR] Unable to create unix user.".PHP_EOL;
					exit;
				}
				$this->unixUser->createFileStructure();

				$this->database = new Database( $this->configuration->getUsername() );
				if ($this->database->createUserDatabase() === false) {
					echo "[ERROR] Unable to create mysql database.".PHP_EOL;
				}


				echo PHP_EOL.$this->getSummary();

				$this->mailSummary();
				return true;
			}
		}
		else {
			echo "[ERROR] Unable to load configuration file (".APP_ROOT."/conf/config.json).".PHP_EOL;
			exit;
		}
	}

	public function getConfiguration()
	{
		return $this->configuration;
	}

	public function getSummary()
	{
		$msg = "";
		$msg .= "=========================================".PHP_EOL;
		$msg .= "================= RZ Deployer =============".PHP_EOL;
		$msg .= "=========================================".PHP_EOL.PHP_EOL;


		$msg .= $this->configuration->getHostname()." is now deployed on your webserver.".PHP_EOL.PHP_EOL;
		$msg .= "Hostname: ".$this->configuration->getHostname().PHP_EOL;

		$msg .= "SSH user: ".$this->unixUser->getUser().PHP_EOL;
		$msg .= "SSH password: ".$this->unixUser->getPassword().PHP_EOL;
		$msg .= "SSH path: ".$this->unixUser->getHome().PHP_EOL.PHP_EOL;

		$msg .= "MySQL database: ".$this->database->getDatabase().PHP_EOL;
		$msg .= "MySQL user: ".$this->database->getUser().PHP_EOL;
		$msg .= "MySQL password: ".$this->database->getPassword().PHP_EOL.PHP_EOL;

		$msg .= "=========================================".PHP_EOL;

		return $msg;
	}

	public function mailSummary()
	{	
		$confData = $this->configuration->getData();

		if (filter_var($confData['sender_email'], FILTER_VALIDATE_EMAIL) !== false && 
			filter_var($confData['notification_email'], FILTER_VALIDATE_EMAIL) !== false) {

			$Name = "RZ Deployer"; //senders name 
			$email = $confData['sender_email']; //senders e-mail adress 
			$recipient = $confData['notification_email']; //recipient 
			$mail_body = $this->getSummary(); //mail body 
			$subject = $this->configuration->getHostname()." is now deployed"; //subject 
			$header = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields 

			mail($recipient, $subject, $mail_body, $header);
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