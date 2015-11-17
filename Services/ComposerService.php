<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\UtilityBundle\Services\AbstractService;
use NyroDev\NyroCmsBundle\Model\Composable;
use Symfony\Component\HttpFoundation\Request;

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
		return $this->getQuickConfig($row, 'change_lang');
	}
	
	public function canChangeTheme(Composable $row) {
		return $this->getQuickConfig($row, 'change_theme');
	}
	
	public function cssTemplate(Composable $row) {
		return $this->getQuickConfig($row, 'css_template');
	}
	
	public function composerTemplate(Composable $row) {
		return $this->getQuickConfig($row, 'composer_template');
	}
	
	public function globalComposerTemplate(Composable $row) {
		return $this->getQuickConfig($row, 'global_composer_template');
	}
	
	public function cancelUrl(Composable $row) {
		$cfg = $this->getConfig($row);
		$routePrm = isset($cfg['cancel_url']['route_prm']) && is_array($cfg['cancel_url']['route_prm']) ? $cfg['cancel_url']['route_prm'] : array();
		if ($cfg['cancel_url']['need_id'])
			$routePrm['id'] = $row->getId();
		return $this->get('nyrodev')->generateUrl($cfg['cancel_url']['route'], $routePrm);
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
	
	public function tinymceAttrs(Composable $row, $prefix, $simple = false) {
		$cfg = $this->getConfig($row);
		$ret = array();
		foreach($cfg['tinymce'.($simple ? '_simple' : null)] as $k=>$v) {
			$ret[$prefix.$k] = is_array($v) ? json_encode($this->tinymceAttrsTrRec($v)) : $v;
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
		
		if ($lang != $this->getParameter('locale'))
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
	
	public function getBlock(Composable $row, $block, array $defaults = array()) {
		$ret = array(
			'type'=>$block,
			'contents'=>array()
		);
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
										$ret['contents'][$k][] = array(
											'title'=>isset($defaults['titles']) && isset($defaults['titles'][$k]) ? $defaults['titles'][$k] : null,
											'file'=>$this->handleDefaultImage($img)
										);
									} else {
										// deletion here
										$images = array_filter(explode("\n", trim($img)));
										$this->removeImages($images);
									}
								}
							}
						} else {
							$ret['contents'][$k] = $this->handleDefaultImage($defaults[$k]);
						}
					} else {
						$ret['contents'][$k] = $defaults[$k];
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
		$ret = '<div class="composer composer_'.$this->getCssTheme($row).'"'.($admin ? ' id="composerCont"' : '').'>';
		$blockName = 'div';
		
		$hasHandler = $row instanceof \NyroDev\NyroCmsBundle\Model\ComposableHandler && $row->getContentHandler();
		if (count($row->getContent()) == 0) {
			// Handle empty content
			if ($admin) {
				$content = array($this->getBlock($row, 'intro'));
				if ($hasHandler)
					$content[] = $this->getBlock($row, 'handler');
				
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
		$ret = null;
		
		switch($block['type']) {
			case 'home_public_square':
				if (isset($block['contents']) && isset($block['contents']['theme']) && $block['contents']['theme'])
					$ret = 'home_public_square_'.$block['contents']['theme'];
				break;
			case 'home_public_wide':
			case 'home_public_large':
			case 'home_public_parallax':
			case 'home_public_small':
				$ret = '';
				if (isset($block['contents']['titleBg']) && $block['contents']['titleBg'] && $block['contents']['titleBg'] != 'transparent')
					$ret.= 'homeTitleBg_'.$block['contents']['titleBg'];
				if (isset($block['contents']['textBg']) && $block['contents']['textBg'] && $block['contents']['textBg'] != 'transparent')
					$ret.= ' homeTextBg_'.$block['contents']['textBg'];
				break;
			case 'home_extranet':
				$ret = implode(' ', array_filter(array(
					'home_extranet_size_'.$block['contents']['size'],
					'home_extranet_form_'.$block['contents']['form'],
					'home_extranet_theme_'.$block['contents']['theme'],
					'home_extranet_type_'.$block['contents']['type'],
					$block['contents']['size'] != '1x1' ? 'home_extranet_imagePos_'.$block['contents']['imagePos'] : null
				)));
				break;
		}
		
		return $ret;
	}
	
	public function getBlockCustomAttrs(Composable $row, array $block, $admin = false) {
		$ret = null;
		
		switch($block['type']) {
			case 'home_public_square':
				if (isset($block['contents']) && isset($block['contents']['image']) && $block['contents']['image']['file'])
					$ret = $this->getBgImage($block['contents']['image']);
				break;
			case 'home_public_wide':
			case 'home_public_large':
			case 'home_public_small':
			case 'home_public_parallax':
				$ret = '';
				//$ret = 'data-top-top="transform: translate3d(0px, 0px, 0px);" data-top-bottom="transform: translate3d(0px, 250%, 0px)" ';
				if (isset($block['contents']) && isset($block['contents']['image']) && $block['contents']['image']['file'])
					$ret.= $this->getBgImage($block['contents']['image']);
				break;
			case 'home_extranet':
				$ret = '';
				$sizes = explode('x', $block['contents']['size']);
				$pos = explode('x', $block['contents']['pos']);
				$ret.= ' data-col="'.$pos[0].'" data-row="'.$pos[1].'" data-sizex="'.$sizes[0].'" data-sizey="'.$sizes[1].'"';
				if (isset($block['contents']) && isset($block['contents']['image']) && $block['contents']['image']['file']) {
					$file = array(
						'file'=>$block['contents']['image']['file']
					);
					$ret.= $this->getBgImage(array_merge($this->getHomeExtranetImageSizes($block['contents']['size'], $block['contents']['imagePos']), $file));
					if ($admin) {
						// Prepare all possible images
						$tmp = $this->getExtranetImages($block['contents']['image']['file'], $block['contents']['size']);
						foreach($tmp as $k=>$v)
							$ret.= ' data-image_'.$k.'="'.$v.'"';
					}
				}
				break;
		}
		
		return $ret;
	}
	
	public function getBgImage(array $image, $urlOnly = false) {
		$imageUrl = $imageUrlOrig = $this->imageResize($image['file'], $image['w'], $image['h']);
		/*
		if ($image['w'] > 1024) {
			$newW = 1024;
			$newH = round($newW * $image['h'] / $image['w']);
			$imageUrl = $this->imageResize($image['file'], $newW, $newH);
		}
		 */
		if ($urlOnly)
			return $imageUrl;
		return ' style="background-image: url('.$imageUrl.')"'.($imageUrlOrig != $imageUrl ? ' data-bigimg="url('.$imageUrlOrig.')"' : '');
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

	public function getExtranetImages($file, $size) {
		$fileA = array('file'=>$file);
		$ret = array(
			'full'=>$this->getBgImage(array_merge($this->getHomeExtranetImageSizes($size, 'full'), $fileA), true)
		);
		$tmp = explode('x', $size);
		if ($tmp[0] == '2') {
			$image = $this->getBgImage(array_merge($this->getHomeExtranetImageSizes($size, 'left'), $fileA), true);
			$ret['left'] = $image;
			$ret['right'] = $image;
		}
		if ($tmp[1] == '2') {
			$image = $this->getBgImage(array_merge($this->getHomeExtranetImageSizes($size, 'top'), $fileA), true);
			$ret['top'] = $image;
			$ret['bottom'] = $image;
		}
		return $ret;
	}
	
	public function getHomeExtranetImageSizes($size, $imagePos) {
		$ret = array(
			'w'=>500,
			'h'=>450
		);
		
		switch($size.'-'.$imagePos) {
			case '1x2-full':
			case '2x2-right':
			case '2x2-left':
				$ret['h'] = $ret['h'] * 2 + 30;
				break;
			case '2x1-full':
			case '2x2-top':
			case '2x2-bottom':
				$ret['w'] = $ret['w'] * 2 + 30;
				break;
			case '2x2-full':
				$ret['w'] = $ret['w'] * 2 + 30;
				$ret['h'] = $ret['h'] * 2 + 30;
				break;
		}
		
		return $ret;
	}
}

