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


class UnixUser {

    private $username;
    private $password;
    private $homeFolder;
    private $vhostRoot;

    public function __construct( $username )
    {
        $mainConf = Kernel::getInstance()->getConfiguration()->getData();

        $this->username =   $username;
        $this->password =   Password::generate(14);
        $this->homeFolder = $mainConf["webserver_root"]."/".Kernel::getInstance()->getConfiguration()->getHostname();

        $this->vhostRoot = (!empty($mainConf['vhost_root']) ? $mainConf['vhost_root'] : 'htdocs');
    }

    public function createUser()
    {
        $mainConf = Kernel::getInstance()->getConfiguration()->getData();

        $results = null;

        /*
         * Additionnal groups
         */
        $groups = array();

        if (!empty($mainConf["allowssh_group"])) {
            $groups[] = $mainConf["allowssh_group"];
        }

        /*
         * Create user without password
         */
        $userQuery = array(
            'useradd',
            '--home '.$this->homeFolder,
            '-m',
            '-s /bin/bash'
        );

        if (count($groups) > 0) {
            $userQuery[] = "-G ".implode(',', $groups);
        }

        $userQuery[] = $this->username;

        exec(implode(' ', $userQuery), $results);
        if (count($results) > 0) {
            echo "[ERROR] Unable to create unix user.".PHP_EOL;
            return false;
        }

        /*
         * Add password
         */
        $pwQuery = 'usermod -p '.Password::encrypt($this->password).' '.$this->username;
        exec($pwQuery, $pwResults);
        if (count($pwResults) > 0) {
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

            // Create special log folder
            $this->createFolder($this->homeFolder."/log", 0770);
            chown($this->homeFolder."/log", $this->username);
            chgrp($this->homeFolder."/log", "root");

            // Create user folders
            $this->createFolder($this->homeFolder."/".$this->vhostRoot);
            $this->createFolder($this->homeFolder."/private");
            $this->createFolder($this->homeFolder."/private/git");
            $this->createFolder($this->homeFolder."/private/backup", 0700);

            // Special folder to store your domain DKIM public and private keys
            $this->createFolder($this->homeFolder."/private/dkim");
            chmod($this->homeFolder."/private/dkim", 0700);

            /*
             * SSH folder must be only read/writeable by user
             */
            $this->createFolder($this->homeFolder."/.ssh", 0700);

            /*
             * ssh-keygen -q -t rsa -N "" -C "comment" -f ~/.ssh/id_rsa
             */
            $createSSHKeyQuery = array(
                'ssh-keygen',
                '-q',
                '-t rsa',
                '-N ""',
                '-C "' . $this->username . '"',
                '-f ' . $this->homeFolder . '/.ssh/id_rsa',
            );
            exec(implode(' ', $createSSHKeyQuery));
            $this->ownPath($this->homeFolder . '/.ssh/id_rsa');
            $this->ownPath($this->homeFolder . '/.ssh/id_rsa.pub');

            /*
             * Create composer cache folder
             */
            $this->createFolder($this->homeFolder."/.composer", 0700);

            // Create test file
            file_put_contents($this->homeFolder."/".$this->vhostRoot."/index.php", "<?php phpinfo(); ?>");
            chown($this->homeFolder."/".$this->vhostRoot."/index.php", $this->username);
            chgrp($this->homeFolder."/".$this->vhostRoot."/index.php", $this->username);
            chmod($this->homeFolder."/".$this->vhostRoot."/index.php", 0644);

            return true;
        }
        return false;
    }

    public function createFile($path, $mode = 0644)
    {
        touch($path);
        $this->ownPath($path);
        chmod($path, $mode);
    }
    
    public function createFolder($path, $mode = 0755)
    {
        mkdir($path, $mode, true);
        $this->ownPath($path);
    }

    public function ownPath($path)
    {
        chown($path, $this->username);
        chgrp($path, $this->username);
    }
}
