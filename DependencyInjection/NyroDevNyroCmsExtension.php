<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

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
		$container->setParameter('nyroCms.db_driver', $config['db_driver']);
		$container->setParameter('nyroCms.model_manager_name', $config['model_manager_name']);
		$container->setParameter('nyroCms.model.namespace', $config['model']['namespace']);
		foreach($config['model']['classes'] as $k=>$v)
			$container->setParameter('nyroCms.model.classes.'.$k, $v);
		
		$container->setParameter('nyroCms.user_types', $config['user_types']);
		
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_'.$config['db_driver'].'.yml');
		
		if ('orm' === $config['db_driver']) {
			$managerService = 'nyrocms.entity_manager';
			$doctrineService = 'doctrine';
		} else {
			$managerService = 'nyrocms.document_manager';
			$doctrineService = sprintf('doctrine_%s', $config['db_driver']);
		}
		$definition = $container->getDefinition($managerService);
		if (method_exists($definition, 'setFactory')) {
			$definition->setFactory(array(new Reference($doctrineService), 'getManager'));
		} else {
			$definition->setFactoryService($doctrineService);
			$definition->setFactoryMethod('getManager');
		}
    }
}
