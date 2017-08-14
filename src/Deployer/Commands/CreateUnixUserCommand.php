<?php


namespace rezozero\Deployer\Commands;

use rezozero\Deployer\Controllers\Password;
use rezozero\Deployer\Controllers\UnixUser;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUnixUserCommand extends ConfigurationAwareCommand
{
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
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('user:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new unix user <info>[Must be run as root]</info>.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a unix user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User name to create.')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password.')
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
        if ($input->getArgument('password') != '') {
            $password = $input->getArgument('password');
        } else {
            $passwordGen = new Password();
            $password = $passwordGen->generate($config['user']['password_length']);
            $output->writeln("Random password will be generated");
        }

        $unixUser = new UnixUser($username, $password, $config);

        if ($unixUser->exists()) {
            throw new InvalidArgumentException('User already exists.');
        }

        $unixUser->create();
        $output->writeln("User: <info>$username</info> has been created with password: <info>$password</info>");
        $output->writeln("Home: <info>" . $unixUser->getHomeFolder() . "</info>");
        $output->writeln("Server root: <info>" . $unixUser->getVhostFolder() . "</info>");
    }
}