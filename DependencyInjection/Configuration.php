<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nyro_dev_nyro_cms');

		$supportedDrivers = array('orm');
		
		$rootNode
			->children()
                ->scalarNode('db_driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                    ->cannotBeOverwritten()
                    ->isRequired()
                    ->cannotBeEmpty()
				->end()
				->scalarNode('model_manager_name')->defaultNull()->end()
				->arrayNode('user_types')
					->defaultValue(array('admin', 'superadmin'))
					->prototype('scalar')->end()
				->end()
				->arrayNode('model')->isRequired()->cannotBeEmpty()
					->children()
						->scalarNode('namespace')->isRequired()->cannotBeEmpty()->end()
						->arrayNode('classes')
							->addDefaultsIfNotSet()
							->children()
								->scalarNode('user')->defaultValue('User')->end()
								->scalarNode('user_log')->defaultValue('Log\\UserLog')->end()
								->scalarNode('user_role')->defaultValue('UserRole')->end()
								->scalarNode('user_login')->defaultValue('UserLogin')->end()
								->scalarNode('content')->defaultValue('Content')->end()
								->scalarNode('content_log')->defaultValue('Log\\ContentLog')->end()
								->scalarNode('content_translation')->defaultValue('Translation\\ContentTranslation')->end()
								->scalarNode('content_spec')->defaultValue('ContentSpec')->end()
								->scalarNode('content_spec_log')->defaultValue('Log\\ContentSpecLog')->end()
								->scalarNode('content_spec_translation')->defaultValue('Translation\\ContentSpecTranslation')->end()
								->scalarNode('content_handler')->defaultValue('ContentHandler')->end()
							->end()
						->end()
					->end()
				->end()
			->end();

        return $treeBuilder;
    }
}
