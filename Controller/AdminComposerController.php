<?php

namespace NyroDev\NyroCmsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class AdminComposerController extends AbstractAdminController {
	
	public function composerAction(Request $request, $type, $id, $lang = null) {
		$row = $this->get('nyrocms_db')->getRepository($type)->find($id);
		if (!$row || !($row instanceof \NyroDev\NyroCmsBundle\Model\Composable))
			throw $this->createNotFoundException();
		
		if (!$this->get('nyrocms_admin')->canAdmin($row))
			throw $this->createAccessDeniedException();
		
		/* @var $row \NyroDev\NyroCmsBundle\Model\Composable */
		$locale = $this->getParameter('locale');
		if (!$lang)
			$lang = $locale;
		
		$composerService = $this->get('nyrocms_composer');
		$composerService->initComposerFor($row, $lang);
		$canChangeLang = $composerService->canChangeLang($row);
		$canChangeTheme = $composerService->canChangeTheme($row);
		
		$langs = $this->getLangs();
		if ($canChangeLang) {
			if ($lang != $locale) {
				$row->setTranslatableLocale($lang);
				$this->get('nyrocms_db')->refresh($row);
				unset($langs[$lang]);
			} else {
				unset($langs[$locale]);
			}
		}
		
		$url = $this->generateUrl('nyrocms_admin_composer', array_filter(array('type'=>$type, 'id'=>$id, 'lang'=>$lang)));
		/* @var $composerService \NyroDev\NyroCmsBundle\Services\ComposerService */
		$availableBlocks = $composerService->getAvailableBlocks($row);
		$themes = $canChangeTheme ? $composerService->getThemes($row->getParent()) : array();
		
		if ($request->isMethod('post')) {
			if ($request->request->has('imageUpload') && $request->files->has('image')) {
				return $composerService->handleImageUpload($request);
			} else if ($request->request->has('video')) {
				$ret = array();
				
				$url = $request->request->get('url');
				$constraints = array(
					new \Symfony\Component\Validator\Constraints\NotBlank(),
					new \NyroDev\UtilityBundle\Validator\Constraints\EmbedUrl(),
				);
				$errors = $this->get('validator')->validateValue($url, $constraints);
			
				if (count($errors) == 0) {
					$dataUrl = $this->get('nyrodev_embed')->data($url);
					$ret = array(
						'url'=>$url,
						'embed'=>$dataUrl['urlEmbed']
					);
				} else {
					$tmp = array();
					foreach($errors as $err)
						$tmp[] = $err->getMessage();
					$ret['err'] = implode(', ', $tmp);
				}
				
				return new \Symfony\Component\HttpFoundation\JsonResponse($ret);
			}
			
			if ($canChangeTheme && $request->request->has('theme'))
				$row->setTheme($request->request->get('theme'));
			
			$contentsKey = $request->request->get('contentsKey');
			$contentsType = $request->request->get('contentsType');
			$contentsDel = $request->request->get('contentsDel');
			$contents = $request->request->get('contents');
			
			$newContents = array();
			$newTexts = array($row->getTitle());
			$firstImage = null;
			foreach($contentsKey as $key) {
				if (isset($contentsType[$key]) && isset($contents[$key])) {
					if (isset($contentsDel[$key]) && $contentsDel[$key]) {
						// Delete this block
						$composerService->deleteBlock($row, $contentsType[$key], $contents[$key]);
					} else {
						$block = $composerService->getBlock($row, $contentsType[$key], $contents[$key]);
						$newContents[] = $block;
						foreach($block['contents'] as $b) {
							if (is_array($b)) {
								if (is_null($firstImage)) {
									if (isset($b['file']) && $b['file']) {
										$firstImage = $b['file'];
									} else if (count($b)) {
										foreach($b as $img) {
											if (is_null($firstImage) && isset($img['file']) && $img['file']) {
												$firstImage = $img['file'];
											} 
										}
									}
								}
							} else {
								$newTexts[] = html_entity_decode(strip_tags($b));
							}
						}
					}
				}
			}
			
			$row->setContent($newContents);
			$row->setContentText(implode("\n", $newTexts));
			$row->setFirstImage($firstImage);
			
			$this->get('nyrocms_db')->flush();
			return $this->redirect($url);
		} else if ($request->query->has('block') && in_array($request->query->get('block'), $availableBlocks)) {
			$html = $composerService->renderNew($row, $request->query->get('block'), true);
			return new \Symfony\Component\HttpFoundation\Response($html);
		}
		
		if ($row instanceof \NyroDev\NyroCmsBundle\Model\ComposableHandler && $row->getContentHandler()) {
			$handler = $this->get('nyrocms')->getHandler($row->getContentHandler());
			$handler->init($request, true);
			$contentHandler = $handler->prepareView($row);
			if ($contentHandler instanceof \Symfony\Component\HttpFoundation\Response)
				return $contentHandler;
		}
		
		return $this->render('NyroDevNyroCmsBundle:Composer:composer.html.php', array(
			'type'=>$type,
			'id'=>$id,
			'composerUrl'=>$url,
			'row'=>$row,
			'lang'=>$lang,
			'langs'=>$langs,
			'availableBlocks'=>$availableBlocks,
			'canChangeTheme'=>$canChangeTheme,
			'canChangeLang'=>$canChangeLang,
			'themes'=>$themes
		));
	}
	
}
