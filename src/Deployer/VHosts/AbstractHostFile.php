<?php
/**
 * Copyright REZO ZERO 2014
 *
 *
 *
 *
 * @file AbstractHostFile.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Deployer\VHosts;

use rezozero\Deployer\Controllers\Kernel;

abstract class AbstractHostFile
{
    protected $vhostFile;
    protected $poolFile;

    public abstract function generateVirtualHost();

    public function enableVirtualHost()
    {
        $mainConf = Kernel::getInstance()->getConfiguration()->getData();

        if (file_exists($this->vhostFile) &&
            is_writable($mainConf['vhosts_enabled_path'])) {

            return symlink( $this->vhostFile , $mainConf['vhosts_enabled_path'].'/'.Kernel::getInstance()->getUser()->getName().".conf" );
        }

        return false;
    }

    public function generatePHPPool(){

        $mainConf = Kernel::getInstance()->getConfiguration()->getData();
        $hostname = Kernel::getInstance()->getConfiguration()->getHostname();

        if (is_writable($mainConf['phpfpm_pools_path']) &&
            !file_exists($this->poolFile)) {

            $vars = array(
                'datetime'=>        time(),
                'username'=>        Kernel::getInstance()->getUser()->getName(),
                'webserver_group'=> $mainConf['webserver_group'],
                'rootPath'=>        Kernel::getInstance()->getUser()->getHome(),
                'hostname'=>        $hostname,
                'rzcms_install'=>   (boolean)$mainConf['use_rzcms'],
                'fpm_version'=>    (boolean)$mainConf['fpm_version'],
            );

            return $this->generateFile($this->poolFile, 'pool.example.conf.twig', $vars);
        }
        return false;
    }

    public function restartServers(){

        $mainConf = Kernel::getInstance()->getConfiguration()->getData();

        switch ($mainConf['webserver_type']) {
            case 'nginx':
                exec("/etc/init.d/nginx restart");
                break;
            case 'apache2':
                exec("/etc/init.d/apache2 restart");
                break;
        }
        exec("/etc/init.d/php5-fpm restart");
    }

    public function __construct(){

        $mainConf = Kernel::getInstance()->getConfiguration()->getData();
        $this->vhostFile = $mainConf['vhosts_path']."/".Kernel::getInstance()->getUser()->getName().".conf";
        $this->poolFile = $mainConf['phpfpm_pools_path']."/".Kernel::getInstance()->getUser()->getName().".conf";
    }

    public function virtualHostExists()
    {
        return file_exists($this->vhostFile);
    }
    public function poolFileExists()
    {
        return file_exists($this->poolFile);
    }

    public function getVHostFile()
    {
        return $this->vhostFile;
    }
    public function getPoolFile()
    {
        return $this->poolFile;
    }

    /**
     * Check if system folder are writable
     * @return boolean
     */
    public function isWritable()
    {
        $mainConf = Kernel::getInstance()->getConfiguration()->getData();

        return is_writable($mainConf['vhosts_enabled_path']) &&
               is_writable($mainConf['vhosts_path']) &&
               is_writable($mainConf['phpfpm_pools_path']);
    }
    /**
     * Genere a file according to template and variables
     * @param  string $dest     Destination file absolute path
     * @param  string $template Relative path to template
     * @param  array $vars Variables to inject into template
     * @return boolean
     */
    public function generateFile( $dest, $template, &$vars )
    {
        if (is_writable(dirname($dest))) {
            $content = Kernel::getInstance()->render($template, $vars);
            if (file_put_contents($dest, $content)) {
                return true;
            }
            else {
                echo "[ERROR] — Unable to create ".$dest." file.".PHP_EOL;
            }
        }
        else {
            echo "[ERROR] — ".dirname($dest)." folder is not writable.".PHP_EOL;
        }
        return false;
    }
}
