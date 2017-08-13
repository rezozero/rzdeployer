<?php


namespace rezozero\Deployer\Commands;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUnixUserCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('unix-user:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new unix user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a unix user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User name to create.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $config = $this->getConfiguration();

        $username = $input->getArgument('username');
        $userHomePath = $config['user']['path'] . '/' . $username;
        if (file_exists($userHomePath)) {
            throw new InvalidArgumentException($userHomePath . ' folder already exists.');
        }
    }
}