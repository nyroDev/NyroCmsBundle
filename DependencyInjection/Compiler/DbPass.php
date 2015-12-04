<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DbPass implements CompilerPassInterface {
	
    public function process(ContainerBuilder $container) {
        if (!$container->hasParameter('nyroDev_utility.db_driver')) {
            return;
        }
		
		$dbDriver = $container->getParameter('nyroDev_utility.db_driver');
		
		if ('orm' === $dbDriver) {
			$managerService = 'nyrodev.entity_manager';
			$doctrineService = 'doctrine';
		} else {
			$managerService = 'nyrodev.document_manager';
			$doctrineService = sprintf('doctrine_%s', $dbDriver);
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