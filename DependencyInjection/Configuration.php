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
		/* @var $rootNode \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition */

		$supportedDrivers = array('orm');
		
		$defaultBlocks = array(
			'intro'=>array(
				'title'=>'OBJECT::getTitle',
				'subtitle'=>'admin.composer.default.subtitle',
				'text'=>'admin.composer.default.text',
			),
			'text'=>array(
				'text'=>'admin.composer.default.text',
			),
			'column2'=>array(
				'text1'=>'admin.composer.default.mediumText',
				'text2'=>'admin.composer.default.mediumText',
			),
			'column3'=>array(
				'text1'=>'admin.composer.default.mediumText',
				'text2'=>'admin.composer.default.mediumText',
				'text3'=>'admin.composer.default.mediumText',
			),
			'image'=>array(
				'image'=>null,
			),
			'image2'=>array(
				'image1'=>null,
				'image2'=>null,
				'text'=>'admin.composer.default.shortText',
			),
			'image3'=>array(
				'image1'=>null,
				'image2'=>null,
				'image3'=>null,
				'text'=>'admin.composer.default.shortText',
			),
			'slideshow'=>array(
				'images'=>null,
			),
			'video'=>array(
				'url'=>null,
				'embed'=>null,
			)
		);
		
		$defaultConfigs = array(
			'image'=>array(
				'image'=>array('w'=>500, 'h'=>500),
			),
			'image2'=>array(
				'image1'=>array('w'=>500, 'h'=>500),
				'image2'=>array('w'=>500, 'h'=>500),
			),
			'image3'=>array(
				'image1'=>array('w'=>500, 'h'=>500),
				'image2'=>array('w'=>500, 'h'=>500),
				'image3'=>array('w'=>500, 'h'=>500),
			),
			'slideshow'=>array(
				'images'=>array('w'=>1500, 'h'=>1000),
			)
		);
		
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
				->arrayNode('content')->addDefaultsIfNotSet()
					->children()
						->integerNode('maxlevel')->defaultValue(4)->end()
					->end()
				->end()
				->arrayNode('user_roles')->addDefaultsIfNotSet()
					->children()
						->integerNode('maxlevel_content')->defaultValue(3)->end()
					->end()
				->end()
				->arrayNode('composable')
					->addDefaultsIfNotSet()
					->children()
						->arrayNode('default')
							->addDefaultsIfNotSet()
							->children()
								->booleanNode('change_lang')->defaultTrue()->end()
								->booleanNode('change_theme')->defaultTrue()->end()
								->arrayNode('themes')
									->defaultValue(array('theme'))
									->prototype('scalar')->end()
								->end()
								->arrayNode('available_blocks')
									->defaultValue(array('intro', 'text', 'column2', 'column3', 'image', 'image2', 'image3', 'slideshow', 'video'))
									->prototype('scalar')->end()
								->end()
								->arrayNode('default_blocks')
									->defaultValue($defaultBlocks)
									->useAttributeAsKey('name')
									->beforeNormalization()
										->always(function($config) use($defaultBlocks) {
											return array_replace_recursive($defaultBlocks, $config);
										})
									->end()
									->prototype('variable')->end()
								->end()
								->arrayNode('config_blocks')
									->defaultValue($defaultConfigs)
									->useAttributeAsKey('name')
									->beforeNormalization()
										->always(function($config) use($defaultConfigs) {
											return array_replace_recursive($defaultConfigs, $config);
										})
									->end()
									->prototype('variable')->end()
								->end()
							->end()
						->end()
						->arrayNode('classes')
							->useAttributeAsKey('class')
							->prototype('array')
								->children()
									->booleanNode('change_lang')->end()
									->booleanNode('change_theme')->end()
									->arrayNode('themes')->prototype('scalar')->end()->end()
									->arrayNode('available_blocks')->prototype('scalar')->end()->end()
									->arrayNode('default_blocks')
										->useAttributeAsKey('name')
										->prototype('variable')->end()
									->end()
									->arrayNode('config_blocks')
										->useAttributeAsKey('name')
										->prototype('variable')->end()
									->end()
								->end()
							->end()
						->end()
					->end()
				->end()
			->end();

        return $treeBuilder;
    }
}
