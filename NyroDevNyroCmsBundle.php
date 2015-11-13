<?php

namespace NyroDev\NyroCmsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use \Symfony\Component\DependencyInjection\ContainerBuilder;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;

class NyroDevNyroCmsBundle extends Bundle
{
	
	public function build(ContainerBuilder $container) {
		parent::build($container);

        $mappings = array(
            realpath(__DIR__ . '/Resources/config/doctrine-mapping') => 'NyroDev\NyroCmsBundle\Model',
        );
        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array('nyrodev_nyrocms.model_manager_name')));
        }
		if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, array('nyrodev_nyrocms.model_manager_name')));
        }
	}

}
