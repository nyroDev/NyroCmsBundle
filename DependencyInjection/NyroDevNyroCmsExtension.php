<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NyroDevNyroCmsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Transform config into parameters usable everywhere
        $container->setParameter('nyrocms.model.namespace', $config['model']['namespace']);
        foreach ($config['model']['classes'] as $k => $v) {
            $container->setParameter('nyrocms.model.classes.'.$k, $v);
        }

        $container->setParameter('nyrocms.user_types', $config['user_types']);
        $container->setParameter('nyrocms.route_resources', $config['route_resources']);
        $container->setParameter('nyrocms.route_handler_path', $config['route_handler_path']);
        $container->setParameter('nyrocms.disabled_locale_urls', $config['disabled_locale_urls']);

        $container->setParameter('nyrocms.content.maxlevel', $config['content']['maxlevel']);
        $container->setParameter('nyrocms.content.admin_per_root', $config['content']['admin_per_root']);
        $container->setParameter('nyrocms.content.root_composer', $config['content']['root_composer']);

        $container->setParameter('nyrocms.user_roles.maxlevel_content', $config['user_roles']['maxlevel_content']);

        $container->setParameter('nyrocms.email.router_scheme', $config['email']['router_scheme']);
        $container->setParameter('nyrocms.email.router_host', $config['email']['router_host']);
        $container->setParameter('nyrocms.email.router_base_url', $config['email']['router_base_url']);

        $container->setParameter('nyrocms.composable_blocks', $config['composable_blocks']);
        $container->setParameter('nyrocms.composable_items', $config['composable_items']);

        $composable = $config['composable']['classes'];
        $composable['default'] = $config['composable']['default'];
        $container->setParameter('nyrocms.composable', $composable);

        $dbDriver = $container->getParameter('nyroDev_utility.db_driver');
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('forms.yaml');
        $loader->load('services_'.$dbDriver.'.yaml');

        // Load commands
        $definition = new Definition();
        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
        ;
        $dirLoader = new Loader\DirectoryLoader($container, new FileLocator(__DIR__.'/../Command'));
        $dirLoader->registerClasses($definition, 'NyroDev\\NyroCmsBundle\\Command\\', './*');

        // Load controllers
        $definition = new Definition();
        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(false)
            ->addMethodCall('setContainer', [new Reference('service_container')])
            ->addTag('controller.service_arguments')
        ;

        $dirLoader = new Loader\DirectoryLoader($container, new FileLocator(__DIR__.'/../Controller'));
        $dirLoader->registerClasses($definition, 'NyroDev\\NyroCmsBundle\\Controller\\', './*');
    }
}
