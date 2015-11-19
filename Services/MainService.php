<?php

namespace NyroDev\NyroCmsBundle\Services;

use NyroDev\NyroCmsBundle\Model\Content;
use NyroDev\NyroCmsBundle\Model\ContentHandler;
use NyroDev\NyroCmsBundle\Model\ContentSpec;
use NyroDev\UtilityBundle\Services\AbstractService;

class MainService extends AbstractService {
	
	protected $handlers = array();
	public function getHandler(ContentHandler $contentHandler) {
		if (!isset($this->handlers[$contentHandler->getId()])) {
			$class = $contentHandler->getClass();
			if (!class_exists($class))
				throw new \RuntimeException($class.' not found when trying to create handler.');
			
			$this->handlers[$contentHandler->getId()] = new $class($contentHandler, $this->container);
		}
		return $this->handlers[$contentHandler->getId()];
	}
	
	protected $activeIds = array();
	public function setActiveIds($activeIds) {
		$this->activeIds = $activeIds;
	}
	public function getActiveIds() {
		return $this->activeIds;
	}
	
	protected $rootContent;
	
	public function setRootContent(Content $content) {
		$this->rootContent = $content;
	}
	public function getRootContent() {
		return $this->rootContent;
	}
	
	protected $contentRoots = array();
	
	/**
	 * 
	 * @param type $id
	 * @return \NyroDev\NyroCmsBundle\Entity\Content
	 */
	public function getContentRoot($id) {
		if (!isset($this->contentRoots[$id]))
			$this->contentRoots[$id] = $this->get('nyrocms_db')->getContentRepository()->find($id);
		return $this->contentRoots[$id];
	}
	
	public function getUrlFor($object, $absolute = false, array $prm = array(), $parent = null) {
		$routeCfg = $this->getRouteFor($object, $prm, $parent);
		return $routeCfg['route'] ? $this->generateUrl($routeCfg['route'], $routeCfg['prm'], $absolute) : '#';
	}
	
	public function getRouteFor($object, array $prm = array(), $parent = null) {
		$routeName = null;
		if ($object instanceof Content) {
			$root = $this->getContentRoot($object->getRoot());
			if ($root->getId() == $object->getId()) {
				$routeName = $root->getHandler().'_homepage';
			} else {
				$routeName = $root->getHandler().'_content';
				if (isset($prm['handler']) && $prm['handler'])
					$routeName.= '_handler';
				$prm = array_merge($prm, array(
					'url'=>trim($object->getUrl(), '/'),
				));
			}
		} else if ($object instanceof ContentSpec) {
			$parent = is_null($parent) ? $object->getParent() : $parent;
			if (!$this->getHandler($object->getContentHandler())->hasContentSpecUrl())
				return $this->getUrlFor($parent, $absolute, $prm);
			
			$root = $this->getContentRoot($parent->getRoot());
			$routeName = $root->getHandler().'_content_spec';
			if (isset($prm['handler']) && $prm['handler'])
				$routeName.= '_handler';
			$prm = array_merge($prm, array(
				'url'=>trim($parent->getUrl(), '/'),
				'id'=>$object->getId(),
				'title'=>$this->get('nyrodev')->urlify($object->getTitle())
			));
		}
		return array(
			'route'=>$routeName,
			'prm'=>$prm
		);
	}

	
	public function getDateFormOptions() {
		return array(
			'widget'=>'single_text',
			'format'=>'dd/MM/yyyy',
			'attr'=>array(
				'class'=>'datepicker',
			)
		);
	}
	
	public function sendEmail($to, $subject, $content, $from = null, $locale = null) {
        $response = $this->get('templating')->renderResponse('NyroDevNyroCmsBundle:Tpl:email.html.php', array(
			'subject'=>$subject,
			'locale'=>$locale ? $locale : $this->getLocale(),
			'content'=>$content,
		));
		$html = $response->getContent();
		$text = $this->get('nyrodev')->html2text($html);
		
		if (!$from)
			$from = $this->getParameter('noreply_email');
		
		$msg = $this->get('mailer')->createMessage()
					->setTo($to)
					->setSubject($subject)
					->setFrom($from)
					->setBody($text)
					->addPart($html, 'text/html');
		
		return $this->get('mailer')->send($msg);
	}
	
	public function getLocale() {
		return $this->getRequest()->getLocale();
	}
	
	public function getDefaultLocale($rootContent = null) {
		$rootContent = $rootContent->getVeryParent();
		if ($rootContent->getLocales()) {
			$tmp = explode('|', $rootContent->getLocales());
			return $tmp[0];
		} else {
			return $this->getParameter('locale');
		}
	}
	
	public function getLocales($rootContent = null) {
		$rootContent = $rootContent->getVeryParent();
		return explode('|', $rootContent && $rootContent->getLocales() ? $rootContent->getLocales() : $this->getParameter('locales'));
	}
	
	public function getLocaleNames($rootContent = null) {
		$names = $this->container->getParameter('localeNames');
		$ret = array();
		foreach($this->getLocales($rootContent) as $locale) {
			if (isset($names[$locale]))
				$ret[$locale] = $names[$locale];
		}
		return $ret;
	}
	
	protected $pathInfoObject;
	public function setPathInfoObject($object) {
		$this->pathInfoObject = $object;
	}
	
	public function getPathInfo() {
		$request = $this->getRequest();
		return array(
			'route'=>$request->get('_route'),
			'routePrm'=>$request->get('_route_params'),
			'object'=>$this->pathInfoObject
		);
	}
	
	public function getLocalesUrl($pathInfo, $absolute = false, $onlyLangs = null) {
		$ret = array();
		$isObjectPage = isset($pathInfo['object']) && $pathInfo['object'];
		
		$objectLocale = $isObjectPage ? $pathInfo['object'] : $this->getRootContent();
		
		$defaultLocale = $this->getDefaultLocale($objectLocale);
		$locales = $this->getLocales($objectLocale);
		$curLocale = $this->getLocale();
		if ($onlyLangs && !is_array($onlyLangs))
			$onlyLangs = explode(',', $onlyLangs);
		
		
		foreach($locales as $locale) {
			if ($locale != $curLocale && ($locale == $defaultLocale || empty($onlyLangs) || in_array($locale, $onlyLangs))) {
				$prm = array('_locale'=>$locale);
				if (!$pathInfo['route']) {
					$ret[$locale] = $this->generateUrl('_homepage_noLocale', array(), $absolute);
				} else if ($pathInfo['route'] == '_homepage_noLocale' && $curLocale == $defaultLocale) {
					$ret[$locale] = $this->generateUrl('_homepage', array_merge($pathInfo['routePrm'], $prm), $absolute);
				} else if ($pathInfo['route'] == '_homepage' && $locale == $defaultLocale) {
					$ret[$locale] = $this->generateUrl('_homepage_noLocale', array(), $absolute);
				} else if ($isObjectPage) {
					$pathInfo['object']->setTranslatableLocale($locale);
					$this->get('nyrocms_db')->refresh($pathInfo['object']);
					$ret[$locale] = $this->getUrlFor($pathInfo['object'], $absolute, $prm);
				} else {
					$ret[$locale] = $this->generateUrl($pathInfo['route'], array_merge($pathInfo['routePrm'], $prm), $absolute);
				}
			}
		}
		return $ret;
	}
	
}