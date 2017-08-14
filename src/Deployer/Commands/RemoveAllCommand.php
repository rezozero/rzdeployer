<?php


namespace rezozero\Deployer\Commands;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RemoveAllCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('all:remove')

            // the short description shown while running "php bin/console list"
            ->setDescription('Removes user, database and application in one command.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to remove an application and its user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User for whom the application will be created.')
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

        $username = $input->getArgument('username');

        /*
         * Delete application
         */
        $command = $this->getApplication()->find('application:remove');

        $arguments = array(
            'command' => 'application:remove',
            'username' => $username,
        );

        $userCreate = new ArrayInput($arguments);
        $command->run($userCreate, $output);

        /*
         * Delete database
         */
        $command = $this->getApplication()->find('database:remove');

        $arguments = array(
            'command' => 'database:remove',
            'username' => $username,
        );

        $userCreate = new ArrayInput($arguments);
        $command->run($userCreate, $output);

        /*
         * Delete user
         */
        $command = $this->getApplication()->find('user:remove');

        $arguments = array(
            'command' => 'user:remove',
            'username' => $username,
        );

        $userCreate = new ArrayInput($arguments);
        $command->run($userCreate, $output);
    }
}