<?php
/**
 * Copyright REZO ZERO 2014
 *
 *
 *
 *
 * @file ApacheHost.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Deployer\VHosts;

use rezozero\Deployer\Controllers\Kernel;
use rezozero\Deployer\VHosts\AbstractHostFile;

class ApacheHost extends AbstractHostFile
{
	public function generateVirtualHost(){

		$mainConf = Kernel::getInstance()->getConfiguration()->getData();
		$hostname = Kernel::getInstance()->getConfiguration()->getHostname();

		if (is_writable($mainConf['vhosts_path']) &&
			!file_exists($this->vhostFile)) {

			$vars = array(
				'datetime'=>	  time(),
				'mpm_itk'=>       (boolean)$mainConf['mpm_itk'],
				'phpfpm_enabled'=>(boolean)$mainConf['phpfpm_enabled'], // You can choose a regular apache config
				'use_index_entrypoint' => (boolean)$mainConf['use_index_entrypoint'],
				'email'=>      	  $mainConf['notification_email'],
				'vhost_root'=>    (!empty($mainConf['vhost_root']) ? $mainConf['vhost_root'] : 'htdocs'),
				'username'=>      Kernel::getInstance()->getUser()->getName(),
				'rootPath'=>      Kernel::getInstance()->getUser()->getHome(),
				'hostname'=>      $hostname,
				'rzcms_install'=> (boolean)$mainConf['use_rzcms'],
				'use_rzcms'=> (boolean)$mainConf['use_rzcms'],
				'use_roadiz'=>    (boolean)$mainConf['use_roadiz']
			);

			if (is_array($mainConf['notification_email']) &&
				!empty($mainConf['notification_email'][0])) {
				$vars['email'] = $mainConf['notification_email'][0];
			}

			return $this->generateFile($this->vhostFile, 'apache2.example.conf', $vars);
		}

		return false;
	}
}
