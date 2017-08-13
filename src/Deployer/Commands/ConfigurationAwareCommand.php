<?php


namespace rezozero\Deployer\Commands;

use rezozero\Deployer\Configuration\DeployerConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

abstract class ConfigurationAwareCommand extends Command
{
    /**
     * @var array|null
     */
    private $configuration;

    private function loadConfiguration()
    {
        $configDirectories = [APP_ROOT.'/conf'];
        $locator = new FileLocator($configDirectories);
        $configFiles = $locator->locate('config.yml', null, false);

        foreach ($configFiles as $configFile) {
            $configs = Yaml::parse(file_get_contents($configFile));
            $processor = new Processor();
            $deployerConfiguration = new DeployerConfiguration();
            $this->configuration = $processor->processConfiguration($deployerConfiguration, $configs);
        }
    }

    /**
     * @return array
     */
    protected function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->loadConfiguration();
        }

        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfiguration();
        /*
         * Check running user
         */
        $user = trim(shell_exec('whoami'));
        if ($this->configuration['sudo'] === true && $user !== 'root') {
            throw new InvalidArgumentException('You must run this command with sudo.');
        } elseif ($this->configuration['sudo'] === false && $user === 'root') {
            throw new InvalidArgumentException('You must not run this command with sudo or as root.');
        }
    }
}