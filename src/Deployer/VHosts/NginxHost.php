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

		if (is_writable($mainConf['vhosts_path']) &&
			!file_exists($this->vhostFile)) {

			$vars = array(
				'datetime'=>	  time(),
				'username'=>      Kernel::getInstance()->getUser()->getName(),
				'rootPath'=>      Kernel::getInstance()->getUser()->getHome(),
				'hostname'=>      $hostname,
				'vhost_root'=>    (!empty($mainConf['vhost_root']) ? $mainConf['vhost_root'] : 'htdocs'),
				'rzcms_install'=> (boolean)$mainConf['use_rzcms'],
				'use_rzcms'=>     (boolean)$mainConf['use_rzcms'],
				'use_roadiz'=>    (boolean)$mainConf['use_roadiz'],
				'phpmyadmin_install'=> (boolean)$mainConf['phpmyadmin_install']
			);

			return $this->generateFile($this->vhostFile, 'nginx.example.conf', $vars);
		}

		return false;
	}
}
