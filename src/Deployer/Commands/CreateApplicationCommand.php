<?php

namespace rezozero\Deployer\Commands;

use rezozero\Deployer\Controllers\UnixUser;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateApplicationCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('application:create')

            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new application for a given user.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a new application for an user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User for whom the application will be created.')
            ->addArgument('type', InputArgument::REQUIRED, 'Application template.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Don’t not create files, only display them.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $config = $this->getConfiguration();
        $twigLoader = new \Twig_Loader_Filesystem(APP_ROOT . '/templates');
        $twig = new \Twig_Environment($twigLoader);
        $username = $input->getArgument('username');
        $unixUser = new UnixUser($username, "", $config);

        if (!$unixUser->exists()) {
            throw new InvalidArgumentException('User does not exist.');
        }

        $webserverTemplate = $input->getArgument('type') . '.' . $config['web_server']['type'] . '.conf.twig';
        $phpTemplate = 'php_pool.conf.twig';

        if (!file_exists(APP_ROOT. '/templates/'.$webserverTemplate)) {
            throw new InvalidArgumentException('Web server template does not exist: '.$webserverTemplate);
        }
        if (!file_exists(APP_ROOT. '/templates/'.$phpTemplate)) {
            throw new InvalidArgumentException('PHP pool template does not exist: '.$phpTemplate);
        }

        $vars = [
            'username' => $username,
            'webserver_user' => $config['web_server']['user'],
            'webserver_port' => $config['web_server']['port'],
            'server_name' => $username . $config['web_server']['domain_suffix'],
            'server_root' => $unixUser->getVhostFolder(),
            'php_sock' => $config['php_fpm']['socket_path'] . '/php' . $config['php_fpm']['version'] . '-fpm-' . $username . '.sock',
            'log_folder' => $unixUser->getLogFolder(),
        ];

        $fileName = $username . $config['web_server']['domain_suffix'] . '.conf';
        $vhostContents = $twig->render($webserverTemplate, $vars) . PHP_EOL;
        $vhostFilePath = $config['web_server']['available_path'] . DIRECTORY_SEPARATOR . $fileName;
        $vhostLinkPath = $config['web_server']['enabled_path'] . DIRECTORY_SEPARATOR . $fileName;
        $phpPoolContents = $twig->render($phpTemplate, $vars) . PHP_EOL;
        $phpPoolFilePath = $config['php_fpm']['pool_path'] . DIRECTORY_SEPARATOR . $fileName;

        if ($input->getOption('dry-run')) {
            $output->writeln('<info>Virtual host file contents:</info>');
            $output->write($vhostContents);
            $output->write(PHP_EOL);
            $output->writeln('<info>PHP pool file contents:</info>');
            $output->write($phpPoolContents);
            $output->write(PHP_EOL);

            return;
        }

        if (false === @file_put_contents($vhostFilePath, $vhostContents)) {
            throw new InvalidArgumentException('Impossible to write "'. $vhostFilePath . '" file.');
        }

        if (false === @symlink($vhostFilePath, $vhostLinkPath)) {
            throw new InvalidArgumentException('Impossible to symlink "'. $vhostFilePath . '" file to "' . $vhostLinkPath . "\".");
        }

        if (false === @file_put_contents($phpPoolFilePath, $phpPoolContents)) {
            throw new InvalidArgumentException('Impossible to write "'. $phpPoolFilePath . '" file.');
        }

        $output->writeln('Virtual host file setup in: <info>' . $vhostLinkPath . '</info>');
        $output->writeln('PHP pool file setup in: <info>' . $phpPoolFilePath . '</info>');
        $output->writeln('New configuration file have been created, <info>please reload your '.$config['web_server']['type'].' and PHP services.</info>');
    }
}