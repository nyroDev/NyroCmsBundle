<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NyroDevNyroCmsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
		
		// Transform config into parameters usable everywhere
		$container->setParameter('nyroCms.model.namespace', $config['model']['namespace']);
		foreach($config['model']['classes'] as $k=>$v)
			$container->setParameter('nyroCms.model.classes.'.$k, $v);
		
		$container->setParameter('nyroCms.user_types', $config['user_types']);

		$container->setParameter('nyroCms.content.maxlevel', $config['content']['maxlevel']);
		$container->setParameter('nyroCms.content.admin_per_root', $config['content']['admin_per_root']);
		$container->setParameter('nyroCms.user_roles.maxlevel_content', $config['user_roles']['maxlevel_content']);
		
		$composable = $config['composable']['classes'];
		$composable['default'] = $config['composable']['default'];
		$container->setParameter('nyroCms.composable', $composable);
		
		$dbDriver = $container->getParameter('nyroDev_utility.db_driver');
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_'.$dbDriver.'.yml');
    }
}
