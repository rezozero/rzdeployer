<?php 
/**
 * Copyright REZO ZERO 2014
 * 
 * 
 * 
 *
 * @file NginxHost.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Deployer\VHosts;

use rezozero\Deployer\Controllers\Kernel;
use rezozero\Deployer\VHosts\AbstractHostFile;

class NginxHost extends AbstractHostFile
{
	public function generateVirtualHost(){

		$mainConf = Kernel::getInstance()->getConfiguration()->getData();
		$hostname = Kernel::getInstance()->getConfiguration()->getHostname();

		$this->vhostFile = $mainConf['vhosts_path']."/".$hostname;

		if (is_writable($mainConf['vhosts_path']) && 
			!file_exists($this->vhostFile)) {
			
			$vars = array(
				'rootPath'=>      Kernel::getInstance()->getUser()->getHome(),
				'hostname'=>      $hostname,
				'rzcms_install'=> (boolean)$mainConf['use_rzcms']
			);

			return $this->generateFile($this->vhostFile, 'nginx.example.com', $vars);
		}

		return false;
	}
	public function generatePHPPool(){

		$mainConf = Kernel::getInstance()->getConfiguration()->getData();
		$hostname = Kernel::getInstance()->getConfiguration()->getHostname();

		$this->poolFile = $mainConf['phpfpm_pools_path']."/".Kernel::getInstance()->getUser()->getName().".conf";

		if (is_writable($mainConf['phpfpm_pools_path']) && 
			!file_exists($this->poolFile)) {
			
			$vars = array(
				'username'=>        Kernel::getInstance()->getUser()->getName(),
				'webserver_group'=> $mainConf['webserver_group'],
				'rootPath'=>        Kernel::getInstance()->getUser()->getHome(),
				'hostname'=>        $hostname,
				'rzcms_install'=>   (boolean)$mainConf['use_rzcms']
			);

			return $this->generateFile($this->poolFile, 'pool.example.conf', $vars);
		}

		return false;
	}
}