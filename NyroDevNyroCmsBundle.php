<?php

namespace NyroDev\NyroCmsBundle;

use NyroDev\NyroCmsBundle\DependencyInjection\Compiler\ValidationPass;
use NyroDev\NyroCmsBundle\DependencyInjection\Compiler\DbPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;

class NyroDevNyroCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ValidationPass());
        $container->addCompilerPass(new DbPass());

        $pathMapping = $container->getParameter('kernel.project_dir').'/config/nyrocms-doctrine-mapping';
        if (file_exists($pathMapping)) {
            $mappings = [
                $pathMapping => 'NyroDev\NyroCmsBundle\Model',
            ];
    
            if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
                $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, ['nyrocms.model_manager_name']));
            }
            if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
                $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, ['nyrocms.model_manager_name']));
            }
        }
    }
}
