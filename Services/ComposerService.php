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
	
	public function canChangeLang(Composable $row) {
		$cfg = $this->getConfig($row);
		return $cfg['change_lang'];
	}
	
	public function canChangeTheme(Composable $row) {
		$cfg = $this->getConfig($row);
		return $cfg['change_theme'];
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
		$cfg = $this->getConfig($row);
		return $cfg['available_blocks'];
	}
	
	public function getDefaultBlocks(Composable $row) {
		$cfg = $this->getConfig($row);
		return $cfg['default_blocks'];
	}
	
	public function getConfigBlocks(Composable $row) {
		$cfg = $this->getConfig($row);
		return $cfg['config_blocks'];
	}
	
	public function getCssTheme(Composable $row) {
		if (!$row->getParent())
			return $row->getTheme();
		
		return $row->getTheme() ? $row->getTheme() : $$this->getCssTheme($row->getParent());
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
	
	public function getBlock(Composable $row, $block, array $defaults = array()) {
		$ret = array(
			'type'=>$block,
			'contents'=>array()
		);
		$isNewsletter = $row->getContentType() == 'newsletter';
		switch($block) {
			case 'intro':
				if ($isNewsletter) {
					$ret['contents']['title'] = isset($defaults['title']) ? $defaults['title'] : $this->trans('admin.newsletter.type_'.$row->getType());
					$ret['contents']['subtitle'] = isset($defaults['subtitle']) ? $defaults['subtitle'] : $row->getTitle();
					$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : '<strong>'.$this->get('nyrodev')->formatDate(new \DateTime('tomorrow'), $this->trans('date.shortText')).'</strong>';
				} else {
					$ret['contents']['title'] = isset($defaults['title']) ? $defaults['title'] : $row->getTitle();
					$ret['contents']['subtitle'] = isset($defaults['subtitle']) ? $defaults['subtitle'] : $this->trans('admin.composer.default.subtitle');
					$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : $this->trans('admin.composer.default.text');
				}
				break;
			case 'text':
				$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : $this->trans('admin.composer.default.text'.($isNewsletter ? 'Newsletter' : null));
				break;
			case 'image':
				$file = isset($defaults['image']) ? $this->handleDefaultImage($defaults['image']) : null;
				$ret['contents']['image'] = array('w'=>$isNewsletter ? 584 : 1575, 'h'=>$isNewsletter ? 350 : 600, 'file'=>$file);
				break;
			case 'image2':
				$file1 = isset($defaults['image1']) ? $this->handleDefaultImage($defaults['image1']) : null;
				$file2 = isset($defaults['image2']) ? $this->handleDefaultImage($defaults['image2']) : null;
				$ret['contents']['image1'] = array('w'=>515, 'h'=>440, 'file'=>$file1);
				$ret['contents']['image2'] = array('w'=>1040, 'h'=>440, 'file'=>$file2);
				$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : $this->trans('admin.composer.default.shortText');
				break;
			case 'image3':
				$file1 = isset($defaults['image1']) ? $this->handleDefaultImage($defaults['image1']) : null;
				$file2 = isset($defaults['image2']) ? $this->handleDefaultImage($defaults['image2']) : null;
				$file3 = isset($defaults['image3']) ? $this->handleDefaultImage($defaults['image3']) : null;
				$ret['contents']['image1'] = array('w'=>515, 'h'=>440, 'file'=>$file1);
				$ret['contents']['image2'] = array('w'=>515, 'h'=>440, 'file'=>$file2);
				$ret['contents']['image3'] = array('w'=>515, 'h'=>440, 'file'=>$file3);
				$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : $this->trans('admin.composer.default.shortText');
				break;
			case 'column2':
				$ret['contents']['text1'] = isset($defaults['text1']) ? $defaults['text1'] : $this->trans('admin.composer.default.mediumText');
				$ret['contents']['text2'] = isset($defaults['text2']) ? $defaults['text2'] : $this->trans('admin.composer.default.mediumText');
				break;
			case 'column3':
				$ret['contents']['text1'] = isset($defaults['text1']) ? $defaults['text1'] : $this->trans('admin.composer.default.mediumText');
				$ret['contents']['text2'] = isset($defaults['text2']) ? $defaults['text2'] : $this->trans('admin.composer.default.mediumText');
				$ret['contents']['text3'] = isset($defaults['text3']) ? $defaults['text3'] : $this->trans('admin.composer.default.mediumText');
				break;
			case 'video':
				$ret['contents']['url'] = isset($defaults['url']) ? $defaults['url'] : null;
				$ret['contents']['embed'] = isset($defaults['embed']) ? $defaults['embed'] : null;
				break;
			case 'slideshow':
				$ret['contents']['sizes'] = array(
					'big'=>array('w'=>1575, 'h'=>925),
					'thumb'=>array('w'=>100, 'h'=>59)
				);
				$ret['contents']['images'] = array();
				$deletes = isset($defaults['deletes']) ? $defaults['deletes'] : array();
				if (isset($defaults['images']) && is_array($defaults['images'])) {
					foreach($defaults['images'] as $k=>$img) {
						if (!isset($deletes[$k]) || !$deletes[$k]) {
							$ret['contents']['images'][] = array(
								'title'=>$defaults['titles'][$k],
								'file'=>$this->handleDefaultImage(array('file'=>$img))
							);
						} else {
							// deletion here
							$images = array_filter(explode("\n", trim($img)));
							$this->removeImages($images);
						}
					}
				}
				break;
			
			case 'home_public_wide':
			case 'home_public_parallax':
				$changed = $changedMobile = false;
				$file = isset($defaults['image']) ? $this->handleDefaultImage($defaults['image'], $changed) : null;
				$fileMove = isset($defaults['imageMove']) ? $this->handleDefaultImage($defaults['imageMove']) : null;
				$fileMobile = isset($defaults['imageMobile']) ? $this->handleDefaultImage($defaults['imageMobile'], $changedMobile) : null;
				
				if ($changed && !$changedMobile && $fileMobile) {
					// We only changed the desktop file, remove the mobile
					$this->removeImages((array($fileMobile)));
					$fileMobile = null;
				}
				
				$ret['contents']['title'] = isset($defaults['title']) ? $defaults['title'] : 'titre tableau';
				$ret['contents']['titleBg'] = isset($defaults['titleBg']) ? $defaults['titleBg'] : 'transparent';
				$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : '<h2>Lorem Ipsum</h2><p>dolor es</p>';
				$ret['contents']['textBg'] = isset($defaults['textBg']) ? $defaults['textBg'] : 'transparent';
				$ret['contents']['linkText'] = isset($defaults['linkText']) ? $defaults['linkText'] : 'en savoir plus';
				$ret['contents']['linkUrl'] = isset($defaults['linkUrl']) ? $defaults['linkUrl'] : null;
				$ret['contents']['image'] = array('w'=>1920, 'h'=>1080, 'file'=>$file);
				$ret['contents']['imageMove'] = array('w'=>1920, 'h'=>1080, 'file'=>$fileMove);
				$ret['contents']['imageMobile'] = array('w'=>640, 'h'=>540, 'file'=>$fileMobile);
				break;
			
			case 'home_public_large':
				$changed = $changedMobile = false;
				$file = isset($defaults['image']) ? $this->handleDefaultImage($defaults['image'], $changed) : null;
				$fileMobile = isset($defaults['imageMobile']) ? $this->handleDefaultImage($defaults['imageMobile'], $changedMobile) : null;
				
				if ($changed && !$changedMobile && $fileMobile) {
					// We only changed the desktop file, remove the mobile
					$this->removeImages((array($fileMobile)));
					$fileMobile = null;
				}
				
				$ret['contents']['title'] = isset($defaults['title']) ? $defaults['title'] : 'titre tableau';
				$ret['contents']['titleBg'] = isset($defaults['titleBg']) ? $defaults['titleBg'] : 'transparent';
				$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : '<h2>Lorem Ipsum</h2><p>dolor es</p>';
				$ret['contents']['textBg'] = isset($defaults['textBg']) ? $defaults['textBg'] : 'transparent';
				$ret['contents']['linkText'] = isset($defaults['linkText']) ? $defaults['linkText'] : 'en savoir plus';
				$ret['contents']['linkUrl'] = isset($defaults['linkUrl']) ? $defaults['linkUrl'] : null;
				$ret['contents']['image'] = array('w'=>1280, 'h'=>1080, 'file'=>$file);
				$ret['contents']['imageMobile'] = array('w'=>640, 'h'=>540, 'file'=>$fileMobile);
				break;
			
			case 'home_public_small':
				$file = isset($defaults['image']) ? $this->handleDefaultImage($defaults['image']) : null;
				$ret['contents']['title'] = isset($defaults['title']) ? $defaults['title'] : 'titre tableau';
				$ret['contents']['titleBg'] = isset($defaults['titleBg']) ? $defaults['titleBg'] : 'transparent';
				$ret['contents']['linkUrl'] = isset($defaults['linkUrl']) ? $defaults['linkUrl'] : null;
				$ret['contents']['image'] = array('w'=>640, 'h'=>540, 'file'=>$file);
				break;
			
			case 'home_public_square':
				$file = isset($defaults['image']) ? $this->handleDefaultImage($defaults['image']) : null;
				$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : '<h3>Lorem Ipsum</h3><p>dolor es</p>';
				$ret['contents']['linkText'] = isset($defaults['linkText']) ? $defaults['linkText'] : 'en savoir plus';
				$ret['contents']['linkUrl'] = isset($defaults['linkUrl']) ? $defaults['linkUrl'] : null;
				$ret['contents']['theme'] = isset($defaults['theme']) ? $defaults['theme'] : 'white';
				$ret['contents']['image'] = array('w'=>640, 'h'=>540, 'file'=>$file);
				break;
			
			case 'home_public_handler':
				$ret['contents']['type'] = isset($defaults['type']) ? $defaults['type'] : null;
				break;
			
			case 'home_extranet':
				$ret['contents']['size'] = isset($defaults['size']) ? $defaults['size'] : '1x1';
				$ret['contents']['pos'] = isset($defaults['pos']) ? $defaults['pos'] : '1x1';
				$ret['contents']['form'] = isset($defaults['form']) ? $defaults['form'] : 'square';
				$ret['contents']['theme'] = isset($defaults['theme']) ? $defaults['theme'] : 'default';
				$ret['contents']['type'] = isset($defaults['type']) ? $defaults['type'] : 'free';
				$ret['contents']['related'] = isset($defaults['related']) ? $defaults['related'] : null;
				
				$file = isset($defaults['image']) ? $this->handleDefaultImage($defaults['image']) : null;
				$ret['contents']['title'] = isset($defaults['title']) ? $defaults['title'] : 'Titre';
				$ret['contents']['text'] = isset($defaults['text']) ? $defaults['text'] : '<h2>Lorem Ipsum</h2><p>dolor es</p>';
				$ret['contents']['image'] = array('file'=>$file);
				$ret['contents']['imagePos'] = isset($defaults['imagePos']) ? $defaults['imagePos'] : 'full';
				$ret['contents']['legend'] = isset($defaults['legend']) ? $defaults['legend'] : 'Légende';
				if ($defaults['type'] != 'handler') {
					$ret['contents']['linkUrl'] = isset($defaults['linkUrl']) ? $defaults['linkUrl'] : null;
				}
				break;
			
			case 'newsletter_news':
			case 'newsletter_jobs':
				$handler = $this->getHandlerNewsletter($block);
				$row = null;
				if (isset($defaults['object_id']) && $defaults['object_id'])
					$row = $handler->getContentSpec($defaults['object_id']);
				
				if (!$row)
					$row = $handler->getContentSpecQueryBuilder()
								->addOrderBy('cs.id', 'DESC')
								->setMaxResults(1)
								->getQuery()->getOneOrNullResult();
				
				$ret['contents']['object_id'] = $row ? $row->getId() : null;
				break;
		}
		return $ret;
	}
	
	public function deleteBlock(Composable $row, $block, array $contents = array()) {
		switch($block) {
			case 'image3':
				if (isset($contents['image3']) && isset($contents['image3']['file'])) {
					$images = array_filter(explode("\n", trim($contents['image3']['file'])));
					$this->removeImages($images);
				}
			case 'image2':
				if (isset($contents['image2']) && isset($contents['image2']['file'])) {
					$images = array_filter(explode("\n", trim($contents['image2']['file'])));
					$this->removeImages($images);
				}
			case 'image':
			case 'home_extranet':
				if (isset($contents['image']) && isset($contents['image']['file'])) {
					$images = array_filter(explode("\n", trim($contents['image']['file'])));
					$this->removeImages($images);
				}
				break;
			case 'slideshow':
				if (isset($contents['images']) && is_array($contents['images'])) {
					foreach($contents['images'] as $k=>$img) {
						$images = array_filter(explode("\n", trim($img)));
						$this->removeImages($images);
					}
				}
				break;
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
	
	protected function handleDefaultImage(array $defaults, &$changed = false) {
		$ret = null;
		if (isset($defaults['file'])) {
			$tmp = array_filter(explode("\n", trim($defaults['file'])));
			$nb = count($tmp);
			if ($nb > 0) {
				$ret = $tmp[$nb - 1];
				if ($ret == 'DELETE')
					$ret = null;
				unset($tmp[$nb - 1]);
				$changed = count($tmp) > 0;
				$this->removeImages($tmp);
			}
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
		$useId = false;
		$isNewsletter = $row->getContentType() == 'newsletter';
		$ret = '<div class="composer composer_'.$row->getCssTheme().'"'.($admin ? ' id="composerCont"' : '').'>';
		$blockName = 'div';
		
		$hasHandler = $row instanceof \NyroDev\NyroCmsBundle\Model\Entity\Content && $row->getContentHandler();
		if (count($row->getContent()) == 0) {
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
				$ret.= $this->get('templating')->render('NyroDevNyroCmsBundle:Composer:block.html.php', array(
					'nb'=>$useId ? $row->getId() : $nb,
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
		return $this->get('templating')->render('NyroDevNyroCmsBundle:Composer:block.html.php', array(
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

