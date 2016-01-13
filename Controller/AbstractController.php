<?php

namespace NyroDev\NyroCmsBundle\Controller;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\NyroCmsBundle\Repository\ContentRepositoryInterface;
use NyroDev\UtilityBundle\Controller\AbstractController as NyroDevAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends NyroDevAbstractController {
	
	abstract protected function getRootHandler();
	
	/**
	 * 
	 * @return ContentRepositoryInterface
	 */
	public function getContentRepo() {
		return $this->get('nyrocms_db')->getContentRepository();
	}
	
	protected $rootContent;
	
	/**
	 * 
	 * @return Content
	 */
	protected function getRootContent() {
		if (is_null($this->rootContent)) {
			$this->rootContent = $this->getContentRepo()->findOneBy(array('level'=>0, 'handler'=>$this->getRootHandler()));
			if (!$this->rootContent)
				throw new \RuntimeException('Cannot find rootContent "'.$this->getRootHandler().'"');
		}
		return $this->rootContent;
	}
	
	protected function setGlobalRootContent() {
		$this->get('nyrocms')->setRootContent($this->getRootContent());
	}
	
	protected $enabledStates = array(
		Content::STATE_ACTIVE,
		Content::STATE_INVISIBLE,
	);
	
	protected function getContentByUrl($url) {
		$url = '/'.$url;
		$root = $this->getRootContent();
		
		// try direct URL match
		$content = $this->getContentRepo()->findByUrl($url, $root->getId(), $this->enabledStates);
		if ($content)
			return $content;
		
		// try by old url
		$oldContents = $this->getContentRepo()->findByLog('url', $url);
		if (count($oldContents)) {
			foreach($oldContents as $oldContent) {
				if ($oldContent->getRoot() == $root->getId() && in_array($oldContent->getState(), $this->enabledStates))
					return $oldContent;
			}
		}
		
		throw $this->createNotFoundException();
	}
	
	public function contentAction(Request $request, $url, $handler = null, $_config = null) {
		$this->get('nyrocms')->setRouteConfig($_config);
		$this->setGlobalRootContent();
		$content = $this->getContentByUrl($url);
		return $this->handleContent($request, $content, null, $handler);
	}
	
	public function contentSpecAction(Request $request, $url, $id, $handler = null, $_config = null) {
		$this->get('nyrocms')->setRouteConfig($_config);
		$this->setGlobalRootContent();
		$content = $this->getContentByUrl($url);
		
		$contentSpec = $this->get('nyrocms_db')->getContentSpecRepository()->findForAction($id, $content->getContentHandler()->getId(), $this->enabledStates);
		
		if (!$contentSpec)
			throw $this->createNotFoundException();
		
		return $this->handleContent($request, $content, $contentSpec, $handler);
	}
	
	protected function handleContent(Request $request, Content $content, ContentSpec $contentSpec = null, $handlerAction = null) {
		$routePrm = array();
		if ($handlerAction)
			$routePrm['handler'] = $handlerAction;
		
		$redirect = null;
		
		if ($content->getGoUrl())
			$redirect = $this->redirect ($content->getGoUrl());
		
		if (!$redirect)
			$redirect = $this->get('nyrodev')->redirectIfNotUrl($this->get('nyrocms')->getUrlFor($contentSpec ? $contentSpec : $content, false, $routePrm));
		
		if ($redirect)
			return $redirect;
		
		if (count($content->getContent()) === 0) {
			// No text content, search for the first sub content
			$subContents = $this->getContentRepo()->childrenForMenu($content, true);
			if (count($subContents))
				return $this->redirect($this->get('nyrocms')->getUrlFor($subContents[0]));
		}
		
		$parents = $this->getContentRepo()->getPathForBreacrumb($content, $contentSpec ? false : true);
		
		$titles = array();
		$activeIds = array();
		foreach($parents as $parent) {
			$activeIds[$parent->getId()] = $parent->getId();
			$titles[] = $parent->getTitle();
		}
		
		$activeIds[$content->getId()] = $content->getId();
		
		$this->get('nyrocms')->setActiveIds($activeIds);
		$this->get('nyrocms')->setPathInfoObject($contentSpec ? $contentSpec : $content);
		
		if ($content->getContentHandler()) {
			$handler = $this->get('nyrocms')->getHandler($content->getContentHandler());
			$handler->init($request);
			$contentHandler = $handler->prepareView($content, $contentSpec, $handlerAction);
			if ($contentHandler instanceof Response)
				return $contentHandler;
		}
		
		$title = count($titles) ? implode(', ', $titles) : null;
		$description = $content->getSummary();
		$image = $content->getFirstImage();
		if ($contentSpec) {
			$title = $contentSpec->getTitle().' - '.$content->getTitle().', '.$title;
			$description = $contentSpec->getSummary();
			if ($contentSpec->getFirstImage())
				$image = $contentSpec->getFirstImage();
		} else {
			$title = $content->getTitle().($title ? ', '.$title : null);
		}
		$this->setTitle($title);
		$this->setDescription($description);
		if ($image)
			$this->setImage($this->get('nyrocms_composer')->imageResize($image, 1000));
		
		return $this->handleContentView($request, $content, $parents, $contentSpec, $handlerAction);
	}
	
	abstract protected function handleContentView(Request $request, Content $content, array $parents = array(), ContentSpec $contentSpec = null, $handlerAction = null);
	
	public function searchAction(Request $request, $_config = null) {
		$this->get('nyrocms')->setRouteConfig($_config);
		$this->setGlobalRootContent();
		$q = strip_tags($request->query->get('q'));
		
		$title = $this->trans('public.header.search');
		$results = array();
		if ($q) {
			$this->get('nyrocms')->setPathInfoSearch($q);
			$title = $this->trans('nyrocms.search.title', array('%q%'=>$q));
			$root = $this->getRootContent();
			$tmpQ = array_filter(array_map('trim', explode(' ', trim($q))));
			$query = $parameters = array();
			foreach($tmpQ as $k=>$v) {
				$query[] = '.contentText LIKE :text'.$k;
				$parameters['text'.$k] = '%'.$v.'%';
			}
			
			$results['contents'] = $this->getContentRepo()->search($tmpQ, $root->getId(), Content::STATE_ACTIVE);
			
			$total = count($results['contents']);
			$cts = array();
			$tmp = $this->getContentRepo()->findWithContentHandler($root->getId(), Content::STATE_ACTIVE);
			foreach($tmp as $t)
				$cts[$t->getContentHandler()->getId()] = $t;
			
			$results['contentSpecs'] = array();
			if (count($cts)) {
				$tmpSpecs = $this->get('nyrocms_db')->getContentSpecRepository()->search($tmpQ, array_keys($cts), ContentSpec::STATE_ACTIVE);

				foreach($tmpSpecs as $tmp) {
					$chId = $tmp->getContentHandler()->getId();
					if (!isset($results['contentSpecs'][$chId])) {
						$results['contentSpecs'][$chId] = array(
							'content'=>$cts[$chId],
							'contentSpecs'=>array()
						);
					}
					$results['contentSpecs'][$chId]['contentSpecs'][] = $tmp;
					$total++;
				}
			}
			
			$results['total'] = $total;
		}
		
		$this->setTitle($title);
		
		return $this->handleSearchView($request, $q, $results, $title);
	}
	
	abstract protected function handleSearchView(Request $request, $q, array $results, $title);
	
	public function sitemapIndexXmlAction($_config = null) {
		$this->get('nyrocms')->setRouteConfig($_config);
		$this->setGlobalRootContent();
		$urls = array();
		foreach($this->get('nyrocms')->getLocales($this->getRootContent()) as $locale)
			$urls[] = $this->generateUrl($this->getRootHandler().'_sitemapXml', array('_locale'=>$locale, '_format'=>'xml'), true);
		return $this->render('NyroDevNyroCmsBundle:Default:sitemapIndex.xml.php', array(
			'urls'=>$urls
		));
	}
	
	public function sitemapXmlAction(Request $request, $_config = null) {
		$this->get('nyrocms')->setRouteConfig($_config);
		$this->setGlobalRootContent();
		$urls = array(
			$this->get('nyrodev')->generateUrl($this->getRootHandler().'_homepage'.($request->getLocale() == 'fr' ? '_noLocale' : ''), array(), true)
		);
		
		foreach($this->getContentRepo()->childrenForMenu($this->getRootContent(), false) as $content) {
			if (!$content->getGoUrl())
				$urls[] = $this->get('nyrocms')->getUrlFor($content, true);
			if ($content->getContentHandler() && $this->get('nyrocms')->getHandler($content->getContentHandler())->hasContentSpecUrl()) {
				$contentSpecs = $this->get('nyrocms_db')->getContentSpecRepository()->getForHandler($content->getContentHandler()->getId(), ContentSpec::STATE_ACTIVE);
				foreach($contentSpecs as $contentSpec) {
					$urls[] = $this->get('nyrocms')->getUrlFor($contentSpec, true, array(), $content);
				}
			}
		}
		
		$response = new Response();
		$response->setPublic();
		$response->setSharedMaxAge(60 * 60);
		
		return $this->render('NyroDevNyroCmsBundle:Default:sitemap.xml.php', array(
			'urls'=>$urls
		), $response);
	}
	
	protected function inlineText($text) {
		return preg_replace('/\s\s+/', ' ', preg_replace('/\s/', ' ', trim($text, " \t\n\r\0\x0B:·-")));
	}
	
	protected function setTitle($title, $addDefault = true) {
		$this->get('nyrodev_share')->setTitle($this->inlineText($title).($addDefault ? ' - '.$this->trans(trim($this->container->getParameter('nyroDev_utility.share.title'))) : ''));
	}
	
	protected function setDescription($description) {
		$this->get('nyrodev_share')->setDescription($this->inlineText($description));
	}
	
	protected function setImage($image) {
		if ($image)
			$this->get('nyrodev_share')->setImage($image);
	}

}
