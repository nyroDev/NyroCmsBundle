<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Config\Resource\FileResource;

class ValidationPass implements CompilerPassInterface {
	
    public function process(ContainerBuilder $container) {
        if (!$container->hasParameter('nyroDev_utility.db_driver')) {
            return;
        }

        $driver = $container->getParameter('nyroDev_utility.db_driver');

        $validationFile = __DIR__ . '/../../Resources/config/doctrine-validation/'.$driver.'.xml';

        if ($container->hasDefinition('validator.builder')) {
            // Symfony 2.5+
            $container->getDefinition('validator.builder')
                ->addMethodCall('addXmlMapping', array($validationFile));

            return;
        }

        // Old method of loading validation
        if (!$container->hasParameter('validator.mapping.loader.xml_files_loader.mapping_files')) {
            return;
        }

        $files = $container->getParameter('validator.mapping.loader.xml_files_loader.mapping_files');

        if (is_file($validationFile)) {
            $files[] = realpath($validationFile);
            $container->addResource(new FileResource($validationFile));
        }

        $container->setParameter('validator.mapping.loader.xml_files_loader.mapping_files', $files);
    }
}