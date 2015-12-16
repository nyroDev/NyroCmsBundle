<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Component\HttpFoundation\Request;
use NyroDev\NyroCmsBundle\Event\WrapperCssThemeEvent;
use NyroDev\NyroCmsBundle\Event\TinymceConfigEvent;
use NyroDev\NyroCmsBundle\Handler\AbstractHandler;

class ComposerService extends AbstractService {
	
	public function getContainer() {
		return $this->container;
	}
	
	protected $configs = array();
	public function getConfig(Composable $row) {
		$class = get_class($row);
		if (!isset($this->configs[$class])) {
			$composableConfig = $this->getParameter('nyroCms.composable');

			$ret = isset($composableConfig[$class]) ? $composableConfig[$class] : array();
			$cfgArrays = array('themes', 'available_blocks');
			$cfgArraysMerge = array('default_blocks', 'config_blocks');
			
			foreach($cfgArrays as $cfg) {
				if (isset($ret[$cfg]) && count($ret[$cfg]) === 0)
					unset($ret[$cfg]);
			}
			
			$this->configs[$class] = array_merge($composableConfig['default'], $ret);
			foreach($cfgArraysMerge as $cfg)
				$this->configs[$class][$cfg] = array_replace_recursive($composableConfig['default'][$cfg], isset($ret[$cfg]) ? $ret[$cfg] : array());
		}
		return $this->configs[$class];
	}
	
	public function getQuickConfig(Composable $row, $key) {
		$cfg = $this->getConfig($row);
		return isset($cfg[$key]) ? $cfg[$key] : null;
	}
	
	public function canChangeLang(Composable $row) {
		return  is_callable(array($row, 'setTranslatableLocale')) && $this->getQuickConfig($row, 'change_lang');
	}
	
	public function canChangeTheme(Composable $row) {
		return is_callable(array($row, 'setTheme')) && $this->getQuickConfig($row, 'change_theme');
	}
	
	public function cssTemplate(Composable $row) {
		return $this->getQuickConfig($row, 'css_template');
	}
	
	public function cssTabletWidth(Composable $row) {
		return $this->getQuickConfig($row, 'css_tablet_width');
	}
	
	public function cssDesktopWidth(Composable $row) {
		return $this->getQuickConfig($row, 'css_desktop_width');
	}
	
	public function getMaxComposerButtons(Composable $row) {
		return $this->getQuickConfig($row, 'max_composer_buttons');
	}
	
	public function composerTemplate(Composable $row) {
		return $this->getQuickConfig($row, 'composer_template');
	}
	
	public function globalComposerTemplate(Composable $row) {
		return $this->getQuickConfig($row, 'global_composer_template');
	}
	
	public function getTinymceConfig(Composable $row, $simple = false) {
		$cfg = $this->getQuickConfig($row, 'tinymce'.($simple ? '_simple' : ''));
		$tinymceConfigEvent = new TinymceConfigEvent($row, $simple, $cfg);
		$this->get('event_dispatcher')->dispatch('nyrocms.events.tinymceConfig', $tinymceConfigEvent);
		return $this->tinymceAttrsTrRec($tinymceConfigEvent->getConfig());
	}
	
	public function cancelUrl(Composable $row) {
		$ret = '#';
		if ($row instanceof \NyroDev\NyroCmsBundle\Model\ContentSpec) {
			$handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
			$ret = $this->get('nyrodev')->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm());
		} else {
			$cfg = $this->getConfig($row);
			$routePrm = isset($cfg['cancel_url']['route_prm']) && is_array($cfg['cancel_url']['route_prm']) ? $cfg['cancel_url']['route_prm'] : array();
			if ($cfg['cancel_url']['need_id'])
				$routePrm['id'] = $row->getId();
			$ret = $this->get('nyrodev')->generateUrl($cfg['cancel_url']['route'], $routePrm);
		}
		return $ret;
	}
	
	public function getThemes(Composable $row) {
		$cfg = $this->getConfig($row);
		$ret = array();
		foreach($cfg['themes'] as $theme) {
			$trIdent = 'admin.composable.themes.'.$theme;
			$tr = $this->trans($trIdent);
			$ret[$theme] = $tr && $tr != $trIdent ? $tr : ucfirst($theme);
		}
		return $ret;
	}
	
	public function getAvailableBlocks(Composable $row) {
		return $this->getQuickConfig($row, 'available_blocks');
	}
	
	public function getDefaultBlocks(Composable $row) {
		return $this->getQuickConfig($row, 'default_blocks');
	}
	
	public function getConfigBlocks(Composable $row) {
		return $this->getQuickConfig($row, 'config_blocks');
	}
	
	public function getCssTheme(Composable $row) {
		if (!$row->getParent())
			return $row->getTheme();
		
		return $row->getTheme() ? $row->getTheme() : $this->getCssTheme($row->getParent());
	}
	
	public function getWrapperCssTheme(Composable $row) {
		$wrapperCssThemeEvent = new WrapperCssThemeEvent($row);
		$this->get('event_dispatcher')->dispatch('nyrocms.events.wrapperCssTheme', $wrapperCssThemeEvent);
		return $wrapperCssThemeEvent->getWrapperCssTheme();
	}
	
	public function tinymceAttrs(Composable $row, $prefix, $simple = false) {
		$ret = array();
		foreach($this->getTinymceConfig($row, $simple) as $k=>$v) {
			$ret[$prefix.$k] = is_array($v) ? json_encode($v) : $v;
		}
		return $ret;
	}
	
	protected function tinymceAttrsTrRec(array $values) {
		$ret = array();
		foreach($values as $k=>$v) {
			if (is_array($v)) {
				$ret[$k] = $this->tinymceAttrsTrRec($v);
			} else if ($k == 'title') {
				$ret[$k] = $this->trans($v);
			} else {
				$ret[$k] = $v;
			}
		}
		return $ret;
	}
	
	protected $existingImages = array();
	
	public function initComposerFor(Composable $row, $lang, $contentFieldName = 'content') {
		$this->existingImages = array();
		
		if ($lang != $this->get('nyrocms')->getDefaultLocale($row))
			$this->existingImages = $this->getImages($row->getContent());
		
		foreach($row->getTranslations() as $tr) {
			if ($tr->getField() == $contentFieldName && $tr->getLocale() != $lang) {
				$this->existingImages+= $this->getImages(json_decode($tr->getContent(), true));
			}
		}
		
		return $this->existingImages;
	}
	
	public function setExistingImages(array $images) {
		$this->existingImages = $images;
	}
	
	protected function getImages(array $blocks) {
		$images = array();
		foreach($blocks as $content) {
			$contents = isset($content['contents']) && is_array($content['contents']) ? $content['contents'] : array();
			foreach($contents as $name=>$c) {
				if ($name == 'images') {
					foreach($c as $img) {
						if (isset($img['file']))
							$images[] = $img['file'];
					}
				} else if (isset($c['file']) && $c['file']) {
					$images[] = $c['file'];
				}
			}
		}
		return $images;
	}
	
	public function getBlockConfig(Composable $row, $block) {
		$cfg = $this->getConfig($row);
		return isset($cfg['config_blocks']) && is_array($cfg['config_blocks']) && isset($cfg['config_blocks'][$block]) ? $cfg['config_blocks'][$block] : array();
	}
	
	public function getBlockTemplate(Composable $row, $block) {
		$config = $this->getBlockConfig($row, $block);
		if (!isset($config['template']))
			throw new \RuntimeException('Template not configured for '.$block);
		return $config['template'];
	}
	
	public function getBlock(Composable $row, $block, array $defaults = array(), $addExtract = false) {
		$ret = array(
			'type'=>$block,
			'contents'=>array(),
		);
		if ($addExtract) {
			$ret['texts'] = array();
			$ret['images'] = array();
		}
		$cfg = $this->getConfig($row);
		if (isset($cfg['default_blocks']) && isset($cfg['default_blocks'][$block])) {
			$tmp = $cfg['default_blocks'][$block];
			$blockConfig = $this->getBlockConfig($row, $block);
			foreach($tmp as $k=>$v) {
				if (isset($defaults[$k])) {
					if (isset($blockConfig[$k]) && isset($blockConfig[$k]['image']) && $blockConfig[$k]['image']) {
						if (isset($blockConfig[$k]['multiple']) && $blockConfig[$k]['multiple']) {
							$ret['contents'][$k] = array();
							if (is_array($defaults[$k])) {
								$deletes = isset($defaults['deletes']) ? $defaults['deletes'] : array();
								foreach($defaults[$k] as $kk=>$img) {
									if (!isset($deletes[$kk]) || !$deletes[$kk]) {
										$image = $this->handleDefaultImage($img);
										$ret['contents'][$k][] = array(
											'title'=>isset($defaults['titles']) && isset($defaults['titles'][$k]) ? $defaults['titles'][$k] : null,
											'file'=>$image
										);
										if ($addExtract)
											$ret['images'][] = $image;
									} else {
										// deletion here
										$images = array_filter(explode("\n", trim($img)));
										$this->removeImages($images);
									}
								}
							}
						} else {
							$ret['contents'][$k] = $this->handleDefaultImage($defaults[$k]);
							if ($addExtract)
								$ret['images'][] = $ret['contents'][$k];
						}
					} else {
						$ret['contents'][$k] = $defaults[$k];
						if ($addExtract)
							$ret['texts'][] = $ret['contents'][$k];
					}
				} else if (strpos($v, 'OBJECT::') === 0) {
					$fct = substr($v, 8);
					$ret['contents'][$k] = $row->$fct();
				} else {
					$ret['contents'][$k] = !is_null($v) ? $this->trans($v) : $v;
				}
			}
		}
		
		return $ret;
	}
	
	public function deleteBlock(Composable $row, $block, array $contents = array()) {
		$blockConfig = $this->getBlockConfig($row, $block);
		foreach($blockConfig as $k=>$v) {
			if (isset($contents[$k]) && $contents[$k]) {
				if (isset($v['image']) && $v['image']) {
					if (isset($v['multiple']) && $v['multiple']) {
						if (is_array($contents[$k])) {
							foreach($contents[$k] as $k=>$img) {
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
	
	public function handleImageUpload(Request $request) {
		$image = $request->files->get('image');
		$file = $this->imageUpload($image);
		$ret = array(
			'file'=>$file,
			'resized'=>$this->imageResize($file, $request->request->get('w'), $request->request->get('h'))
		);

		if ($request->request->get('w2') && $request->request->get('h2'))
			$ret['resized2'] = $this->imageResize($file, $request->request->get('w2'), $request->request->get('h2'));

		if ($request->request->has('more')) {
			// We have a size defined here, it's for home_extranet block
			$more = $request->request->get('more');
			$tmp = $this->getExtranetImages($file, $more);
			if (count($tmp)) {
				$ret['datas'] = array();
				foreach($tmp as $k=>$v)
					$ret['datas']['image_'.$k] = $v;
			}
		}
		
		return new \Symfony\Component\HttpFoundation\JsonResponse($ret);
	}
	
	protected function handleDefaultImage($defaults, &$changed = false) {
		$ret = null;
		$tmp = array_filter(explode("\n", trim($defaults)));
		$nb = count($tmp);
		if ($nb > 0) {
			$ret = $tmp[$nb - 1];
			if ($ret == 'DELETE')
				$ret = null;
			unset($tmp[$nb - 1]);
			$changed = count($tmp) > 0;
			$this->removeImages($tmp);
		}
		return $ret;
	}
	
	protected function removeImages(array $images) {
		// Clean images to keep existing ones
		$images = array_diff(array_map('trim', $images), $this->existingImages);
		$fs = new \Symfony\Component\Filesystem\Filesystem();
		foreach($images as $image) {
			if (trim($image) && $image != 'DELETE') {
				$file = $this->getRootImageDir().'/'.trim($image);
				if ($fs->exists($file)) {
					$fs->remove($file);
					$this->get('nyrodev_image')->removeCache($file);
				}
			}
		}
	}
	
	public function render(Composable $row, $handlerContent = null, $admin = false) {
		$ret = null;
		$showAll = false;
		$ret = '<div class="composer composer_'.$this->getCssTheme($row).' '.$this->getWrapperCssTheme($row).'"'.($admin ? ' id="composerCont"' : '').'>';
		$blockName = 'div';
		
		$hasHandler = $row instanceof \NyroDev\NyroCmsBundle\Model\ComposableHandler && $row->getContentHandler();
		if (count($row->getContent()) == 0) {
			// Handle empty content
			if ($admin) {
				$content = array($this->getBlock($row, 'intro'));
				if ($hasHandler) {
					$handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
					if ($handler->isWrapped() && $handler->isWrappedAs()) {
						$tmp = array($handler->isWrappedAs()=>AbstractHandler::TEMPLATE_INDICATOR);
						$content[] = $this->getBlock($row, $handler->isWrapped(), $tmp);
					} else {
						$content[] = $this->getBlock($row, 'handler');
					}
				}
				
				$row->setContent($content);
			} else if ($hasHandler) {
				$content = array($this->getBlock($row, 'handler'));
				$row->setContent($content);
			}
		}
		
		foreach($row->getContent() as $nb=>$cont) {
			if ($showAll || !$handlerContent || $cont['type'] == 'handler') {
				$ret.= $this->get('templating')->render($this->getQuickConfig($row, 'block_template'), array(
					'nb'=>$nb,
					'row'=>$row,
					'handlerContent'=>$handlerContent,
					'block'=>$cont,
					'admin'=>$admin,
					'customClass'=>$this->getBlockCustomClass($row, $cont, $admin),
					'customAttrs'=>$this->getBlockCustomAttrs($row, $cont, $admin),
				))."\n\n";
			}
		}
		
		if ($blockName)
			$ret.= '</'.$blockName.'>';
		return $ret;
	}
	
	public function renderNew(Composable $row, $block, $admin = false, array $defaults = array()) {
		$cont = $this->getBlock($row, $block, $defaults);
		return $this->get('templating')->render($this->getQuickConfig($row, 'block_template'), array(
				'nb'=>'--NEW--',
				'row'=>$row,
				'handlerContent'=>null,
				'block'=>$cont,
				'admin'=>$admin,
				'customClass'=>$this->getBlockCustomClass($row, $cont, $admin),
				'customAttrs'=>$this->getBlockCustomAttrs($row, $cont, $admin),
			))."\n\n";
	}
	
	public function getBlockCustomClass(Composable $row, array $block, $admin = false) {
		return null;
	}
	
	public function getBlockCustomAttrs(Composable $row, array $block, $admin = false) {
		return null;
	}
	
	public function getImageDir() {
		return 'uploads/composer';
	}
	
	protected $rootImageDir;
	protected function getRootImageDir() {
		if (is_null($this->rootImageDir)) {
			$this->rootImageDir = $this->getParameter('kernel.root_dir').'/../web/'.$this->getImageDir();
			$fs = new \Symfony\Component\Filesystem\Filesystem();
			if (!$fs->exists($this->rootImageDir))
				$fs->mkdir($this->rootImageDir);
		}
		return $this->rootImageDir;
	}
	
	public function imageUpload(\Symfony\Component\HttpFoundation\File\UploadedFile $image) {
		$dir = $this->getRootImageDir();
		$filename = $this->get('nyrodev')->getUniqFileName($dir, $image->getClientOriginalName());
		$image->move($dir, $filename);
		return $filename;
	}
	
	public function imageResize($file, $w, $h = null) {
		$absoluteFile = $this->getRootImageDir().'/'.$file;
		$ret = null;
		if (file_exists($absoluteFile)) {
			try {
				$resizedPath = $this->get('nyrodev_image')->_resize($absoluteFile, array(
					'name'=>$w.'_'.$h,
					'w'=>$w,
					'h'=>$h,
					'fit'=>true,
					'quality'=>80,
				));

				$tmp = explode('/web/', $resizedPath);
				$ret = $this->get('templating.helper.assets')->getUrl($tmp[1]);
			} catch (\Exception $e) {}
		}
		
		if (!$ret)
			$ret = 'data:'.\NyroDev\UtilityBundle\Utility\TransparentPixelResponse::CONTENT_TYPE.';base64,'.\NyroDev\UtilityBundle\Utility\TransparentPixelResponse::IMAGE_CONTENT;
		
		return $ret;
	}

}

