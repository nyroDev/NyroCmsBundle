<?php

namespace NyroDev\NyroCmsBundle\DependencyInjection;

use NyroDev\NyroCmsBundle\Services\ComposerService;
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
            ComposerService::BLOCK_FULL => [
                'template' => '@NyroDevNyroCms/Composer/block/full.html.php',
                'icon' => 'block_full',
                'nb_containers' => 1,
                'addable' => true,
                'widthContainers' => [
                    [
                        'dims' => '1300x1300',
                        'sizes' => '(min-width: 1300px) 1300px, 100vw',
                        'srcset' => [
                            [
                                'dims' => '1300x1300',
                                'width' => '1300w',
                            ],
                            [
                                'dims' => '800x800',
                                'width' => '800w',
                            ],
                            [
                                'dims' => '400x400',
                                'width' => '400w',
                            ],
                        ],
                    ],
                ],
            ],
            'two' => [
                'template' => '@NyroDevNyroCms/Composer/block/two.html.php',
                'icon' => 'block_two',
                'nb_containers' => 2,
                'addable' => true,
                'widthContainers' => [
                    // Defiing only 1 containers meeans all containers will have the same width
                    [
                        'dims' => '650x650',
                        'sizes' => '(min-width: 1300px) 650px, (min-width: 900px) 50vw, 100vw',
                        'srcset' => [
                            [
                                'dims' => '800x800',
                                'width' => '800w',
                            ],
                            [
                                'dims' => '650x650',
                                'width' => '650w',
                            ],
                            [
                                'dims' => '400x400',
                                'width' => '400w',
                            ],
                        ],
                    ],
                ],
            ],
            'two_1_2' => [
                'template' => '@NyroDevNyroCms/Composer/block/two_1_2.html.php',
                'icon' => 'block_two_1_2',
                'nb_containers' => 2,
                'addable' => true,
                'widthContainers' => [
                    [
                        'dims' => '433x433',
                        'sizes' => '(min-width: 1300px) 433px, (min-width: 900px) 33vw, 100vw',
                        'srcset' => [
                            [
                                'dims' => '800x800',
                                'width' => '800w',
                            ],
                            [
                                'dims' => '650x650',
                                'width' => '650w',
                            ],
                            [
                                'dims' => '400x400',
                                'width' => '400w',
                            ],
                            [
                                'dims' => '320x320',
                                'width' => '320w',
                            ],
                        ],
                    ],
                    [
                        'dims' => '866x866',
                        'sizes' => '(min-width: 1300px) 866px, (min-width: 900px) 66vw, 100vw',
                        'srcset' => [
                            [
                                'dims' => '866x866',
                                'width' => '866w',
                            ],
                            [
                                'dims' => '650x650',
                                'width' => '650w',
                            ],
                            [
                                'dims' => '400x400',
                                'width' => '400w',
                            ],
                            [
                                'dims' => '320x320',
                                'width' => '320w',
                            ],
                        ],
                    ],
                ],
            ],
            'two_2_1' => [
                'template' => '@NyroDevNyroCms/Composer/block/two_2_1.html.php',
                'icon' => 'block_two_2_1',
                'nb_containers' => 2,
                'addable' => true,
                'widthContainers' => [
                    [
                        'dims' => '866x866',
                        'sizes' => '(min-width: 1300px) 866px, (min-width: 900px) 66vw, 100vw',
                        'srcset' => [
                            [
                                'dims' => '866x866',
                                'width' => '866w',
                            ],
                            [
                                'dims' => '650x650',
                                'width' => '650w',
                            ],
                            [
                                'dims' => '400x400',
                                'width' => '400w',
                            ],
                            [
                                'dims' => '320x320',
                                'width' => '320w',
                            ],
                        ],
                    ],
                    [
                        'dims' => '433x433',
                        'sizes' => '(min-width: 1300px) 433px, (min-width: 900px) 33vw, 100vw',
                        'srcset' => [
                            [
                                'dims' => '800x800',
                                'width' => '800w',
                            ],
                            [
                                'dims' => '650x650',
                                'width' => '650w',
                            ],
                            [
                                'dims' => '400x400',
                                'width' => '400w',
                            ],
                            [
                                'dims' => '320x320',
                                'width' => '320w',
                            ],
                        ],
                    ],
                ],
            ],
            'three' => [
                'template' => '@NyroDevNyroCms/Composer/block/three.html.php',
                'icon' => 'block_three',
                'nb_containers' => 3,
                'addable' => true,
                'widthContainers' => [
                    // Defiing only 1 containers meeans all containers will have the same width
                    [
                        'dims' => '433x433',
                        'sizes' => '(min-width: 1300px) 433px, (min-width: 900px) 33vw, 100vw',
                        'srcset' => [
                            [
                                'dims' => '800x800',
                                'width' => '800w',
                            ],
                            [
                                'dims' => '650x650',
                                'width' => '650w',
                            ],
                            [
                                'dims' => '400x400',
                                'width' => '400w',
                            ],
                            [
                                'dims' => '320x320',
                                'width' => '320w',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $defaultItems = [
            'title' => [
                'template' => '@NyroDevNyroCms/Composer/item/title.html.php',
                'icon' => 'item_title',
                'addable' => true,
                'editables' => [
                    'level' => [
                        'selector' => 'h1, h2, h3, h4, h5, h6',
                        'type' => ComposerService::EDITABLE_TYPE_DOM,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_RADIO,
                        'dataOptions' => [
                            'h1',
                            'h2',
                            'h3',
                            'h4',
                            'h5',
                            'h6',
                        ],
                        'default' => 'h1',
                        'auto' => false,
                    ],
                    'title' => [
                        'selector' => 'h1, h2, h3, h4, h5, h6',
                        'type' => ComposerService::EDITABLE_TYPE_SIMPLE_TEXT,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_TEXT,
                        'default' => null,
                        'auto' => false,
                    ],
                ],
            ],
            'text' => [
                'template' => '@NyroDevNyroCms/Composer/item/text.html.php',
                'icon' => 'item_text',
                'addable' => true,
                'editables' => [
                    'text' => [
                        'selector' => 'div.text',
                        'type' => ComposerService::EDITABLE_TYPE_TEXT,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_TEXT,
                        'default' => null,
                        'auto' => false,
                    ],
                ],
            ],
            'image' => [
                'template' => '@NyroDevNyroCms/Composer/item/image.html.php',
                'icon' => 'item_image',
                'addable' => true,
                'editables' => [
                    'src' => [
                        'selector' => 'img',
                        'type' => ComposerService::EDITABLE_TYPE_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_IMAGE,
                        'default' => null,
                        'auto' => false,
                    ],
                    'alt' => [
                        'selector' => 'img',
                        'type' => ComposerService::EDITABLE_TYPE_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_TEXT,
                        'default' => 'image',
                        'auto' => false,
                    ],
                    'width' => [
                        'selector' => 'img',
                        'type' => ComposerService::EDITABLE_TYPE_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_NUMBER,
                        'default' => 100,
                        'auto' => true,
                    ],
                    'height' => [
                        'selector' => 'img',
                        'type' => ComposerService::EDITABLE_TYPE_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_NUMBER,
                        'default' => 100,
                        'auto' => true,
                    ],
                ],
            ],
            'slideshow' => [
                'template' => '@NyroDevNyroCms/Composer/item/slideshow.html.php',
                'icon' => 'item_slideshow',
                'addable' => true,
                'editables' => [
                    'images' => [
                        'selector' => 'nyro-swiper',
                        'type' => ComposerService::EDITABLE_TYPE_DOM,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_IMAGES,
                        'default' => null,
                        'auto' => false,
                    ],
                ],
            ],
            'videoEmbed' => [
                'template' => '@NyroDevNyroCms/Composer/item/videoEmbed.html.php',
                'icon' => 'item_videoEmbed',
                'addable' => true,
                'editables' => [
                    'url' => [
                        'selector' => 'iframe',
                        'type' => ComposerService::EDITABLE_TYPE_DATA_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_VIDEO_URL,
                        'default' => null,
                        'auto' => false,
                    ],
                    'src' => [
                        'selector' => 'iframe',
                        'type' => ComposerService::EDITABLE_TYPE_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_VIDEO_EMBED,
                        'default' => null,
                        'auto' => true,
                    ],
                    'autoplay' => [
                        'selector' => 'iframe',
                        'type' => ComposerService::EDITABLE_TYPE_DATA_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_BOOLEAN,
                        'default' => true,
                        'auto' => true,
                    ],
                    'aspectRatio' => [
                        'selector' => 'iframe',
                        'type' => ComposerService::EDITABLE_TYPE_STYLE,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_TEXT,
                        'default' => '16/9',
                        'auto' => true,
                    ],
                ],
            ],
            'iframe' => [
                'template' => '@NyroDevNyroCms/Composer/item/iframe.html.php',
                'icon' => 'item_iframe',
                'addable' => true,
                'editables' => [
                    'url' => [
                        'selector' => 'iframe',
                        'type' => ComposerService::EDITABLE_TYPE_DATA_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_IFRAME_URL,
                        'default' => null,
                        'auto' => false,
                    ],
                    'src' => [
                        'selector' => 'iframe',
                        'type' => ComposerService::EDITABLE_TYPE_ATTR,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_VIDEO_EMBED,
                        'default' => null,
                        'auto' => true,
                    ],
                    'aspectRatio' => [
                        'selector' => 'iframe',
                        'type' => ComposerService::EDITABLE_TYPE_STYLE,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_TEXT,
                        'default' => '1',
                        'auto' => true,
                    ],
                ],
            ],
            'separator' => [
                'template' => '@NyroDevNyroCms/Composer/item/separator.html.php',
                'icon' => 'item_separator',
                'addable' => true,
                'editables' => [
                    'space' => [
                        'selector' => 'hr',
                        'type' => ComposerService::EDITABLE_TYPE_CLASS,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_RADIO,
                        'dataOptions' => [
                            'small',
                            'medium',
                            'big',
                        ],
                        'default' => 'small',
                        'auto' => false,
                    ],
                ],
            ],
            'spacer' => [
                'template' => '@NyroDevNyroCms/Composer/item/spacer.html.php',
                'icon' => 'item_spacer',
                'addable' => true,
                'editables' => [
                    'space' => [
                        'selector' => 'hr',
                        'type' => ComposerService::EDITABLE_TYPE_CLASS,
                        'dataType' => ComposerService::EDITABLE_DATATYPE_RADIO,
                        'dataOptions' => [
                            'small',
                            'medium',
                            'big',
                        ],
                        'default' => 'small',
                        'auto' => false,
                    ],
                ],
            ],
            ComposerService::ITEM_HANDLER => [
                'template' => '@NyroDevNyroCms/Composer/item/handler.html.php',
                'icon' => 'item_handler',
                'addable' => true,
                'editables' => [],
            ],
        ];

        $availableItems = array_filter(array_keys($defaultItems), function ($item) {
            return ComposerService::ITEM_HANDLER !== $item;
        });

        $defaultTinymce = [
            'plugins' => 'anchor,autolink,charmap,code,fullscreen,image,emoticons,insertdatetime,link,lists,advlist,media,nonbreaking,preview,searchreplace,table,visualblocks,visualchars,wordcount',
            'menubar' => 'insert edit view table tools',
            'removed_menuitems' => 'image media preview',
            'toolbar' => 'undo redo | link unlink | styles | bold italic | removeformat | alignleft aligncenter alignright alignjustify | fullscreen | bullist numlist outdent indent',
            'paste_block_drop' => 'true',
            'style_formats' => [
                ['title' => 'admin.composer.tinymce.styleFormats.formats.bold', 'icon' => 'bold', 'format' => 'bold'],
                ['title' => 'admin.composer.tinymce.styleFormats.formats.italic', 'icon' => 'italic', 'format' => 'italic'],
                ['title' => 'admin.composer.tinymce.styleFormats.formats.underline', 'icon' => 'underline', 'format' => 'underline'],
                ['title' => 'admin.composer.tinymce.styleFormats.formats.strikethrough', 'icon' => 'strikethrough', 'format' => 'strikethrough'],
                ['title' => 'admin.composer.tinymce.styleFormats.formats.superscript', 'icon' => 'superscript', 'format' => 'superscript'],
                ['title' => 'admin.composer.tinymce.styleFormats.formats.subscript', 'icon' => 'subscript', 'format' => 'subscript'],
                ['title' => 'admin.composer.tinymce.styleFormats.formats.code', 'icon' => 'code', 'format' => 'code'],
            ],
            'skin' => 'tinymce-5',
            'license_key' => 'gpl',
        ];

        $defaultTinymceSimple = [
            'toolbar' => 'undo redo',
            'paste_block_drop' => 'true',
            'skin' => 'tinymce-5',
            'license_key' => 'gpl',
        ];

        $rootNode
            ->children()
                ->arrayNode('user_types')
                    ->defaultValue(['admin', 'superadmin'])
                    ->prototype('string')->end()
                ->end()
                ->arrayNode('route_resources')
                    ->prototype('string')->end()
                ->end()
                ->stringNode('route_handler_path')->defaultValue('handler')->end()
                ->variableNode('disabled_locale_urls')->defaultValue(false)->end()
                ->arrayNode('model')->isRequired()
                    ->children()
                        ->stringNode('namespace')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('classes')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->stringNode('user')->defaultValue('User')->end()
                                ->stringNode('user_log')->defaultValue('Log\\UserLog')->end()
                                ->stringNode('user_role')->defaultValue('UserRole')->end()
                                ->stringNode('user_login')->defaultValue('UserLogin')->end()
                                ->stringNode('content')->defaultValue('Content')->end()
                                ->stringNode('content_log')->defaultValue('Log\\ContentLog')->end()
                                ->stringNode('content_translation')->defaultValue('Translation\\ContentTranslation')->end()
                                ->stringNode('content_spec')->defaultValue('ContentSpec')->end()
                                ->stringNode('content_spec_log')->defaultValue('Log\\ContentSpecLog')->end()
                                ->stringNode('content_spec_translation')->defaultValue('Translation\\ContentSpecTranslation')->end()
                                ->stringNode('content_handler')->defaultValue('ContentHandler')->end()
                                ->stringNode('content_handler_config')->defaultValue('ContentHandlerConfig')->end()
                                ->stringNode('content_handler_config_log')->defaultValue('Log\\ContentHandlerConfigLog')->end()
                                ->stringNode('content_handler_config_translation')->defaultValue('Translation\\ContentHandlerConfigTranslation')->end()
                                ->stringNode('template_category')->defaultValue('TemplateCategory')->end()
                                ->stringNode('template')->defaultValue('Template')->end()
                                ->stringNode('template_log')->defaultValue('Log\\TemplateLog')->end()
                                ->stringNode('tooltip')->defaultValue('Tooltip')->end()
                                ->stringNode('tooltip_log')->defaultValue('Log\\TooltipLog')->end()
                                ->stringNode('translation')->defaultValue('Translation')->end()
                                ->stringNode('contact_message')->defaultValue('ContactMessage')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('content')->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('maxlevel')->min(0)->defaultValue(4)->end()
                        ->booleanNode('admin_per_root')->defaultFalse()->end()
                        ->booleanNode('root_composer')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('user_roles')->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('maxlevel_content')->min(0)->defaultValue(3)->end()
                    ->end()
                ->end()
                ->arrayNode('email')->addDefaultsIfNotSet()
                    ->children()
                        ->stringNode('router_scheme')->defaultValue('http')->end()
                        ->stringNode('router_host')->defaultValue('localhost')->end()
                        ->stringNode('router_base_url')->defaultValue('')->end()
                    ->end()
                ->end()
                ->arrayNode('composable_blocks')
                    ->defaultValue($defaultBlocks)
                    ->beforeNormalization()
                        ->always(function ($config) use ($defaultBlocks) {
                            $ret = $defaultBlocks;
                            foreach ($config as $k => $v) {
                                if (!isset($ret[$k])) {
                                    $ret[$k] = $v;
                                    continue;
                                }
                                $ret[$k] = array_merge($ret[$k], $v);
                            }

                            return $ret;
                        })
                    ->end()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->performNoDeepMerging()
                        ->children()
                            ->stringNode('name')->cannotBeEmpty()->end()
                            ->stringNode('template')->isRequired()->cannotBeEmpty()->end()
                            ->stringNode('icon')->isRequired()->end()
                            ->booleanNode('addable')->defaultTrue()->end()
                            ->integerNode('nb_containers')->defaultValue(0)->min(0)->end()
                            ->arrayNode('widthContainers')
                                ->arrayPrototype()
                                    ->children()
                                        ->stringNode('dims')->isRequired()->cannotBeEmpty()->end()
                                        ->stringNode('sizes')->defaultNull()->end()
                                        ->arrayNode('srcset')
                                            ->arrayPrototype()
                                                ->children()
                                                    ->stringNode('dims')->isRequired()->cannotBeEmpty()->end()
                                                    ->stringNode('width')->isRequired()->cannotBeEmpty()->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('composable_items')
                    ->defaultValue($defaultItems)
                    ->beforeNormalization()
                        ->always(function ($config) use ($defaultItems) {
                            $ret = $defaultItems;
                            foreach ($config as $k => $v) {
                                if (!isset($ret[$k])) {
                                    $ret[$k] = $v;
                                    continue;
                                }
                                $ret[$k] = array_merge($ret[$k], $v);
                            }

                            return $ret;
                        })
                    ->end()
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->stringNode('name')->cannotBeEmpty()->end()
                            ->stringNode('template')->isRequired()->cannotBeEmpty()->end()
                            ->stringNode('icon')->isRequired()->end()
                            ->booleanNode('addable')->defaultTrue()->end()
                            ->arrayNode('editables')
                            ->performNoDeepMerging()
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->addDefaultsIfNotSet()
                                    ->performNoDeepMerging()
                                    ->children()
                                        ->stringNode('name')->cannotBeEmpty()->end()
                                        ->stringNode('selector')->isRequired()->cannotBeEmpty()->end()
                                        ->enumNode('type')->values(ComposerService::EDITABLE_TYPES)->isRequired()->cannotBeEmpty()->end()
                                        ->enumNode('dataType')->values(ComposerService::EDITABLE_DATATYPES)->defaultValue(ComposerService::EDITABLE_DATATYPE_TEXT)->cannotBeEmpty()->end()
                                        ->arrayNode('dataOptions')
                                            ->scalarPrototype()->end()
                                        ->end()
                                        ->scalarNode('default')->defaultNull()->end()
                                        ->booleanNode('auto')->defaultFalse()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
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
                                ->stringNode('composer_template')->defaultValue('@NyroDevNyroCms/Composer/composer.html.php')->end()
                                ->stringNode('block_template')->defaultValue('@NyroDevNyroCms/Composer/block.html.php')->end()
                                ->stringNode('css_template')->defaultValue('Front/NyroCms/cssTemplate.html.php')->end()
                                ->arrayNode('cancel_url')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->stringNode('route')->defaultValue('nyrocms_admin_data_content_tree')->end()
                                        ->booleanNode('need_id')->defaultFalse()->end()
                                        ->booleanNode('need_veryParent_id')->defaultTrue()->end()
                                        ->variableNode('route_prm')->defaultValue([])->end()
                                    ->end()
                                ->end()
                                ->arrayNode('themes')
                                    ->prototype('string')->end()
                                ->end()
                                ->arrayNode('available_blocks')
                                    ->defaultValue(array_keys($defaultBlocks))
                                    ->prototype('string')->end()
                                ->end()
                                ->arrayNode('available_items')
                                    ->defaultValue($availableItems)
                                    ->prototype('string')->end()
                                ->end()
                                ->booleanNode('tinymce_browser')->defaultTrue()->end()
                                ->booleanNode('tinymce_browser_per_root')->defaultTrue()->end()
                                ->stringNode('tinymce_browser_route')->defaultValue('nyrocms_admin_tinymce_browser')->end()
                                ->stringNode('tinymce_browser_route_per_root')->defaultValue('nyrocms_admin_tinymce_browser_dirname')->end()
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
                            ->end()
                        ->end()
                        ->arrayNode('classes')
                            ->useAttributeAsKey('class')
                            ->arrayPrototype()
                                ->children()
                                    ->booleanNode('change_lang')->end()
                                    ->booleanNode('same_lang_structure')->end()
                                    ->booleanNode('same_lang_media')->end()
                                    ->booleanNode('change_theme')->end()
                                    ->stringNode('composer_template')->end()
                                    ->stringNode('block_template')->end()
                                    ->stringNode('css_template')->end()
                                    ->arrayNode('cancel_url')
                                        ->children()
                                            ->stringNode('route')->end()
                                            ->booleanNode('need_id')->end()
                                            ->booleanNode('need_veryParent_id')->end()
                                            ->variableNode('route_prm')->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('themes')->prototype('string')->end()->end()
                                    ->arrayNode('available_blocks')->prototype('string')->end()->end()
                                    ->arrayNode('available_items')->prototype('string')->end()->end()
                                    ->arrayNode('tinymce')
                                        ->prototype('variable')->end()
                                    ->end()
                                    ->arrayNode('tinymce_simple')
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
