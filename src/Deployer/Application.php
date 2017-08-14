<?php


namespace rezozero\Deployer;

use rezozero\Deployer\Commands\CreateAllCommand;
use rezozero\Deployer\Commands\CreateApplicationCommand;
use rezozero\Deployer\Commands\CreateDatabaseUserCommand;
use rezozero\Deployer\Commands\CreateUnixUserCommand;
use rezozero\Deployer\Commands\DeleteUnixUserCommand;
use rezozero\Deployer\Commands\DropDatabaseUserCommand;
use rezozero\Deployer\Commands\RemoveAllCommand;
use rezozero\Deployer\Commands\RemoveApplicationCommand;

class Application extends \Symfony\Component\Console\Application
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Rezo Zero Deployer';
    }

    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), [
            new CreateUnixUserCommand(),
            new DeleteUnixUserCommand(),
            new CreateDatabaseUserCommand(),
            new DropDatabaseUserCommand(),
            new CreateApplicationCommand(),
            new RemoveApplicationCommand(),
            new CreateAllCommand(),
            new RemoveAllCommand(),
        ]);
    }
}