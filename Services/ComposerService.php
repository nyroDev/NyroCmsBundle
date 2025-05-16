<?php

namespace NyroDev\NyroCmsBundle\Services;

use Exception;
use NyroDev\NyroCmsBundle\Event\ComposerConfigEvent;
use NyroDev\NyroCmsBundle\Event\ComposerDefaultBlockEvent;
use NyroDev\NyroCmsBundle\Event\ComposerEvent;
use NyroDev\NyroCmsBundle\Event\TinymceConfigEvent;
use NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent;
use NyroDev\NyroCmsBundle\Handler\AbstractHandler;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\ComposableContentSummary;
use NyroDev\NyroCmsBundle\Model\ComposableHandler;
use NyroDev\NyroCmsBundle\Model\ComposableTranslatable;
use NyroDev\NyroCmsBundle\Model\ContentRootable;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Model\Template;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\UtilityBundle\Services\ImageService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use NyroDev\UtilityBundle\Services\Traits\TwigServiceableTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ComposerService extends AbstractService
{
    use AssetsPackagesServiceableTrait;
    use TwigServiceableTrait;

    public const BLOCK_FULL = 'full';
    public const ITEM_HANDLER = 'handler';

    public const READONLY = 'readonly';

    public const TEMPLATE = '_template';

    public const EDITABLE_TYPE_SIMPLE_TEXT = 'simpleText';
    public const EDITABLE_TYPE_TEXT = 'text';
    public const EDITABLE_TYPE_CLASS = 'class';
    public const EDITABLE_TYPE_ATTR = 'attr';
    public const EDITABLE_TYPE_DATA_ATTR = 'dataAttr';
    public const EDITABLE_TYPE_DOM = 'dom';

    public const EDITABLE_TYPES = [
        self::EDITABLE_TYPE_SIMPLE_TEXT,
        self::EDITABLE_TYPE_TEXT,
        self::EDITABLE_TYPE_CLASS,
        self::EDITABLE_TYPE_ATTR,
        self::EDITABLE_TYPE_DATA_ATTR,
        self::EDITABLE_TYPE_DOM,
    ];

    public const EDITABLE_DATATYPE_TEXT = 'text';
    public const EDITABLE_DATATYPE_NUMBER = 'number';
    public const EDITABLE_DATATYPE_BOOLEAN = 'boolean';
    public const EDITABLE_DATATYPE_RADIO = 'radio';
    public const EDITABLE_DATATYPE_SELECT = 'select';
    public const EDITABLE_DATATYPE_IMAGE = 'image';
    public const EDITABLE_DATATYPE_FILE = 'file';
    public const EDITABLE_DATATYPE_IMAGES = 'images';
    public const EDITABLE_DATATYPE_VIDEO_URL = 'videoUrl';
    public const EDITABLE_DATATYPE_VIDEO_EMBED = 'videoEmbed';

    public const EDITABLE_DATATYPES = [
        self::EDITABLE_DATATYPE_TEXT,
        self::EDITABLE_DATATYPE_NUMBER,
        self::EDITABLE_DATATYPE_BOOLEAN,
        self::EDITABLE_DATATYPE_RADIO,
        self::EDITABLE_DATATYPE_SELECT,
        self::EDITABLE_DATATYPE_IMAGE,
        self::EDITABLE_DATATYPE_FILE,
        self::EDITABLE_DATATYPE_IMAGES,
        self::EDITABLE_DATATYPE_VIDEO_URL,
        self::EDITABLE_DATATYPE_VIDEO_EMBED,
    ];

    public function __construct(
        private readonly NyrodevService $nyrodevService,
        private readonly ImageService $imageService,
        private readonly NyroCmsService $nyroCmsService,
        private readonly DbAbstractService $dbService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected array $configs = [];

    public function getConfig(Composable $row): array
    {
        $class = get_class($row);
        if (!isset($this->configs[$class])) {
            $composableConfig = $this->getParameter('nyrocms.composable');

            $ret = isset($composableConfig[$class]) ? $composableConfig[$class] : [];
            $cfgArrays = ['themes', 'available_blocks', 'tinymce', 'tinymce_simple'];

            foreach ($cfgArrays as $cfg) {
                if (isset($ret[$cfg]) && 0 === count($ret[$cfg])) {
                    unset($ret[$cfg]);
                }
            }

            $this->configs[$class] = array_merge($composableConfig['default'], $ret);
        }

        return $this->configs[$class];
    }

    protected function getComposableConfig(string $type, string $name): array
    {
        $composableConfig = $this->getParameter('nyrocms.composable_'.$type);

        return $composableConfig[$name];
    }

    public function getQuickConfig(Composable $row, string $key): mixed
    {
        $cfg = $this->getConfig($row);
        $event = new ComposerConfigEvent($row, $key, isset($cfg[$key]) ? $cfg[$key] : null);
        $this->eventDispatcher->dispatch($event, ComposerConfigEvent::COMPOSER_CONFIG);

        return $event->getConfig();
    }

    public function canChangeLang(Composable $row): bool
    {
        return $row instanceof ComposableTranslatable && $this->getQuickConfig($row, 'change_lang');
    }

    public function isSameLangStructure(Composable $row): bool
    {
        return $row instanceof ComposableTranslatable && $this->getQuickConfig($row, 'same_lang_structure');
    }

    public function canChangeStructure(Composable $row): bool
    {
        if (!($row instanceof ComposableTranslatable)) {
            return false;
        }

        $isDefaultLocale = $this->nyroCmsService->getDefaultLocale($row) === $row->getTranslatableLocale();

        return $isDefaultLocale || !$this->isSameLangStructure($row);
    }

    public function isSameLangMedia(Composable $row): bool
    {
        return $row instanceof ComposableTranslatable && $this->getQuickConfig($row, 'same_lang_media');
    }

    public function canChangeMedia(Composable $row): bool
    {
        if (!($row instanceof ComposableTranslatable)) {
            return true;
        }

        $isDefaultLocale = $this->nyroCmsService->getDefaultLocale($row) === $row->getTranslatableLocale();

        return $isDefaultLocale || !$this->isSameLangMedia($row);
    }

    public function canChangeTheme(Composable $row): bool
    {
        return is_callable([$row, 'setTheme']) && $this->getQuickConfig($row, 'change_theme');
    }

    public function cssTemplate(Composable $row): string
    {
        return $this->getQuickConfig($row, 'css_template');
    }

    public function composerTemplate(Composable $row): string
    {
        return $this->getQuickConfig($row, 'composer_template');
    }

    public function getTinymceConfig(Composable $row, bool $simple = false): array
    {
        $cfg = $this->getQuickConfig($row, 'tinymce'.($simple ? '_simple' : ''));

        if (!$simple && $this->getQuickConfig($row, 'tinymce_browser')) {
            // Browser enable, add elements for it
            $cfg['plugins'] .= ',filemanager';
            $normalUrl = $this->generateUrl($this->getQuickConfig($row, 'tinymce_browser_route'), ['type' => '_TYPE_']);
            if ($this->getQuickConfig($row, 'tinymce_browser_per_root') && $row instanceof ContentRootable) {
                $url = $this->generateUrl($this->getQuickConfig($row, 'tinymce_browser_route_per_root'), [
                    'dirName' => 'tinymce_'.$row->getVeryParent()->getId(),
                    'type' => '_TYPE_',
                ]);
            } else {
                $url = $normalUrl;
            }
            $cfg['external_filemanager_path'] = $url.'/';
            $cfg['filemanager_title'] = $this->trans('nyrodev.browser.title');
        }

        $tinymceConfigEvent = new TinymceConfigEvent($row, $simple, $cfg);
        $this->eventDispatcher->dispatch($tinymceConfigEvent, TinymceConfigEvent::TINYMCE_CONFIG);

        return $this->tinymceAttrsTrRec($tinymceConfigEvent->getConfig());
    }

    public function cancelUrl(Composable $row): string
    {
        $ret = '#';
        if ($row instanceof ContentSpec) {
            $handler = $this->nyroCmsService->getHandler($row->getContentHandler());
            $ret = $this->nyrodevService->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
        } else {
            $cfg = $this->getConfig($row);
            $routePrm = isset($cfg['cancel_url']['route_prm']) && is_array($cfg['cancel_url']['route_prm']) ? $cfg['cancel_url']['route_prm'] : [];
            if ($cfg['cancel_url']['need_id']) {
                $routePrm['id'] = $row->getId();
            } elseif ($cfg['cancel_url']['need_veryParent_id'] && $row instanceof ContentRootable) {
                $routePrm['id'] = $row->getVeryParent()->getId();
            }
            $ret = $this->nyrodevService->generateUrl($cfg['cancel_url']['route'], $routePrm);
        }

        return $ret;
    }

    public function getThemes(Composable $row): array
    {
        $cfg = $this->getConfig($row);

        return $this->translateThemes($cfg['themes']);
    }

    public function getDefaultThemes(): array
    {
        $composableConfig = $this->getParameter('nyrocms.composable');

        return $this->translateThemes($composableConfig['default']['themes']);
    }

    private function translateThemes(array $themes): array
    {
        $ret = [];

        foreach ($themes as $theme) {
            $trIdent = 'admin.composable.themes.'.$theme;
            $tr = $this->trans($trIdent);
            $ret[$theme] = $tr && $tr != $trIdent ? $tr : ucfirst($theme);
        }

        return $ret;
    }

    public function getAvailableTemplates(Composable $row): array
    {
        $templates = [];

        foreach ($this->dbService->getTemplateRepository()->getAvailableTemplatesFor($row) as $tpl) {
            $templates[$tpl->getId()] = $tpl;
        }

        $event = new ComposerConfigEvent($row, 'available_templates', $templates);
        $this->eventDispatcher->dispatch($event, ComposerConfigEvent::COMPOSER_CONFIG);

        return $event->getConfig();
    }

    public function getTemplateDefaultFor(Composable $row): ?Template
    {
        return $this->dbService->getTemplateRepository()->getTemplateDefaultFor($row);
    }

    public function getAvailableBlocks(Composable $row): array
    {
        return $this->getQuickConfig($row, 'available_blocks');
    }

    public function getAvailableItems(Composable $row): array
    {
        return $this->getQuickConfig($row, 'available_items');
    }

    public function getCssTheme(Composable $row): ?string
    {
        if (!$row->getParent()) {
            return $row->getTheme();
        }

        return $row->getTheme() ? $row->getTheme() : $this->getCssTheme($row->getParent());
    }

    protected array $wrapperCssthemeEvents = [];

    public function getWrapperCssTheme(Composable $row, string $position = WrapperCssThemeEvent::POSITION_NORMAL): ?string
    {
        if (!isset($this->wrapperCssthemeEvents[$row->getId()])) {
            $this->wrapperCssthemeEvents[$row->getId()] = new WrapperCssThemeEvent($row);
            $this->eventDispatcher->dispatch($this->wrapperCssthemeEvents[$row->getId()], WrapperCssThemeEvent::WRAPPER_CSS_THEME);
        }

        return $this->wrapperCssthemeEvents[$row->getId()]->getWrapperCssTheme($position);
    }

    public function tinymceAttrs(Composable $row, string $prefix, bool $simple = false): array
    {
        $ret = [];
        foreach ($this->getTinymceConfig($row, $simple) as $k => $v) {
            $ret[$prefix.$k] = is_array($v) ? json_encode($v) : $v;
        }

        return $ret;
    }

    protected function tinymceAttrsTrRec(array $values): array
    {
        $ret = [];
        foreach ($values as $k => $v) {
            if (is_array($v)) {
                $ret[$k] = $this->tinymceAttrsTrRec($v);
            } elseif ('title' == $k) {
                $ret[$k] = $this->trans($v);
            } else {
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    public function renderBlockComposerTemplate(Composable $row, string $blockName): string
    {
        $cfg = $this->getComposableConfig('blocks', $blockName);
        unset($cfg['template']);

        return $this->getTwig()->render('@NyroDevNyroCms/Composer/_composerTemplate.html.php', [
            'type' => 'block',
            'id' => $blockName,
            'cfg' => $cfg,
            'row' => $row,
            'html' => $this->renderBlock($row, $blockName, admin: true),
        ])."\n\n";
    }

    private function renderBlock(Composable $row, string $blockName, ?array $cont = [], bool $admin = false): string
    {
        $config = $this->getComposableConfig('blocks', $blockName);

        $template = $config['template'];
        unset($config['template']);

        $conts = $cont['conts'] ?? [];

        // Ensure correct number of conts
        if (count($conts) > $config['nb_containers']) {
            $conts = array_slice($conts, 0, $config['nb_containers']);
        } elseif (count($conts) !== $config['nb_containers']) {
            for ($i = 0; $i < $config['nb_containers']; $i++) {
                if (!isset($conts[$i])) {
                    $conts[$i] = [];
                }
            }
        }

        $readonlyAttr = (isset($cont[ComposerService::READONLY]) && $cont[ComposerService::READONLY] ? ' readonly' : '');

        $renderedConts = [];
        foreach ($conts as $cont) {
            $itemsCont = [];

            foreach ($cont as $contItem) {
                $itemsCont[] = $this->renderItem($row, $contItem['_type'], $contItem, $admin);
            }

            $renderedCont = implode(PHP_EOL, $itemsCont);

            if ($admin) {
                $renderedCont = '<nyro-composer-container'.$readonlyAttr.'>'.$renderedCont.'</nyro-composer-container>';
            }

            $renderedConts[] = $renderedCont;
        }

        $ret = $this->getTwig()->render($template, [
            'conts' => $renderedConts,
            'row' => $row,
        ]);

        if ($admin) {
            $ret = '<nyro-composer-block type="'.$blockName.'"'.$readonlyAttr.'>'.$ret.'</nyro-composer-block>';
        }

        return $ret;
    }

    public function renderItemComposerTemplate(Composable $row, string $itemName): string
    {
        $cfg = $this->getComposableConfig('items', $itemName);
        unset($cfg['template']);

        return $this->getTwig()->render('@NyroDevNyroCms/Composer/_composerTemplate.html.php', [
            'type' => 'item',
            'id' => $itemName,
            'cfg' => $cfg,
            'row' => $row,
            'html' => $this->renderItem($row, $itemName, admin: true),
        ]).PHP_EOL.PHP_EOL;
    }

    private function renderItem(Composable $row, string $itemName, ?array $values = [], bool $admin = false): string
    {
        $config = $this->getComposableConfig('items', $itemName);

        $template = $config['template'];
        unset($config['template']);

        foreach ($config['editables'] as $editableName => $editable) {
            if (isset($values[$editableName])) {
                continue;
            }
            $default = $editable['default'] ?? null;
            if (!$default) {
                switch ($editable['type']) {
                    case self::EDITABLE_TYPE_SIMPLE_TEXT:
                        $default = 'simpleText';
                        break;
                    case self::EDITABLE_TYPE_TEXT:
                        $default = '<p>text</p>';
                        break;
                }
            }
            $values[$editableName] = $default;
        }

        $values['row'] = $row;

        $ret = $this->getTwig()->render($template, $values);

        if ($admin) {
            $readonlyAttr = (isset($values[ComposerService::READONLY]) && $values[ComposerService::READONLY] ? ' readonly' : '');
            $ret = '<nyro-composer-item type="'.$itemName.'"'.$readonlyAttr.'>'.$ret.'</nyro-composer-item>';
        }

        return $ret;
    }

    public function getRenderCssTheme(Composable $row): string
    {
        return implode(' ', array_filter([
            'composerTheme',
            'composerTheme_'.$this->getCssTheme($row),
            $this->getWrapperCssTheme($row),
        ]));
    }

    public function render(Composable $row, bool $admin = false): string
    {
        $content = $row->getContent();
        if (!$content || 0 === count($content)) {
            // Handle empty content only on admin
            if ($admin) {
                $content = [];
                if ($template = $this->getTemplateDefaultFor($row)) {
                    $content = $template->getContent();
                }

                $event = new ComposerDefaultBlockEvent($row, $content);
                $this->eventDispatcher->dispatch($event, ComposerDefaultBlockEvent::COMPOSER_DEFAULT_ADMIN_CONTENT);

                $content = $event->getContent();
            } else {
                $content = [];
            }
        }

        if ($row instanceof ComposableHandler && $row->getContentHandler()) {
            $hasHandlerPlaced = false;
            foreach ($content as $block) {
                if (isset($block['conts'])) {
                    foreach ($block['conts'] as $cont) {
                        foreach ($cont as $contItem) {
                            if (ComposerService::ITEM_HANDLER === $contItem['_type']) {
                                $hasHandlerPlaced = true;
                                break 3;
                            }
                        }
                    }
                }
            }

            if (!$hasHandlerPlaced) {
                $handler = $this->nyroCmsService->getHandler($row->getContentHandler());
                $content[] = $handler->getDefaultComposerBlock();
            }
        }

        $html = [];

        foreach ($content as $cont) {
            if (isset($cont[self::TEMPLATE])) {
                $html[] = '<input type="hidden" data-template="'.$cont[self::TEMPLATE].'" name="content[]" value="{&quot;'.self::TEMPLATE.'&quot;:&quot;'.$cont[self::TEMPLATE].'&quot;}"/>';
                continue;
            }
            $html[] = $this->renderBlock($row, $cont['_type'], $cont, $admin);
        }

        return implode(PHP_EOL.PHP_EOL, $html);
    }

    public function applyContent(Composable $row, array $content): void
    {
        $firstImage = null;
        $newTexts = [
            $row->getTitle(),
        ];

        foreach ($content as $block) {
            if (isset($block['conts'])) {
                foreach ($block['conts'] as $cont) {
                    foreach ($cont as $contItem) {
                        $itemCfg = $this->getComposableConfig('items', $contItem['_type']);
                        foreach ($itemCfg['editables'] as $name => $editable) {
                            $value = $contItem[$name] ?? null;
                            if (null === $value) {
                                continue;
                            }
                            switch ($editable['dataType']) {
                                case self::EDITABLE_DATATYPE_TEXT:
                                    $newTexts[] = $value;
                                    break;
                                case self::EDITABLE_DATATYPE_IMAGE:
                                    if (!$firstImage) {
                                        $firstImage = $value;
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }

        $row->setContent($content);
        if ($row instanceof ComposableContentSummary) {
            $row->setContentText(implode(PHP_EOL, $newTexts));
            $row->setFirstImage($firstImage);
        }
    }

    public function afterComposerEdition(Composable $row): void
    {
        // @todo refactor this function to work with new storage
        return;

        $canChangeLang = $this->canChangeLang($row);
        $langs = $this->nyroCmsService->getLocaleNames($row);

        $isDefaultLocale = !($row instanceof ComposableTranslatable) || $this->nyroCmsService->getDefaultLocale($row) === $row->getTranslatableLocale();

        $event = new ComposerEvent($row);
        $eventName = null;
        if ($isDefaultLocale || !$canChangeLang) {
            // We changed the default locale row
            $sameLangStructure = $this->isSameLangStructure($row);
            $sameLangMedia = $this->isSameLangMedia($row);

            if ($row instanceof ComposableTranslatable && ($sameLangStructure || $sameLangMedia)) {
                // We have to check if the structure or the media of already translated content need to be updated

                $contents = $this->idifyContents($row->getContent());

                $newContents = [];
                $cacheConfigMedia = [];

                foreach ($row->getTranslations() as $translation) {
                    if ('content' === $translation->getField() && $translation->getContent()) {
                        try {
                            $trContents = $this->idifyContents(json_decode($translation->getContent(), true));

                            $hasChange = false;

                            $importedIds = [];

                            if ($sameLangStructure) {
                                $newTrContents = [];
                                foreach ($contents as $id => $origContent) {
                                    if (!isset($trContents[$id])) {
                                        $hasChange = true;
                                        $importedIds[] = $id;
                                        $newTrContents[$id] = $origContent;
                                    } else {
                                        $newTrContents[$id] = $trContents[$id];
                                    }
                                }
                                $nbBefore = count($trContents);
                                $trContents = array_intersect_key($newTrContents, $contents);
                                if (count($trContents) != $nbBefore) {
                                    // Something was removed
                                    $hasChange = true;
                                }
                            }

                            if ($sameLangMedia) {
                                $checkIds = array_diff(array_keys($trContents), $importedIds);
                                foreach ($checkIds as $checkId) {
                                    if (isset($contents[$checkId])) {
                                        $origContent = $contents[$checkId];
                                        $trContent = $trContents[$checkId];

                                        if (!isset($cacheConfigMedia[$origContent['type']])) {
                                            $cfgMedia = [];
                                            $blockConfig = $this->getBlockConfig($row, $origContent['type']);
                                            foreach ($blockConfig as $k => $v) {
                                                if (is_array($v)) {
                                                    if (isset($v['image']) && $v['image']) {
                                                        if (isset($v['multiple']) && $v['multiple']) {
                                                            $cfgMedia[$k] = 'multiple';
                                                        } else {
                                                            $cfgMedia[$k] = true;
                                                        }
                                                    } elseif (isset($v['treatAsMedia']) && $v['treatAsMedia']) {
                                                        $cfgMedia[$k] = true;
                                                        if (isset($v['linkedFields']) && $v['linkedFields'] && is_array($v['linkedFields'])) {
                                                            foreach ($v['linkedFields'] as $linkedField) {
                                                                $cfgMedia[$linkedField] = true;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            $cacheConfigMedia[$origContent['type']] = $cfgMedia;
                                        }

                                        $blockConfigMedia = $cacheConfigMedia[$origContent['type']];

                                        if ($blockConfigMedia && is_array($blockConfigMedia) && count($blockConfigMedia)) {
                                            foreach ($blockConfigMedia as $k => $v) {
                                                if (isset($origContent['contents'][$k]) && $origContent['contents'][$k]) {
                                                    if ('multiple' === $v) {
                                                        $origImages = $this->idifyContents($origContent['contents'][$k]);
                                                        $trImages = $this->idifyContents($trContent['contents'][$k]);

                                                        $newImages = [];
                                                        foreach ($origImages as $id => $img) {
                                                            if (!isset($trImages[$id])) {
                                                                $hasChange = true;
                                                                $newImages[$id] = $img;
                                                            } else {
                                                                $newImages[$id] = $trImages[$id];
                                                                // check image change here
                                                                if ($newImages[$id]['file'] != $img['file']) {
                                                                    $newImages[$id]['file'] = $img['file'];
                                                                    $hasChange = true;
                                                                }
                                                            }
                                                        }

                                                        $nbBefore = count($trImages);
                                                        $trContent['contents'][$k] = array_values($newImages);
                                                        if (count($trContent['contents'][$k]) != $nbBefore) {
                                                            // Something was removed
                                                            $hasChange = true;
                                                        }
                                                    } elseif (!isset($trContent['contents'][$k]) || $trContent['contents'][$k] != $origContent['contents'][$k]) {
                                                        $trContent['contents'][$k] = $origContent['contents'][$k];
                                                        $hasChange = true;
                                                    }
                                                } elseif (isset($trContent['contents'][$k]) && $trContent['contents'][$k]) {
                                                    unset($trContent['contents'][$k]);
                                                    $hasChange = true;
                                                }
                                            }
                                        }

                                        $trContents[$checkId] = $trContent;
                                    }
                                }
                            }

                            if ($hasChange) {
                                $newContents[$translation->getLocale()] = $trContents;
                            }
                        } catch (Exception $e) {
                            // What to do here?
                        }
                    }
                }

                if (count($newContents)) {
                    // We have some lang changes to apply, let's do it
                    $om = $this->dbService->getObjectManager();
                    $curLocale = $row->getTranslatableLocale();

                    foreach ($newContents as $lg => $content) {
                        $row->setTranslatableLocale($lg);
                        $om->refresh($row);

                        $newConts = [];
                        $newTexts = [];
                        $firstImage = false;
                        foreach ($content as $id => $cont) {
                            $block = $this->getBlock($row, $id, $cont['type'], $cont['contents'], true);
                            foreach ($block['texts'] as $t) {
                                if (AbstractHandler::TEMPLATE_INDICATOR != $t) {
                                    $newTexts[] = html_entity_decode(strip_tags($t));
                                }
                            }
                            if (is_null($firstImage) && count($block['images']) && isset($block['images'][0])) {
                                $firstImage = $block['images'][0];
                            }
                            unset($block['texts']);
                            unset($block['images']);
                            unset($block['files']);
                            $newConts[] = $block;
                        }

                        $row->setContent($newConts);
                        if ($row instanceof ComposableContentSummary) {
                            $row->setContentText(implode("\n", $newTexts));
                            $row->setFirstImage($firstImage);
                        }

                        $om->flush();

                        $this->eventDispatcher->dispatch($event, ComposerEvent::COMPOSER_LANG_SAME);
                    }

                    $row->setTranslatableLocale($curLocale);
                    $om->refresh($row);
                }
            }

            $eventName = ComposerEvent::COMPOSER_DEFAULT;
        } else {
            // We changed a locale row, simply dipatch an event
            $eventName = ComposerEvent::COMPOSER_LANG;
        }

        if ($eventName) {
            $this->eventDispatcher->dispatch($event, $eventName);
        }
    }

    public function getSelectedTemplateId(Composable $row): ?string
    {
        $contents = $row->getContent();
        foreach ($contents as $content) {
            if (isset($content[self::TEMPLATE])) {
                return $content[self::TEMPLATE];
            }
        }

        return null;
    }

    public function applyTemplate(Template $template, Composable $row): void
    {
        $rowContents = $row->getContent();

        // Use template contents as new contents first
        $newContents = $template->getContent();

        if ($rowContents && count($rowContents)) {
            $filledContents = [];

            // First loop to fill block with exact matching (block type and items)
            foreach ($rowContents as $k => $rowContent) {
                if (0 === count(array_filter($rowContent['conts']))) {
                    // No content, ignore it
                    continue;
                }

                $blockItems = $rowContent['_type'].'_'.$this->computeblockItems($rowContent['conts']);

                foreach ($newContents as $kk => $newContent) {
                    if ($newContent['_type'].'_'.$this->computeblockItems($newContent['conts']) === $blockItems) {
                        $newContents[$kk] = $rowContent;
                        $filledContents[$kk] = true;
                        unset($rowContents[$k]);
                        continue 2;
                    }
                }
            }

            // Second loop to fill block with exact matching (same containeer number and same items)
            foreach ($rowContents as $k => $rowContent) {
                if (0 === count(array_filter($rowContent['conts']))) {
                    // No content, ignore it
                    continue;
                }

                $blockItems = $this->computeblockItems($rowContent['conts']);

                foreach ($newContents as $kk => $newContent) {
                    if (isset($filledContents[$kk])) {
                        continue;
                    }
                    if ($blockItems === $this->computeblockItems($newContent['conts'])) {
                        $newContents[$kk] = $rowContent;
                        $filledContents[$kk] = true;
                        unset($rowContents[$k]);
                        continue 2;
                    }
                }
            }

            $handlerBlock = null;
            $itemsByType = [];
            foreach ($rowContents as $k => $block) {
                if (0 === count(array_filter($rowContent['conts']))) {
                    // No content, ignore it
                    continue;
                }

                foreach ($block['conts'] as $kk => $cont) {
                    foreach ($cont as $contItem) {
                        if (ComposerService::ITEM_HANDLER === $contItem['_type']) {
                            $handlerBlock = $block;
                            continue 3;
                        }

                        if (!isset($itemsByType[$contItem['_type']])) {
                            $itemsByType[$contItem['_type']] = [];
                        }
                        $contItem['_originBlock'] = [$block['_type'], $kk];
                        $itemsByType[$contItem['_type']][] = $contItem;
                    }
                }
            }

            if (count($itemsByType)) {
                foreach ($newContents as $k => $newContent) {
                    if (isset($filledContents[$kk])) {
                        continue;
                    }

                    foreach ($newContent['conts'] as $kk => $cont) {
                        foreach ($cont as $kkk => $contItem) {
                            $type = $contItem['_type'];
                            if (isset($itemsByType[$type])) {
                                $newContents[$k]['conts'][$kk][$kkk] = array_shift($itemsByType[$type]);
                                unset($newContents[$k]['conts'][$kk][$kkk]['_originBlock']);
                                if (0 === count($itemsByType[$type])) {
                                    unset($itemsByType[$type]);
                                }
                            }
                        }
                    }
                }
            }

            $addedMissingBlocks = [];
            foreach ($itemsByType as $contItem) {
                $blockType = $contItem['_originBlock'][0];
                $blockContIdx = $contItem['_originBlock'][1];
                unset($contItem['_originBlock']);
                if (!isset($addedMissingBlocks[$blockType])) {
                    $cfg = $this->getComposableConfig('blocks', $blockType);
                    $addedMissingBlocks[$blockType] = [
                        '_type' => $blockType,
                        'conts' => array_fill(0, $cfg['nb_containers'], []),
                    ];
                }
                $addedMissingBlocks[$blockType]['conts'][$blockContIdx][] = $contItem;
            }

            $newContents = array_merge($newContents, array_values($addedMissingBlocks));

            if ($handlerBlock) {
                $newContents[] = $handlerBlock;
            }
        }

        $this->applyContent($row, [
            [self::TEMPLATE => $template->getId()],
            ...$newContents,
        ]);
    }

    private function computeblockItems(array $conts): string
    {
        return implode('_', array_map(function ($cont) {
            return implode(',', array_map(function ($contItem) {
                return $contItem['_type'];
            }, $cont));
        }, $conts));
    }

    protected function idifyContents(array $contents): array
    {
        $ret = [];

        foreach ($contents as $content) {
            $ret[$content['id']] = $content;
        }

        return $ret;
    }
}
