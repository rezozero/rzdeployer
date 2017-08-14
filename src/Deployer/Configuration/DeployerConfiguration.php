<?php


namespace rezozero\Deployer\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DeployerConfiguration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('deployer');

        $rootNode
            ->append($this->addDatabaseNode())
            ->append($this->addUserNode())
            ->append($this->addPhpNode())
            ->append($this->addWebServerNode())
        ;

        return $treeBuilder;
    }

    protected function addDatabaseNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('database');

        $rootNode
            ->isRequired()
            ->children()
                ->scalarNode('host')
                    ->defaultValue("localhost")
                ->end()
                ->scalarNode('user')
                    ->defaultValue("root")
                ->end()
                ->scalarNode('password')
                    ->isRequired()
                ->end()
                ->integerNode('password_length')
                    ->defaultValue(12)
                ->end()
            ->end();

        return $rootNode;
    }

    protected function addUserNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('user');

        $rootNode
            ->isRequired()
            ->children()
                ->scalarNode('path')
                    ->info('This value defined each unix user home root path on system.')
                    ->defaultValue("/var/www/vhosts")
                ->end()
                ->scalarNode('group')
                    ->info('This value defined each unix base group.')
                    ->defaultValue("www-data")
                ->end()
                ->scalarNode('allowssh_group')
                    ->info('Fill this value if you restricted your SSH on a given group name.')
                    ->defaultNull()
                ->end()
                ->scalarNode('server_root')
                    ->info('Name your server root name for each user.')
                    ->defaultValue("htdocs")
                ->end()
                ->integerNode('password_length')
                    ->defaultValue(12)
                ->end()
            ->end();

        return $rootNode;
    }

    protected function addPhpNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('php_fpm');

        $rootNode
            ->isRequired()
            ->children()
                ->scalarNode('version')
                    ->info('PHP version installed on system.')
                    ->defaultValue("7.0")
                ->end()
                ->scalarNode('pool_path')
                    ->info('Path where PHP-FPM pool files are stored.')
                    ->defaultValue("/etc/php/7.0/fpm/pool.d")
                ->end()
                ->scalarNode('socket_path')
                    ->info('Path where PHP-FPM socket files are created.')
                    ->defaultValue("/var/run")
                ->end()
            ->end();

        return $rootNode;
    }

    protected function addWebServerNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('web_server');

        $rootNode
            ->isRequired()
            ->children()
                ->enumNode('type')
                    ->info('Web server type (apache24, apache22 or nginx).')
                    ->values(['apache22', 'apache24', 'nginx'])
                    ->defaultValue("nginx")
                ->end()
                ->scalarNode('user')
                    ->info('Web server running user.')
                    ->defaultValue("www-data")
                ->end()
                ->integerNode('port')
                    ->info('Web server port.')
                    ->defaultValue(80)
                ->end()
                ->scalarNode('domain_suffix')
                    ->info('Suffix to append after username to create website domain name (default .dev).')
                    ->defaultValue(".dev")
                ->end()
                ->scalarNode('available_path')
                    ->info('Path where web-server stores available virtual host files.')
                    ->defaultValue("/etc/nginx/sites-available")
                ->end()
                ->scalarNode('enabled_path')
                    ->info('Path where web-server stores enabled virtual host files.')
                    ->defaultValue("/etc/nginx/sites-enabled")
                ->end()
            ->end();

        return $rootNode;
    }
}