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


use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Process\ProcessBuilder;

class UnixUser
{
    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $homeFolder;
    /**
     * @var array
     */
    private $configuration;
    /**
     * @var array
     */
    private $groups;
    /**
     * @var string
     */
    private $vhostFolder;
    /**
     * @var string
     */
    private $logFolder;
    /**
     * @var string
     */
    private $privateFolder;

    /**
     * UnixUser constructor.
     *
     * @param $username
     * @param $password
     * @param array $configuration
     */
    public function __construct($username, $password = "", array $configuration = [])
    {
        $this->configuration = $configuration;

        $this->username = $username;
        $this->password = $password;
        $this->groups = [
            $this->configuration["user"]["group"]
        ];
        $this->homeFolder = $this->configuration["user"]["path"] . DIRECTORY_SEPARATOR . $username;
        $this->vhostFolder = $this->homeFolder . DIRECTORY_SEPARATOR . $this->configuration["user"]["server_root"];
        $this->logFolder = $this->homeFolder . DIRECTORY_SEPARATOR . "log";
        $this->privateFolder = $this->homeFolder . DIRECTORY_SEPARATOR . "private";
    }

    /**
     * @return string
     */
    public function getHomeFolder()
    {
        return $this->homeFolder;
    }

    /**
     * @return string
     */
    public function getVhostFolder()
    {
        return $this->vhostFolder;
    }

    /**
     * @return string
     */
    public function getLogFolder()
    {
        return $this->logFolder;
    }

    /**
     * @return string
     */
    public function getPrivateFolder()
    {
        return $this->privateFolder;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        if ($this->userExists() && file_exists($this->homeFolder)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function userExists()
    {
        $builder = new ProcessBuilder([
            'id', '-u', $this->username
        ]);
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            return false;
        }

        return true;
    }

    /**
     * Create user and its files.
     */
    public function create()
    {
        if (!$this->exists()) {
            $this->createUser();
            $this->changePassword();
            $this->createFileStructure();
        }
    }

    /**
     * Delete user and its home folder.
     */
    public function delete()
    {
        if ($this->userExists()) {
            $builder = new ProcessBuilder([
                'deluser',
                '--remove-home',
                $this->username
            ]);
            $process = $builder->getProcess();
            $process->run();

            if (!$process->isSuccessful()) {
                throw new InvalidArgumentException("Unable to delete unix user: " . $process->getErrorOutput());
            }
        }
    }

    protected function createUser()
    {
        if (!empty($this->configuration["user"]["allowssh_group"])) {
            $this->groups[] = $this->configuration["user"]["allowssh_group"];
        }

        /*
         * Create user without password
         */
        $userQuery = [
            'useradd',
            '--home', $this->homeFolder,
            '-m',
            '-s', '/bin/bash'
        ];

        if (count($this->groups) > 0) {
            $userQuery[] = "-G";
            $userQuery[] = implode(',', $this->groups);
        }

        $userQuery[] = $this->username;

        $builder = new ProcessBuilder($userQuery);
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new InvalidArgumentException("Unable to create unix user: " . $process->getErrorOutput());
        }
    }

    /**
     *
     */
    protected function changePassword()
    {
        /*
         * Add password
         */
        $passwordTool = new Password();
        $builder = new ProcessBuilder([
            'usermod',
            '-p', $passwordTool->encrypt($this->password),
            $this->username
        ]);
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new InvalidArgumentException("Unable to change user password: " . $process->getErrorOutput());
        }
    }


    protected function createFileStructure()
    {
        if (chdir($this->homeFolder) === false) {
            throw new InvalidArgumentException("Unable to change directory into: " . $this->homeFolder);
        }

        /*
         * Change user home mod to 750 and writable by www-data
         */
        chown($this->homeFolder, $this->configuration["user"]["group"]);
        chgrp($this->homeFolder, $this->username);
        chmod($this->homeFolder, 0750);

        // Create special log folder
        $this->createFolder($this->logFolder, 0770);
        chown($this->logFolder, $this->username);
        chgrp($this->logFolder, "root");

        // Create user folders

        $this->createFolder($this->vhostFolder);
        $this->createFolder($this->privateFolder);
        $this->createFolder($this->privateFolder . DIRECTORY_SEPARATOR . "git");
        $this->createFolder($this->privateFolder . DIRECTORY_SEPARATOR . "backup", 0700);

        // Special folder to store your domain DKIM public and private keys
        $dkimFolder = $this->privateFolder . DIRECTORY_SEPARATOR . "dkim";
        $this->createFolder($dkimFolder);
        chmod($dkimFolder, 0700);

        /*
         * SSH folder must be only read/writeable by user
         */
        $sshFolder = $this->homeFolder . DIRECTORY_SEPARATOR . '.ssh';
        $this->createFolder($sshFolder, 0700);
        $this->createSshKey($sshFolder);

        /*
         * Create composer and yarn cache folder
         */
        $this->createFolder($this->homeFolder . DIRECTORY_SEPARATOR . ".composer", 0700);
        $this->createFolder($this->homeFolder . DIRECTORY_SEPARATOR . ".cache", 0700);
        $this->createFolder($this->homeFolder . DIRECTORY_SEPARATOR . ".yarn", 0700);
        $this->createFile($this->homeFolder . DIRECTORY_SEPARATOR . ".bash_history", 0640);

        // Create test file
        file_put_contents($this->vhostFolder . DIRECTORY_SEPARATOR . "index.php", "<?php phpinfo(); ?>");
        chown($this->vhostFolder . DIRECTORY_SEPARATOR . "index.php", $this->username);
        chgrp($this->vhostFolder . DIRECTORY_SEPARATOR . "index.php", $this->username);
        chmod($this->vhostFolder . DIRECTORY_SEPARATOR . "index.php", 0644);
    }

    /**
     * @param string $sshFolder
     */
    protected function createSshKey($sshFolder)
    {
        /*
         * ssh-keygen -q -t rsa -N "" -C "comment" -f ~/.ssh/id_rsa
         */
        $createSSHKeyQuery = [
            'ssh-keygen',
            '-q',
            '-t', 'rsa',
            '-N', '',
            '-C', $this->username,
            '-f', $sshFolder . DIRECTORY_SEPARATOR . 'id_rsa',
        ];

        $builder = new ProcessBuilder($createSSHKeyQuery);
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new InvalidArgumentException("Unable to create ssh keys: " . $process->getErrorOutput());
        }

        $this->ownPath($sshFolder . DIRECTORY_SEPARATOR . 'id_rsa');
        $this->ownPath($sshFolder . DIRECTORY_SEPARATOR . 'id_rsa.pub');
    }

    /**
     * @param string $path
     * @param int $mode
     */
    protected function createFile($path, $mode = 0644)
    {
        touch($path);
        $this->ownPath($path);
        chmod($path, $mode);
    }

    /**
     * @param string $path
     * @param int $mode
     */
    protected function createFolder($path, $mode = 0755)
    {
        mkdir($path, $mode, true);
        $this->ownPath($path);
    }

    /**
     * @param string $path
     */
    protected function ownPath($path)
    {
        chown($path, $this->username);
        chgrp($path, $this->username);
    }
}
