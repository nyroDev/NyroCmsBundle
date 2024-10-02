<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('nyro_dev_nyro_cms');
        $rootNode = $builder->getRootNode($builder, 'nyro_dev_nyro_cms');

        $defaultBlocks = [
            'intro' => [
                'title' => 'OBJECT::getTitle',
                'subtitle' => 'admin.composer.default.subtitle',
                'text' => 'admin.composer.default.text',
            ],
            'text' => [
                'text' => 'admin.composer.default.text',
            ],
            'column2' => [
                'text1' => 'admin.composer.default.mediumText',
                'text2' => 'admin.composer.default.mediumText',
            ],
            'column3' => [
                'text1' => 'admin.composer.default.mediumText',
                'text2' => 'admin.composer.default.mediumText',
                'text3' => 'admin.composer.default.mediumText',
            ],
            'image_text' => [
                'text' => 'admin.composer.default.mediumText',
                'image' => null,
            ],
            'text_image' => [
                'text' => 'admin.composer.default.mediumText',
                'image' => null,
            ],
            'image' => [
                'image' => null,
            ],
            'image2' => [
                'image1' => null,
                'image2' => null,
                'text' => 'admin.composer.default.shortText',
            ],
            'image3' => [
                'image1' => null,
                'image2' => null,
                'image3' => null,
                'text' => 'admin.composer.default.shortText',
            ],
            'slideshow' => [
                'images' => null,
            ],
            'video' => [
                'url' => null,
                'embed' => null,
                'autoplay' => null,
            ],
            'separator' => [
            ],
        ];

        $defaultConfigs = [
            'intro' => [
                'template' => '@NyroDevNyroCms/Composer/block_intro.html.php',
            ],
            'text' => [
                'template' => '@NyroDevNyroCms/Composer/block_text.html.php',
            ],
            'column2' => [
                'template' => '@NyroDevNyroCms/Composer/block_column2.html.php',
            ],
            'column3' => [
                'template' => '@NyroDevNyroCms/Composer/block_column3.html.php',
            ],
            'image' => [
                'template' => '@NyroDevNyroCms/Composer/block_image.html.php',
                'image' => ['image' => true, 'w' => 1500, 'h' => 600, 'name' => 'image', 'fit' => true, 'quality' => 80],
            ],
            'image_text' => [
                'template' => '@NyroDevNyroCms/Composer/block_image_text.html.php',
                'image' => ['image' => true, 'w' => 500, 'h' => 500, 'name' => 'image', 'fit' => true, 'quality' => 80],
            ],
            'text_image' => [
                'template' => '@NyroDevNyroCms/Composer/block_text_image.html.php',
                'image' => ['image' => true, 'w' => 500, 'h' => 500, 'name' => 'image', 'fit' => true, 'quality' => 80],
            ],
            'image2' => [
                'template' => '@NyroDevNyroCms/Composer/block_image2.html.php',
                'image1' => ['image' => true, 'w' => 500, 'h' => 500, 'name' => 'image1', 'fit' => true, 'quality' => 80],
                'image2' => ['image' => true, 'w' => 1000, 'h' => 500, 'name' => 'image2', 'fit' => true, 'quality' => 80],
            ],
            'image3' => [
                'template' => '@NyroDevNyroCms/Composer/block_image3.html.php',
                'image1' => ['image' => true, 'w' => 500, 'h' => 500, 'name' => 'image1', 'fit' => true, 'quality' => 80],
                'image2' => ['image' => true, 'w' => 500, 'h' => 500, 'name' => 'image2', 'fit' => true, 'quality' => 80],
                'image3' => ['image' => true, 'w' => 500, 'h' => 500, 'name' => 'image3', 'fit' => true, 'quality' => 80],
            ],
            'slideshow' => [
                'template' => '@NyroDevNyroCms/Composer/block_slideshow.html.php',
                'images' => [
                    'image' => true,
                    'multiple' => true,
                    'multipleFields' => [],
                    'big' => [
                        'w' => 1500,
                        'h' => 1000,
                        'name' => 'big',
                        'fit' => true,
                        'quality' => 80,
                    ],
                    'thumb' => [
                        'w' => 100,
                        'h' => 100,
                        'name' => 'thumb',
                        'fit' => true,
                        'quality' => 80,
                    ],
                ],
            ],
            'video' => [
                'template' => '@NyroDevNyroCms/Composer/block_video.html.php',
                'url' => [
                    'treatAsMedia' => true,
                    'linkedFields' => [
                        'embed',
                        'autoplay',
                    ],
                ],
            ],
            'handler' => [
                'template' => '@NyroDevNyroCms/Composer/block_handler.html.php',
            ],
            'separator' => [
                'template' => '@NyroDevNyroCms/Composer/block_separator.html.php',
            ],
        ];

        $defaultTinymce = [
            'plugins' => 'lists,advlist,anchor,autolink,link,image,charmap,preview,hr,searchreplace,visualblocks,visualchars,code,fullscreen,insertdatetime,media,nonbreaking,table,paste,contextmenu,tabfocus,wordcount',
            'menubar' => 'insert edit view table tools',
            'toolbar' => 'undo redo | styleselect fontsizeselect removeformat | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link unlink | image media fullpage',
            'style_formats' => [
                ['title' => 'admin.composer.tinymce.styleFormats.blocks', 'items' => [
                    ['title' => 'admin.composer.tinymce.styleFormats.block.title1', 'block' => 'h1'],
                    ['title' => 'admin.composer.tinymce.styleFormats.block.title2', 'block' => 'h2'],
                    ['title' => 'admin.composer.tinymce.styleFormats.block.title3', 'block' => 'h3'],
                    ['title' => 'admin.composer.tinymce.styleFormats.block.title4', 'block' => 'h4'],
                    ['title' => 'admin.composer.tinymce.styleFormats.block.paragraph', 'block' => 'p'],
                ]],
                ['title' => 'admin.composer.tinymce.styleFormats.formatting', 'items' => [
                    ['title' => 'admin.composer.tinymce.styleFormats.formats.bold', 'icon' => 'bold', 'format' => 'bold'],
                    ['title' => 'admin.composer.tinymce.styleFormats.formats.italic', 'icon' => 'italic', 'format' => 'italic'],
                    ['title' => 'admin.composer.tinymce.styleFormats.formats.underline', 'icon' => 'underline', 'format' => 'underline'],
                    ['title' => 'admin.composer.tinymce.styleFormats.formats.strikethrough', 'icon' => 'strikethrough', 'format' => 'strikethrough'],
                    ['title' => 'admin.composer.tinymce.styleFormats.formats.superscript', 'icon' => 'superscript', 'format' => 'superscript'],
                    ['title' => 'admin.composer.tinymce.styleFormats.formats.subscript', 'icon' => 'subscript', 'format' => 'subscript'],
                    ['title' => 'admin.composer.tinymce.styleFormats.formats.code', 'icon' => 'code', 'format' => 'code'],
                ]],
            ],
        ];

        $defaultTinymceSimple = [
            'toolbar' => 'undo redo',
        ];

        $rootNode
            ->children()
                ->arrayNode('user_types')
                    ->defaultValue(['admin', 'superadmin'])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('route_resources')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('route_handler_path')->defaultValue('handler')->end()
                ->variableNode('disabled_locale_urls')->defaultValue(false)->end()
                ->arrayNode('model')->isRequired()
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
                                ->scalarNode('content_handler_config')->defaultValue('ContentHandlerConfig')->end()
                                ->scalarNode('content_handler_config_log')->defaultValue('Log\\ContentHandlerConfigLog')->end()
                                ->scalarNode('content_handler_config_translation')->defaultValue('Translation\\ContentHandlerConfigTranslation')->end()
                                ->scalarNode('translation')->defaultValue('Translation')->end()
                                ->scalarNode('contact_message')->defaultValue('ContactMessage')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('content')->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('maxlevel')->defaultValue(4)->end()
                        ->booleanNode('admin_per_root')->defaultFalse()->end()
                        ->booleanNode('root_composer')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('user_roles')->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('maxlevel_content')->defaultValue(3)->end()
                    ->end()
                ->end()
                ->arrayNode('email')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('global_template')->defaultValue('@NyroDevNyroCms/Tpl/email.html.php')->end()
                        ->scalarNode('styles_template')->defaultValue('@NyroDevNyroCms/Tpl/emailStyles.html.php')->end()
                        ->scalarNode('body_template')->defaultValue('@NyroDevNyroCms/Tpl/emailBody.html.php')->end()
                        ->scalarNode('router_scheme')->defaultValue('http')->end()
                        ->scalarNode('router_host')->defaultValue('localhost')->end()
                        ->scalarNode('router_base_url')->defaultValue('')->end()
                    ->end()
                ->end()
                ->arrayNode('composable')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('default')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('change_lang')->defaultTrue()->end()
                                ->booleanNode('same_lang_structure')->defaultFalse()->end()
                                ->booleanNode('same_lang_media')->defaultFalse()->end()
                                ->booleanNode('change_theme')->defaultTrue()->end()
                                ->scalarNode('global_composer_template')->defaultValue('@NyroDevNyroCms/Composer/composer.html.php')->end()
                                ->scalarNode('composer_template')->defaultValue('@NyroDevNyroCms/Composer/composerTemplate.html.php')->end()
                                ->scalarNode('block_template')->defaultValue('@NyroDevNyroCms/Composer/block.html.php')->end()
                                ->scalarNode('css_template')->defaultValue('Front/NyroCms/cssTemplate.html.php')->end()
                                ->integerNode('max_composer_buttons')->defaultValue(10)->end()
                                ->arrayNode('cancel_url')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('route')->defaultValue('nyrocms_admin_data_content_tree')->end()
                                        ->booleanNode('need_id')->defaultFalse()->end()
                                        ->booleanNode('need_veryParent_id')->defaultTrue()->end()
                                        ->variableNode('route_prm')->defaultValue([])->end()
                                    ->end()
                                ->end()
                                ->arrayNode('themes')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('available_blocks')
                                    ->defaultValue(['intro', 'text', 'column2', 'column3', 'image_text', 'text_image', 'image', 'image2', 'image3', 'slideshow', 'video', 'separator'])
                                    ->prototype('scalar')->end()
                                ->end()
                                ->booleanNode('tinymce_browser')->defaultTrue()->end()
                                ->booleanNode('tinymce_browser_per_root')->defaultTrue()->end()
                                ->scalarNode('tinymce_browser_route')->defaultValue('nyrocms_admin_tinymce_browser')->end()
                                ->scalarNode('tinymce_browser_route_per_root')->defaultValue('nyrocms_admin_tinymce_browser_dirname')->end()
                                ->arrayNode('tinymce')
                                    ->defaultValue($defaultTinymce)
                                    ->beforeNormalization()
                                        ->always(function ($config) use ($defaultTinymce) {
                                            $ret = $defaultTinymce;
                                            foreach ($config as $k => $v) {
                                                if (is_array($v)) {
                                                    $hasReplace = array_search('REPLACE', $v);
                                                    if (false !== $hasReplace) {
                                                        unset($v[$hasReplace]);
                                                    }
                                                    $ret[$k] = !isset($ret[$k]) || false !== $hasReplace ? $v : ('style_formats' == $k ? array_merge($ret[$k], $v) : array_replace_recursive($ret[$k], $v));
                                                } else {
                                                    $ret[$k] = $v;
                                                }
                                            }

                                            return $ret;
                                        })
                                    ->end()
                                    ->prototype('variable')->end()
                                ->end()
                                ->arrayNode('tinymce_simple')
                                    ->defaultValue($defaultTinymceSimple)
                                    ->beforeNormalization()
                                        ->always(function ($config) use ($defaultTinymceSimple) {
                                            $ret = $defaultTinymceSimple;
                                            foreach ($config as $k => $v) {
                                                if (is_array($v)) {
                                                    $hasReplace = array_search('REPLACE', $v);
                                                    if (false !== $hasReplace) {
                                                        unset($v[$hasReplace]);
                                                    }
                                                    $ret[$k] = !isset($ret[$k]) || false !== $hasReplace ? $v : ('style_formats' == $k ? array_merge($ret[$k], $v) : array_replace_recursive($ret[$k], $v));
                                                } else {
                                                    $ret[$k] = $v;
                                                }
                                            }

                                            return $ret;
                                        })
                                    ->end()
                                    ->prototype('variable')->end()
                                ->end()
                                ->arrayNode('default_blocks')
                                    ->defaultValue($defaultBlocks)
                                    ->useAttributeAsKey('name')
                                    ->beforeNormalization()
                                        ->always(function ($config) use ($defaultBlocks) {
                                            $ret = $defaultBlocks;
                                            foreach ($config as $k => $v) {
                                                if (is_array($v)) {
                                                    $hasReplace = array_search('REPLACE', $v);
                                                    if (false !== $hasReplace) {
                                                        unset($v[$hasReplace]);
                                                    }
                                                    $ret[$k] = !isset($ret[$k]) || false !== $hasReplace ? $v : array_replace_recursive($ret[$k], $v);
                                                } else {
                                                    $ret[$k] = $v;
                                                }
                                            }

                                            return $ret;
                                        })
                                    ->end()
                                    ->prototype('variable')->end()
                                ->end()
                                ->arrayNode('config_blocks')
                                    ->defaultValue($defaultConfigs)
                                    ->useAttributeAsKey('name')
                                    ->beforeNormalization()
                                        ->always(function ($config) use ($defaultConfigs) {
                                            $ret = $defaultConfigs;
                                            foreach ($config as $k => $v) {
                                                if (is_array($v)) {
                                                    $hasReplace = array_search('REPLACE', $v);
                                                    if (false !== $hasReplace) {
                                                        unset($v[$hasReplace]);
                                                    }
                                                    $ret[$k] = !isset($ret[$k]) || false !== $hasReplace ? $v : array_replace_recursive($ret[$k], $v);
                                                } else {
                                                    $ret[$k] = $v;
                                                }
                                            }

                                            return $ret;
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
                                    ->booleanNode('same_lang_structure')->end()
                                    ->booleanNode('same_lang_media')->end()
                                    ->booleanNode('change_theme')->end()
                                    ->scalarNode('global_composer_template')->end()
                                    ->scalarNode('composer_template')->end()
                                    ->scalarNode('block_template')->end()
                                    ->scalarNode('css_template')->end()
                                    ->arrayNode('cancel_url')
                                        ->children()
                                            ->scalarNode('route')->end()
                                            ->booleanNode('need_id')->end()
                                            ->booleanNode('need_veryParent_id')->end()
                                            ->variableNode('route_prm')->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('themes')->prototype('scalar')->end()->end()
                                    ->arrayNode('available_blocks')->prototype('scalar')->end()->end()
                                    ->arrayNode('tinymce')
                                        ->prototype('variable')->end()
                                    ->end()
                                    ->arrayNode('tinymce_simple')
                                        ->prototype('variable')->end()
                                    ->end()
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

        return $builder;
    }
}
