<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\NyroCmsBundle\Event\ComposerBlockVarsEvent;
use NyroDev\NyroCmsBundle\Event\ComposerConfigEvent;
use NyroDev\NyroCmsBundle\Event\TinymceConfigEvent;
use NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent;
use NyroDev\NyroCmsBundle\Handler\AbstractHandler;
use NyroDev\NyroCmsBundle\Model\Composable;
use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\UtilityBundle\Services\ImageService;
use NyroDev\UtilityBundle\Services\MainService as nyroDevService;
use Symfony\Component\HttpFoundation\Request;

class ComposerService extends AbstractService
{
    public function getContainer()
    {
        return $this->container;
    }

    protected $configs = array();

    public function getConfig(Composable $row)
    {
        $class = get_class($row);
        if (!isset($this->configs[$class])) {
            $composableConfig = $this->getParameter('nyroCms.composable');

            $ret = isset($composableConfig[$class]) ? $composableConfig[$class] : array();
            $cfgArrays = array('themes', 'available_blocks');
            $cfgArraysMerge = array('default_blocks', 'config_blocks');

            foreach ($cfgArrays as $cfg) {
                if (isset($ret[$cfg]) && 0 === count($ret[$cfg])) {
                    unset($ret[$cfg]);
                }
            }

            $this->configs[$class] = array_merge($composableConfig['default'], $ret);
            foreach ($cfgArraysMerge as $cfg) {
                $this->configs[$class][$cfg] = array_replace_recursive($composableConfig['default'][$cfg], isset($ret[$cfg]) ? $ret[$cfg] : array());
            }
        }

        return $this->configs[$class];
    }

    public function getQuickConfig(Composable $row, $key)
    {
        $cfg = $this->getConfig($row);
        $event = new ComposerConfigEvent($row, $key, isset($cfg[$key]) ? $cfg[$key] : null);
        $this->get('event_dispatcher')->dispatch(ComposerConfigEvent::COMPOSER_CONFIG, $event);

        return $event->getConfig();
    }

    public function canChangeLang(Composable $row)
    {
        return  is_callable(array($row, 'setTranslatableLocale')) && $this->getQuickConfig($row, 'change_lang');
    }

    public function canChangeTheme(Composable $row)
    {
        return is_callable(array($row, 'setTheme')) && $this->getQuickConfig($row, 'change_theme');
    }

    public function cssTemplate(Composable $row)
    {
        return $this->getQuickConfig($row, 'css_template');
    }

    public function cssTabletWidth(Composable $row)
    {
        return $this->getQuickConfig($row, 'css_tablet_width');
    }

    public function cssDesktopWidth(Composable $row)
    {
        return $this->getQuickConfig($row, 'css_desktop_width');
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
            if ($this->getQuickConfig($row, 'tinymce_browser')) {
                $url = $this->generateUrl($this->getQuickConfig($row, 'tinymce_browser_route_per_root'), array(
                    'dirName' => 'tinymce_'.$row->getVeryParent()->getId(),
                ));
            } else {
                $url = $normalUrl;
            }
            $cfg['external_filemanager_path'] = $url.'/';
            $cfg['filemanager_title'] = $this->trans('nyrodev.browser.title');
            $cfg['external_plugins'] = array('filemanager' => $normalUrl.'/plugin.min.js');
        }

        $tinymceConfigEvent = new TinymceConfigEvent($row, $simple, $cfg);
        $this->get('event_dispatcher')->dispatch(TinymceConfigEvent::TINYMCE_CONFIG, $tinymceConfigEvent);

        return $this->tinymceAttrsTrRec($tinymceConfigEvent->getConfig());
    }

    public function cancelUrl(Composable $row)
    {
        $ret = '#';
        if ($row instanceof \NyroDev\NyroCmsBundle\Model\ContentSpec) {
            $handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
            $ret = $this->get(nyroDevService::class)->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
        } else {
            $cfg = $this->getConfig($row);
            $routePrm = isset($cfg['cancel_url']['route_prm']) && is_array($cfg['cancel_url']['route_prm']) ? $cfg['cancel_url']['route_prm'] : array();
            if ($cfg['cancel_url']['need_id']) {
                $routePrm['id'] = $row->getId();
            } elseif ($cfg['cancel_url']['need_veryParent_id']) {
                $routePrm['id'] = $row->getVeryParent()->getId();
            }
            $ret = $this->get(nyroDevService::class)->generateUrl($cfg['cancel_url']['route'], $routePrm);
        }

        return $ret;
    }

    public function getThemes(Composable $row)
    {
        $cfg = $this->getConfig($row);
        $ret = array();
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

    protected $wrapperCssthemeEvents = array();

    public function getWrapperCssTheme(Composable $row, $position = WrapperCssThemeEvent::POSITION_NORMAL)
    {
        if (!isset($this->wrapperCssthemeEvents[$row->getId()])) {
            $this->wrapperCssthemeEvents[$row->getId()] = new WrapperCssThemeEvent($row);
            $this->get('event_dispatcher')->dispatch(WrapperCssThemeEvent::WRAPPER_CSS_THEME, $this->wrapperCssthemeEvents[$row->getId()]);
        }

        return $this->wrapperCssthemeEvents[$row->getId()]->getWrapperCssTheme($position);
    }

    public function tinymceAttrs(Composable $row, $prefix, $simple = false)
    {
        $ret = array();
        foreach ($this->getTinymceConfig($row, $simple) as $k => $v) {
            $ret[$prefix.$k] = is_array($v) ? json_encode($v) : $v;
        }

        return $ret;
    }

    protected function tinymceAttrsTrRec(array $values)
    {
        $ret = array();
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

    protected $existingImages = array();

    public function initComposerFor(Composable $row, $lang, $contentFieldName = 'content')
    {
        $this->existingImages = array();

        if ($lang != $this->get('nyrocms')->getDefaultLocale($row)) {
            $this->existingImages = $this->getImages($row->getContent());
        }

        foreach ($row->getTranslations() as $tr) {
            if ($tr->getField() == $contentFieldName && $tr->getLocale() != $lang) {
                $this->existingImages += $this->getImages(json_decode($tr->getContent(), true));
            }
        }

        return $this->existingImages;
    }

    public function setExistingImages(array $images)
    {
        $this->existingImages = $images;
    }

    protected function getImages(array $blocks)
    {
        $images = array();
        foreach ($blocks as $content) {
            $contents = isset($content['contents']) && is_array($content['contents']) ? $content['contents'] : array();
            foreach ($contents as $name => $c) {
                if ('images' == $name) {
                    foreach ($c as $img) {
                        if (isset($img['file'])) {
                            $images[] = $img['file'];
                        }
                    }
                } elseif (isset($c['file']) && $c['file']) {
                    $images[] = $c['file'];
                }
            }
        }

        return $images;
    }

    public function getBlockConfig(Composable $row, $block)
    {
        $cfg = $this->getConfig($row);

        return isset($cfg['config_blocks']) && is_array($cfg['config_blocks']) && isset($cfg['config_blocks'][$block]) ? $cfg['config_blocks'][$block] : array();
    }

    public function getBlockTemplate(Composable $row, $block)
    {
        $config = $this->getBlockConfig($row, $block);
        if (!isset($config['template'])) {
            throw new \RuntimeException('Template not configured for '.$block);
        }

        return $config['template'];
    }

    public function getBlock(Composable $row, $block, array $defaults = array(), $addExtract = false)
    {
        $ret = array(
            'type' => $block,
            'contents' => array(),
        );
        if ($addExtract) {
            $ret['texts'] = array();
            $ret['images'] = array();
        }
        $cfg = $this->getConfig($row);
        if (isset($cfg['default_blocks']) && isset($cfg['default_blocks'][$block])) {
            $tmp = $cfg['default_blocks'][$block];
            $blockConfig = $this->getBlockConfig($row, $block);
            foreach ($tmp as $k => $v) {
                if (isset($defaults[$k])) {
                    if (isset($blockConfig[$k]) && isset($blockConfig[$k]['image']) && $blockConfig[$k]['image']) {
                        if (isset($blockConfig[$k]['multiple']) && $blockConfig[$k]['multiple']) {
                            $ret['contents'][$k] = array();
                            if (is_array($defaults[$k])) {
                                $deletes = isset($defaults['deletes']) ? $defaults['deletes'] : array();
                                foreach ($defaults[$k] as $kk => $img) {
                                    if (!isset($deletes[$kk]) || !$deletes[$kk]) {
                                        $image = $this->handleDefaultImage($img);
                                        $val = array(
                                            'title' => isset($defaults['titles']) && isset($defaults['titles'][$kk]) ? $defaults['titles'][$kk] : null,
                                            'file' => $image,
                                        );

                                        if (isset($blockConfig[$k]['multipleFields']) && is_array($blockConfig[$k]['multipleFields']) && count($blockConfig[$k]['multipleFields'])) {
                                            foreach ($blockConfig[$k]['multipleFields'] as $field) {
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

    public function deleteBlock(Composable $row, $block, array $contents = array())
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
                }
            }
        }
    }

    public function handleImageUpload(Request $request)
    {
        $image = $request->files->get('image');
        $file = $this->imageUpload($image);
        $ret = array(
            'file' => $file,
            'resized' => $this->imageResizeConfig($file, $request->request->get('cfg')),
        );

        if ($request->request->get('cfg2')) {
            $ret['resized2'] = $this->imageResizeConfig($file, $request->request->get('cfg2'));
        }

        if ($request->request->has('more')) {
            // We have a size defined here, it's for home_extranet block
            $more = $request->request->get('more');
            $tmp = $this->getExtranetImages($file, $more);
            if (count($tmp)) {
                $ret['datas'] = array();
                foreach ($tmp as $k => $v) {
                    $ret['datas']['image_'.$k] = $v;
                }
            }
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($ret);
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
        $fs = new \Symfony\Component\Filesystem\Filesystem();
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

    public function render(Composable $row, $handlerContent = null, $handlerAction = null, $admin = false)
    {
        $ret = null;
        $ret = '<div class="composer composer_'.$this->getCssTheme($row).' '.$this->getWrapperCssTheme($row).'"'.($admin ? ' id="composerCont"' : '').'>';
        $blockName = 'div';

        $hasHandler = $row instanceof \NyroDev\NyroCmsBundle\Model\ComposableHandler && $row->getContentHandler();
        if (0 == count($row->getContent())) {
            // Handle empty content
            if ($admin) {
                $content = array($this->getBlock($row, 'intro'));
                if ($hasHandler) {
                    $handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
                    if ($handler->isWrapped() && $handler->isWrappedAs()) {
                        $tmp = array($handler->isWrappedAs() => AbstractHandler::TEMPLATE_INDICATOR);
                        $content[] = $this->getBlock($row, $handler->isWrapped(), $tmp);
                    } else {
                        $content[] = $this->getBlock($row, 'handler');
                    }
                }

                $row->setContent($content);
            } elseif ($hasHandler) {
                $content = array($this->getBlock($row, 'handler'));
                $row->setContent($content);
            }
        } elseif ($hasHandler) {
            // Check if the handler is placed, and add it if not here
            $content = $row->getContent();
            $handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
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
                    $tmp = array($wrappedAs => AbstractHandler::TEMPLATE_INDICATOR);
                    $content[] = $this->getBlock($row, $isWrapped, $tmp);
                } else {
                    $content[] = $this->getBlock($row, 'handler');
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

    public function renderNew(Composable $row, $block, $admin = false, array $defaults = array())
    {
        $cont = $this->getBlock($row, $block, $defaults);

        return $this->renderBlock($row, '--NEW--', null, $cont, $admin);
    }

    protected function renderBlock(Composable $row, $nb, $handlerContent, $block, $admin)
    {
        $event = new ComposerBlockVarsEvent($row, $this->getQuickConfig($row, 'block_template'), array(
            'nb' => $nb,
            'row' => $row,
            'handlerContent' => $handlerContent,
            'block' => $block,
            'admin' => $admin,
            'customClass' => null,
            'customAttrs' => null,
        ));
        $this->get('event_dispatcher')->dispatch(ComposerBlockVarsEvent::COMPOSER_BLOCK_VARS, $event);

        return $this->get('templating')->render($event->getTemplate(), $event->getVars())."\n\n";
    }

    public function getImageDir()
    {
        return 'uploads/composer';
    }

    protected $rootImageDir;

    protected function getRootImageDir()
    {
        if (is_null($this->rootImageDir)) {
            $this->rootImageDir = $this->getParameter('kernel.root_dir').'/../web/'.$this->getImageDir();
            $fs = new \Symfony\Component\Filesystem\Filesystem();
            if (!$fs->exists($this->rootImageDir)) {
                $fs->mkdir($this->rootImageDir);
            }
        }

        return $this->rootImageDir;
    }

    public function imageUpload(\Symfony\Component\HttpFoundation\File\UploadedFile $image)
    {
        $dir = $this->getRootImageDir();
        $filename = $this->get(nyroDevService::class)->getUniqFileName($dir, $image->getClientOriginalName());
        $image->move($dir, $filename);

        return $filename;
    }

    public function imageResize($file, $w, $h = null)
    {
        return $this->imageResizeConfig($file, array(
            'name' => $w.'_'.$h,
            'w' => $w,
            'h' => $h,
            'fit' => true,
            'quality' => 80,
        ));
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

                $tmp = explode('/web/', $resizedPath);
                $ret = $this->get('templating.helper.assets')->getUrl($tmp[1]);
            } catch (\Exception $e) {
            }
        }

        if (!$ret) {
            $ret = 'data:'.\NyroDev\UtilityBundle\Utility\TransparentPixelResponse::CONTENT_TYPE.';base64,'.\NyroDev\UtilityBundle\Utility\TransparentPixelResponse::IMAGE_CONTENT;
        }

        return $ret;
    }
}
