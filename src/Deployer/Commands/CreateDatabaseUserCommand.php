<?php


namespace rezozero\Deployer\Commands;

use rezozero\Deployer\Controllers\Password;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDatabaseUserCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('database:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new database with its user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a database user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User and database name to create.')
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
            $password = $passwordGen->generate($config['database']['password_length']);
            $output->writeln("Random password will be generated");
        }

        $dsn = $dsn = "mysql:host=".$config['database']['host'];
        $pdo = new \PDO($dsn, $config['database']['user'], $config['database']['password']);

        // Creation of user "user_name"
        $pdo->query("CREATE USER '".$username."'@'".$config['database']['host']."' IDENTIFIED BY '".$password."';");

        // Creation of database "new_db"
        $pdo->query("CREATE DATABASE ".$username.";");
        // Adding all privileges on our newly created database
        $pdo->query("GRANT ALL PRIVILEGES on ".$username.".* TO '".$username."'@'".$config['database']['host']."' WITH GRANT OPTION;");


        $output->writeln("Database: <info>$username</info> was created for user: <info>$username</info> with password: <info>$password</info>");
    }
}