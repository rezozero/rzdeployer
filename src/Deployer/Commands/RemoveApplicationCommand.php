<?php

namespace rezozero\Deployer\Commands;

use rezozero\Deployer\Controllers\UnixUser;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RemoveApplicationCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('application:remove')

            // the short description shown while running "php bin/console list"
            ->setDescription('Remove an user application.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to remove a application for an user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User for whom the application will be created.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Don’t remove files, only display paths.')
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
        $unixUser = new UnixUser($username, "", $config);

        $question = new ConfirmationQuestion("<question>Are you sure to remove $username application configuration files?</question> [y|N]", false);
        if ($this->getHelper('question')->ask($input, $output, $question) === false) {
            return false;
        }

        if (!$unixUser->exists()) {
            throw new InvalidArgumentException('User does not exist.');
        }

        $fileName = $username . $config['web_server']['domain_suffix'] . '.conf';
        $vhostFilePath = $config['web_server']['available_path'] . DIRECTORY_SEPARATOR . $fileName;
        $vhostLinkPath = $config['web_server']['enabled_path'] . DIRECTORY_SEPARATOR . $fileName;
        $phpPoolFilePath = $config['php_fpm']['pool_path'] . DIRECTORY_SEPARATOR . $fileName;

        if ($input->getOption('dry-run')) {
            $output->writeln('<info>Virtual host file path:</info>');
            $output->write($vhostFilePath);
            $output->write(PHP_EOL);
            $output->writeln('<info>Virtual host file link:</info>');
            $output->write($vhostLinkPath);
            $output->write(PHP_EOL);
            $output->writeln('<info>PHP pool file path:</info>');
            $output->write($phpPoolFilePath);
            $output->write(PHP_EOL);

            return true;
        }

        if (false === @unlink($vhostFilePath)) {
            throw new InvalidArgumentException('Impossible to remove "'. $vhostFilePath . '" file.');
        }

        if (false === @unlink($vhostLinkPath)) {
            throw new InvalidArgumentException('Impossible to remove "' . $vhostLinkPath . "\" link.");
        }

        if (false === @unlink($phpPoolFilePath)) {
            throw new InvalidArgumentException('Impossible to remove "'. $phpPoolFilePath . '" file.');
        }

        $output->writeln('Removed virtual host file setup: <info>' . $vhostFilePath . '</info>');
        $output->writeln('Removed virtual host file link: <info>' . $vhostLinkPath . '</info>');
        $output->writeln('Removed PHP pool file setup: <info>' . $phpPoolFilePath . '</info>');
        $output->writeln('Configuration file have been deleted, <info>please reload your '.$config['web_server']['type'].' and PHP services.</info>');
    }
}