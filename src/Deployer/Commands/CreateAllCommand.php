<?php


namespace rezozero\Deployer\Commands;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAllCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('all:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates user, database and application in one command.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a new application for an user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User for whom the application will be created.')
            ->addArgument('type', InputArgument::REQUIRED, 'Application template.')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password for SSH and database.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function needsPrivileges()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        /*
         * Create user
         */
        $command = $this->getApplication()->find('user:create');

        $arguments = array(
            'command' => 'user:create',
            'username' => $input->getArgument('username'),
            'password' => $input->getArgument('password'),
        );

        $userCreate = new ArrayInput($arguments);
        $command->run($userCreate, $output);


        /*
         * Create user
         */
        $command = $this->getApplication()->find('database:create');

        $arguments = array(
            'command' => 'database:create',
            'username' => $input->getArgument('username'),
            'password' => $input->getArgument('password'),
        );

        $userCreate = new ArrayInput($arguments);
        $command->run($userCreate, $output);

        /*
         * Create application
         */
        $command = $this->getApplication()->find('application:create');

        $arguments = array(
            'command' => 'application:create',
            'username' => $input->getArgument('username'),
            'type' => $input->getArgument('type'),
        );

        $userCreate = new ArrayInput($arguments);
        $command->run($userCreate, $output);
    }
}