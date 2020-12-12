<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\NyroCmsBundle\Event\ComposerBlockVarsEvent;
use NyroDev\NyroCmsBundle\Event\ComposerConfigEvent;
use NyroDev\NyroCmsBundle\Event\ComposerDefaultBlockEvent;
use NyroDev\NyroCmsBundle\Event\ComposerEvent;
use NyroDev\NyroCmsBundle\Event\TinymceConfigEvent;
use NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent;
use NyroDev\NyroCmsBundle\Handler\AbstractHandler;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\NyroCmsBundle\Model\ComposableHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\UtilityBundle\Services\ImageService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\Traits\AssetsPackagesServiceableTrait;
use NyroDev\UtilityBundle\Services\Traits\TwigServiceableTrait;
use NyroDev\UtilityBundle\Utility\TransparentPixelResponse;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ComposerService extends AbstractService
{
    use AssetsPackagesServiceableTrait;
    use TwigServiceableTrait;

    public function getContainer()
    {
        return $this->container;
    }

    protected $configs = [];

    public function getConfig(Composable $row)
    {
        $class = get_class($row);
        if (!isset($this->configs[$class])) {
            $composableConfig = $this->getParameter('nyrocms.composable');

            $ret = isset($composableConfig[$class]) ? $composableConfig[$class] : [];
            $cfgArrays = ['themes', 'available_blocks'];
            $cfgArraysMerge = ['default_blocks', 'config_blocks'];

            foreach ($cfgArrays as $cfg) {
                if (isset($ret[$cfg]) && 0 === count($ret[$cfg])) {
                    unset($ret[$cfg]);
                }
            }

            $this->configs[$class] = array_merge($composableConfig['default'], $ret);
            foreach ($cfgArraysMerge as $cfg) {
                $this->configs[$class][$cfg] = array_replace_recursive($composableConfig['default'][$cfg], isset($ret[$cfg]) ? $ret[$cfg] : []);
            }
        }

        return $this->configs[$class];
    }

    public function getQuickConfig(Composable $row, $key)
    {
        $cfg = $this->getConfig($row);
        $event = new ComposerConfigEvent($row, $key, isset($cfg[$key]) ? $cfg[$key] : null);
        $this->get('event_dispatcher')->dispatch($event, ComposerConfigEvent::COMPOSER_CONFIG);

        return $event->getConfig();
    }

    public function canChangeLang(Composable $row)
    {
        return is_callable([$row, 'setTranslatableLocale']) && $this->getQuickConfig($row, 'change_lang');
    }

    public function isSameLangStructure(Composable $row)
    {
        return is_callable([$row, 'setTranslatableLocale']) && $this->getQuickConfig($row, 'same_lang_structure');
    }

    public function canChangeStructure(Composable $row)
    {
        $isDefaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row) === $row->getTranslatableLocale();

        return $isDefaultLocale || !$this->isSameLangStructure($row);
    }

    public function isSameLangMedia(Composable $row)
    {
        return is_callable([$row, 'setTranslatableLocale']) && $this->getQuickConfig($row, 'same_lang_media');
    }

    public function canChangeMedia(Composable $row)
    {
        $isDefaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row) === $row->getTranslatableLocale();

        return $isDefaultLocale || !$this->isSameLangMedia($row);
    }

    public function canChangeTheme(Composable $row)
    {
        return is_callable([$row, 'setTheme']) && $this->getQuickConfig($row, 'change_theme');
    }

    public function cssTemplate(Composable $row)
    {
        return $this->getQuickConfig($row, 'css_template');
    }

    public function getMaxComposerButtons(Composable $row)
    {
        return $this->getQuickConfig($row, 'max_composer_buttons');
    }

    public function composerTemplate(Composable $row)
    {
        return $this->getQuickConfig($row, 'composer_template');
    }

    public function globalComposerTemplate(Composable $row)
    {
        return $this->getQuickConfig($row, 'global_composer_template');
    }

    public function getTinymceConfig(Composable $row, $simple = false)
    {
        $cfg = $this->getQuickConfig($row, 'tinymce'.($simple ? '_simple' : ''));

        if (!$simple && $this->getQuickConfig($row, 'tinymce_browser')) {
            // Browser enable, add elements for it
            $cfg['plugins'] .= ',responsivefilemanager';
            $normalUrl = $this->generateUrl($this->getQuickConfig($row, 'tinymce_browser_route'));
            if ($this->getQuickConfig($row, 'tinymce_browser_per_root')) {
                $url = $this->generateUrl($this->getQuickConfig($row, 'tinymce_browser_route_per_root'), [
                    'dirName' => 'tinymce_'.$row->getVeryParent()->getId(),
                ]);
            } else {
                $url = $normalUrl;
            }
            $cfg['external_filemanager_path'] = $url.'/';
            $cfg['filemanager_title'] = $this->trans('nyrodev.browser.title');
            $cfg['external_plugins'] = ['filemanager' => $normalUrl.'/plugin.min.js'];
        }

        $tinymceConfigEvent = new TinymceConfigEvent($row, $simple, $cfg);
        $this->get('event_dispatcher')->dispatch($tinymceConfigEvent, TinymceConfigEvent::TINYMCE_CONFIG);

        return $this->tinymceAttrsTrRec($tinymceConfigEvent->getConfig());
    }

    public function cancelUrl(Composable $row)
    {
        $ret = '#';
        if ($row instanceof ContentSpec) {
            $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());
            $ret = $this->get(NyrodevService::class)->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
        } else {
            $cfg = $this->getConfig($row);
            $routePrm = isset($cfg['cancel_url']['route_prm']) && is_array($cfg['cancel_url']['route_prm']) ? $cfg['cancel_url']['route_prm'] : [];
            if ($cfg['cancel_url']['need_id']) {
                $routePrm['id'] = $row->getId();
            } elseif ($cfg['cancel_url']['need_veryParent_id']) {
                $routePrm['id'] = $row->getVeryParent()->getId();
            }
            $ret = $this->get(NyrodevService::class)->generateUrl($cfg['cancel_url']['route'], $routePrm);
        }

        return $ret;
    }

    public function getThemes(Composable $row)
    {
        $cfg = $this->getConfig($row);
        $ret = [];
        foreach ($cfg['themes'] as $theme) {
            $trIdent = 'admin.composable.themes.'.$theme;
            $tr = $this->trans($trIdent);
            $ret[$theme] = $tr && $tr != $trIdent ? $tr : ucfirst($theme);
        }

        return $ret;
    }

    public function getAvailableBlocks(Composable $row)
    {
        return $this->getQuickConfig($row, 'available_blocks');
    }

    public function getDefaultBlocks(Composable $row)
    {
        return $this->getQuickConfig($row, 'default_blocks');
    }

    public function getConfigBlocks(Composable $row)
    {
        return $this->getQuickConfig($row, 'config_blocks');
    }

    public function getCssTheme(Composable $row)
    {
        if (!$row->getParent()) {
            return $row->getTheme();
        }

        return $row->getTheme() ? $row->getTheme() : $this->getCssTheme($row->getParent());
    }

    protected $wrapperCssthemeEvents = [];

    public function getWrapperCssTheme(Composable $row, $position = WrapperCssThemeEvent::POSITION_NORMAL)
    {
        if (!isset($this->wrapperCssthemeEvents[$row->getId()])) {
            $this->wrapperCssthemeEvents[$row->getId()] = new WrapperCssThemeEvent($row);
            $this->get('event_dispatcher')->dispatch($this->wrapperCssthemeEvents[$row->getId()], WrapperCssThemeEvent::WRAPPER_CSS_THEME);
        }

        return $this->wrapperCssthemeEvents[$row->getId()]->getWrapperCssTheme($position);
    }

    public function tinymceAttrs(Composable $row, $prefix, $simple = false)
    {
        $ret = [];
        foreach ($this->getTinymceConfig($row, $simple) as $k => $v) {
            $ret[$prefix.$k] = is_array($v) ? json_encode($v) : $v;
        }

        return $ret;
    }

    protected function tinymceAttrsTrRec(array $values)
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

    protected $existingImages = [];
    protected $existingFiles = [];

    public function initComposerFor(Composable $row, $lang, $contentFieldName = 'content')
    {
        $this->existingImages = [];
        $this->existingFiles = [];

        if ($lang != $this->get(NyroCmsService::class)->getDefaultLocale($row)) {
            $tmp = $this->getImagesAndFiles($row, $row->getContent());
            $this->existingImages = $tmp[0];
            $this->existingFiles = $tmp[1];
        }

        foreach ($row->getTranslations() as $tr) {
            if ($tr->getField() == $contentFieldName && $tr->getLocale() != $lang) {
                $tmp = $this->getImagesAndFiles($row, json_decode($tr->getContent(), true));
                $this->existingImages += $tmp[0];
                $this->existingFiles += $tmp[1];
            }
        }
    }

    public function setExistingImages(array $images)
    {
        $this->existingImages = $images;
    }

    public function getExistingImages()
    {
        return $this->existingImages;
    }

    public function setExistingFiles(array $files)
    {
        $this->existingFiles = $files;
    }

    public function getExistingFiles()
    {
        return $this->existingFiles;
    }

    protected function getImagesAndFiles(Composable $row, array $blocks)
    {
        $configs = [];

        $images = [];
        $files = [];
        foreach ($blocks as $content) {
            if (!isset($configs[$content['type']])) {
                $configs[$content['type']] = $this->getBlockConfig($row, $content['type']);
            }

            $contents = isset($content['contents']) && is_array($content['contents']) ? $content['contents'] : [];

            foreach ($configs[$content['type']] as $k => $v) {
                if (isset($contents[$k]) && $contents[$k]) {
                    if (isset($v['image']) && $v['image']) {
                        if (isset($v['multiple']) && $v['multiple']) {
                            if (is_array($contents[$k])) {
                                foreach ($contents[$k] as $k => $img) {
                                    $images[] = $img['file'];
                                }
                            }
                        } else {
                            $images[] = $contents[$k];
                        }
                    } elseif (isset($v['file']) && $v['file']) {
                        $files[] = $contents[$k];
                    }
                }
            }
        }

        return [$images, $files];
    }

    public function getBlockConfig(Composable $row, $block)
    {
        $cfg = $this->getConfig($row);

        return isset($cfg['config_blocks']) && is_array($cfg['config_blocks']) && isset($cfg['config_blocks'][$block]) ? $cfg['config_blocks'][$block] : [];
    }

    public function getBlockTemplate(Composable $row, $block)
    {
        $config = $this->getBlockConfig($row, $block);
        if (!isset($config['template'])) {
            throw new \RuntimeException('Template not configured for '.$block);
        }

        return $config['template'];
    }

    public function getBlock(Composable $row, $id, $block, array $defaults = [], $addExtract = false)
    {
        $ret = [
            'id' => $id,
            'type' => $block,
            'contents' => [],
        ];
        if ($addExtract) {
            $ret['texts'] = [];
            $ret['images'] = [];
            $ret['files'] = [];
        }
        $cfg = $this->getConfig($row);
        if (isset($cfg['default_blocks']) && isset($cfg['default_blocks'][$block])) {
            $tmp = $cfg['default_blocks'][$block];
            $blockConfig = $this->getBlockConfig($row, $block);
            foreach ($tmp as $k => $v) {
                if (isset($defaults[$k])) {
                    if (isset($blockConfig[$k]) && isset($blockConfig[$k]['image']) && $blockConfig[$k]['image']) {
                        if (isset($blockConfig[$k]['multiple']) && $blockConfig[$k]['multiple']) {
                            $ret['contents'][$k] = [];
                            if (is_array($defaults[$k])) {
                                $multipleFields = isset($blockConfig[$k]['multipleFields']) && is_array($blockConfig[$k]['multipleFields']) && count($blockConfig[$k]['multipleFields']);
                                if (is_array($defaults[$k][0])) {
                                    // We're coming from a non request call, so transform structure as it should have been
                                    $tmp = [];
                                    $defaults['titles'] = [];
                                    $defaults['ids'] = [];
                                    if ($multipleFields) {
                                        foreach ($multipleFields as $field) {
                                            $defaults[$field.'s'] = [];
                                        }
                                    }

                                    foreach ($defaults[$k] as $kk => $vv) {
                                        $tmp[$kk] = $vv['file'];
                                        $defaults['titles'][$kk] = $vv['title'] ?? null;
                                        $defaults['ids'][$kk] = $vv['id'] ?? 'img-'.time() * 1000;
                                        if ($multipleFields) {
                                            foreach ($multipleFields as $field) {
                                                $defaults[$field.'s'][$kk] = $vv[$field] ?? null;
                                            }
                                        }
                                    }

                                    $defaults[$k] = $tmp;
                                }

                                $deletes = isset($defaults['deletes']) ? $defaults['deletes'] : [];
                                foreach ($defaults[$k] as $kk => $img) {
                                    if (!isset($deletes[$kk]) || !$deletes[$kk]) {
                                        $image = $this->handleDefaultImage($img);
                                        $val = [
                                            'id' => isset($defaults['ids']) && isset($defaults['ids'][$kk]) ? $defaults['ids'][$kk] : 'img-'.time() * 1000,
                                            'title' => isset($defaults['titles']) && isset($defaults['titles'][$kk]) ? $defaults['titles'][$kk] : null,
                                            'file' => $image,
                                        ];

                                        if ($multipleFields) {
                                            foreach ($multipleFields as $field) {
                                                $fields = $field.'s';
                                                $val[$field] = isset($defaults[$fields]) && isset($defaults[$fields][$kk]) ? $defaults[$fields][$kk] : null;
                                            }
                                        }

                                        $ret['contents'][$k][] = $val;

                                        if ($addExtract) {
                                            $ret['images'][] = $image;
                                        }
                                    } else {
                                        // deletion here
                                        $images = array_filter(explode("\n", trim($img)));
                                        $this->removeImages($images);
                                    }
                                }
                            }
                        } else {
                            $ret['contents'][$k] = $this->handleDefaultImage($defaults[$k]);
                            if ($addExtract) {
                                $ret['images'][] = $ret['contents'][$k];
                            }
                        }
                    } elseif (isset($blockConfig[$k]) && isset($blockConfig[$k]['file']) && $blockConfig[$k]['file']) {
                        $ret['contents'][$k] = $this->handleDefaultFile($defaults[$k]);
                        if ($addExtract) {
                            $ret['files'][] = $ret['contents'][$k];
                        }
                    } else {
                        $ret['contents'][$k] = $defaults[$k];
                        if ($addExtract) {
                            $ret['texts'][] = $ret['contents'][$k];
                        }
                    }
                } elseif (0 === strpos($v, 'OBJECT::')) {
                    $fct = substr($v, 8);
                    $ret['contents'][$k] = $row->{$fct}();
                } else {
                    $ret['contents'][$k] = !is_null($v) ? $this->trans($v) : $v;
                }
            }
        }

        return $ret;
    }

    public function deleteBlock(Composable $row, $id, $block, array $contents = [])
    {
        $blockConfig = $this->getBlockConfig($row, $block);
        foreach ($blockConfig as $k => $v) {
            if (isset($contents[$k]) && $contents[$k]) {
                if (isset($v['image']) && $v['image']) {
                    if (isset($v['multiple']) && $v['multiple']) {
                        if (is_array($contents[$k])) {
                            foreach ($contents[$k] as $k => $img) {
                                $images = array_filter(explode("\n", trim($img)));
                                $this->removeImages($images);
                            }
                        }
                    } else {
                        $images = array_filter(explode("\n", trim($contents[$k])));
                        $this->removeImages($images);
                    }
                } elseif (isset($v['file']) && $v['file']) {
                    $files = array_filter(explode("\n", trim($contents[$k])));
                    $this->removeFiles($files);
                }
            }
        }
    }

    public function handleImageUpload(Request $request)
    {
        $image = $request->files->get('image');
        $file = $this->imageUpload($image);
        $ret = [
            'file' => $file,
            'resized' => $this->imageResizeConfig($file, $request->request->get('cfg')),
        ];

        if ($request->request->get('cfg2')) {
            $ret['resized2'] = $this->imageResizeConfig($file, $request->request->get('cfg2'));
        }

        return new JsonResponse($ret);
    }

    protected function handleDefaultImage($defaults, &$changed = false)
    {
        $ret = null;
        $tmp = array_filter(explode("\n", trim($defaults)));
        $nb = count($tmp);
        if ($nb > 0) {
            $ret = $tmp[$nb - 1];
            if ('DELETE' == $ret) {
                $ret = null;
            }
            unset($tmp[$nb - 1]);
            $changed = count($tmp) > 0;
            $this->removeImages($tmp);
        }

        return $ret;
    }

    protected function removeImages(array $images)
    {
        // Clean images to keep existing ones
        $images = array_diff(array_map('trim', $images), $this->existingImages);
        $fs = new Filesystem();
        foreach ($images as $image) {
            if (trim($image) && 'DELETE' != $image) {
                $file = $this->getRootImageDir().'/'.trim($image);
                if ($fs->exists($file)) {
                    $fs->remove($file);
                    $this->get(ImageService::class)->removeCache($file);
                }
            }
        }
    }

    public function handleFileUpload(Request $request)
    {
        $fileUp = $request->files->get('file');
        $file = $this->fileUpload($fileUp);
        $ret = [
            'file' => $file,
        ];

        return new JsonResponse($ret);
    }

    protected function handleDefaultFile($defaults, &$changed = false)
    {
        $ret = null;
        $tmp = array_filter(explode("\n", trim($defaults)));
        $nb = count($tmp);
        if ($nb > 0) {
            $ret = $tmp[$nb - 1];
            if ('DELETE' == $ret) {
                $ret = null;
            }
            unset($tmp[$nb - 1]);
            $changed = count($tmp) > 0;
            $this->removeFiles($tmp);
        }

        return $ret;
    }

    protected function removeFiles(array $files)
    {
        // Clean files to keep existing ones
        $files = array_diff(array_map('trim', $files), $this->existingFiles);
        $fs = new Filesystem();
        foreach ($files as $file) {
            if (trim($file) && 'DELETE' != $file) {
                $fileUp = $this->getRootImageDir().'/'.trim($file);
                if ($fs->exists($fileUp)) {
                    $fs->remove($fileUp);
                }
            }
        }
    }

    public function render(Composable $row, $handlerContent = null, $handlerAction = null, $admin = false)
    {
        $ret = null;
        $ret = '<div class="composer composer_'.$this->getCssTheme($row).' '.$this->getWrapperCssTheme($row).'"'.($admin ? ' id="composerCont"' : '').'>';
        $blockName = 'div';

        $hasHandler = $row instanceof ComposableHandler && $row->getContentHandler();
        if (0 == count($row->getContent())) {
            // Handle empty content
            if ($admin) {
                $event = new ComposerDefaultBlockEvent($row, [$this->getBlock($row, 'intro-intro', 'intro')]);
                $this->get('event_dispatcher')->dispatch($event, ComposerDefaultBlockEvent::COMPOSER_DEFAULT_ADMIN_CONTENT);

                $content = $event->getContent();

                if ($hasHandler) {
                    $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());
                    if ($handler->isWrapped() && $handler->isWrappedAs()) {
                        $tmp = [$handler->isWrappedAs() => AbstractHandler::TEMPLATE_INDICATOR];
                        $content[] = $this->getBlock($row, 'wrapper-'.$handler->isWrapped(), $handler->isWrapped(), $tmp);
                    } else {
                        $content[] = $this->getBlock($row, 'handler-handler', 'handler');
                    }
                }

                $row->setContent($content);
            } elseif ($hasHandler) {
                $content = [$this->getBlock($row, 'handler-handler', 'handler')];
                $row->setContent($content);
            }
        } elseif ($hasHandler) {
            // Check if the handler is placed, and add it if not here
            $content = $row->getContent();
            $handler = $this->get(NyroCmsService::class)->getHandler($row->getContentHandler());
            $isWrapped = $handler->isWrapped();
            $wrappedAs = $handler->isWrappedAs();
            $hasHandlerPlaced = false;
            foreach ($content as $cont) {
                if ('handler' == $cont['type'] || ($isWrapped && $isWrapped == $cont['type'] && isset($cont['contents'][$wrappedAs]) && AbstractHandler::TEMPLATE_INDICATOR == $cont['contents'][$wrappedAs])) {
                    $hasHandlerPlaced = true;
                }
            }
            if (!$hasHandlerPlaced) {
                if ($admin && $isWrapped) {
                    $tmp = [$wrappedAs => AbstractHandler::TEMPLATE_INDICATOR];
                    $content[] = $this->getBlock($row, 'wrapper-'.$isWrapped, $isWrapped, $tmp);
                } else {
                    $content[] = $this->getBlock($row, 'handler-handler', 'handler');
                }
                $row->setContent($content);
            }
        }

        foreach ($row->getContent() as $nb => $cont) {
            if ((!$handlerContent && !$handlerAction) || 'handler' == $cont['type']) {
                $ret .= $this->renderBlock($row, $nb, $handlerContent, $cont, $admin);
            }
        }

        if ($blockName) {
            $ret .= '</'.$blockName.'>';
        }

        return $ret;
    }

    public function renderNew(Composable $row, $block, $admin = false, array $defaults = [])
    {
        $cont = $this->getBlock($row, $block.'---ID--', $block, $defaults);

        return $this->renderBlock($row, '--NEW--', null, $cont, $admin);
    }

    protected function renderBlock(Composable $row, $nb, $handlerContent, $block, $admin)
    {
        $event = new ComposerBlockVarsEvent($row, $this->getQuickConfig($row, 'block_template'), [
            'nb' => $nb,
            'row' => $row,
            'handlerContent' => $handlerContent,
            'block' => $block,
            'admin' => $admin,
            'customClass' => null,
            'customAttrs' => null,
        ], $this->getBlockConfig($row, $block['type']));

        $this->get('event_dispatcher')->dispatch($event, ComposerBlockVarsEvent::COMPOSER_BLOCK_VARS);

        return $this->getTwig()->render($event->getTemplate(), $event->getVars())."\n\n";
    }

    public function getImageDir()
    {
        return 'uploads/composer';
    }

    protected $rootImageDir;

    protected function getRootImageDir()
    {
        if (is_null($this->rootImageDir)) {
            $this->rootImageDir = $this->get(NyrodevService::class)->getKernel()->getProjectDir().'/public/'.$this->getImageDir();
            $fs = new Filesystem();
            if (!$fs->exists($this->rootImageDir)) {
                $fs->mkdir($this->rootImageDir);
            }
        }

        return $this->rootImageDir;
    }

    public function imageUpload(UploadedFile $image)
    {
        $dir = $this->getRootImageDir();
        $filename = $this->get(NyrodevService::class)->getUniqFileName($dir, $image->getClientOriginalName());
        $image->move($dir, $filename);

        return $filename;
    }

    public function imageResize($file, $w, $h = null)
    {
        return $this->imageResizeConfig($file, [
            'name' => $w.'_'.$h,
            'w' => $w,
            'h' => $h,
            'fit' => true,
            'quality' => 80,
        ]);
    }

    public function imageResizeConfig($file, array $config)
    {
        $absoluteFile = $this->getRootImageDir().'/'.$file;
        $ret = null;

        if (file_exists($absoluteFile)) {
            try {
                if (!isset($config['name'])) {
                    $config['name'] = md5(json_encode($config));
                }

                $resizedPath = $this->get(ImageService::class)->_resize($absoluteFile, $config);

                $tmp = explode('/public/', $resizedPath);
                $ret = $this->getAssetsPackages()->getUrl($tmp[1]);
            } catch (\Exception $e) {
            }
        }

        if (!$ret) {
            $ret = 'data:'.TransparentPixelResponse::CONTENT_TYPE.';base64,'.TransparentPixelResponse::IMAGE_CONTENT;
        }

        return $ret;
    }

    public function getFileUrl($file)
    {
        return $this->getAssetsPackages()->getUrl($this->getImageDir().'/'.$file);
    }

    public function fileUpload(UploadedFile $file)
    {
        $dir = $this->getRootImageDir();
        $filename = $this->get(NyrodevService::class)->getUniqFileName($dir, $file->getClientOriginalName());
        $file->move($dir, $filename);

        return $filename;
    }

    public function afterComposerEdition(Composable $row)
    {
        $isDefaultLocale = true;
        $canChangeLang = $this->canChangeLang($row);
        $langs = $this->get(NyroCmsService::class)->getLocaleNames($row);

        $isDefaultLocale = $this->get(NyroCmsService::class)->getDefaultLocale($row) === $row->getTranslatableLocale();

        $event = new ComposerEvent($row);
        $eventName = null;
        if ($isDefaultLocale || !$canChangeLang) {
            // We changed the default local row
            $sameLangStructure = $this->isSameLangStructure($row);
            $sameLangMedia = $this->isSameLangMedia($row);

            if ($sameLangStructure || $sameLangMedia) {
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
                        } catch (\Exception $e) {
                            // What to do here?
                        }
                    }
                }

                if (count($newContents)) {
                    // We have some lang changes to apply, let's do it
                    $om = $this->get(DbAbstractService::class)->getObjectManager();
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
                        $row->setContentText(implode("\n", $newTexts));
                        $row->setFirstImage($firstImage);

                        $om->flush();

                        $this->get('event_dispatcher')->dispatch($event, ComposerEvent::COMPOSER_LANG_SAME);
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
            $this->get('event_dispatcher')->dispatch($event, $eventName);
        }
    }

    protected function idifyContents(array $contents)
    {
        $ret = [];
        foreach ($contents as $content) {
            $ret[$content['id']] = $content;
        }

        return $ret;
    }
}
