<?php


namespace rezozero\Deployer\Commands;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DropDatabaseUserCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('database:remove')

            // the short description shown while running "php bin/console list"
            ->setDescription('Drop an existing database with its user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to Drop an existing database and its user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User and database name to drop.')
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

        $question = new ConfirmationQuestion("<question>Are you sure to drop $username database and user?</question> [y|N]", false);
        if ($this->getHelper('question')->ask($input, $output, $question) === false) {
            return false;
        }

        try {
            $dsn = $dsn = "mysql:host=".$config['database']['host'];
            $pdo = new \PDO($dsn, $config['database']['user'], $config['database']['password']);

            $pdo->query("DROP DATABASE `".$username."`;");
            $pdo->query("DROP USER '$username'@'".$config['database']['host']."';");

            $output->writeln("Database: <info>$username</info> and user: <info>$username</info> were dropped");

            return true;
        } catch(\PDOException $e) {
            throw new InvalidConfigurationException($e->getMessage());
        }
    }
}