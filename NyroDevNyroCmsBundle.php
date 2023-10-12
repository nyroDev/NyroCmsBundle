<?php

namespace NyroDev\NyroCmsBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use NyroDev\NyroCmsBundle\DependencyInjection\Compiler\DbPass;
use NyroDev\NyroCmsBundle\DependencyInjection\Compiler\ValidationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

class NyroDevNyroCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ValidationPass());
        $container->addCompilerPass(new DbPass());

        // Kernel is not build yet
        // $pathMapping = $container->get(KernelInterface::class)->getProjectDir().'/config/nyrocms-doctrine-mapping';
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
